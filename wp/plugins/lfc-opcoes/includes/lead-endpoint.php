<?php
/**
 * Endpoint AJAX para receber leads do front-end.
 * Salva como CPT lfc_lead + dispara webhook opcional.
 *
 * @package LFC_Opcoes
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registra endpoint público (não precisa login)
 */
add_action( 'wp_ajax_lfc_submit_lead',        'lfc_handle_lead_submit' );
add_action( 'wp_ajax_nopriv_lfc_submit_lead', 'lfc_handle_lead_submit' );

function lfc_handle_lead_submit() {
	// Nonce: validação básica
	if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], 'lfc_lead' ) ) {
		wp_send_json_error( [ 'message' => 'Sessão inválida. Recarregue a página.' ], 403 );
	}

	// Honeypot: campo "website" NÃO visível no form. Se veio preenchido, é bot.
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success( [ 'message' => 'ok' ] ); // finge sucesso para o bot
		return;
	}

	// Sanitiza dados
	$nome      = sanitize_text_field( $_POST['nome'] ?? '' );
	$telefone  = sanitize_text_field( $_POST['telefone'] ?? '' );
	$email     = sanitize_email( $_POST['email'] ?? '' );
	$interesse = sanitize_text_field( $_POST['interesse'] ?? '' );
	$contexto  = sanitize_text_field( $_POST['contexto'] ?? 'geral' );
	$source    = sanitize_text_field( $_POST['source'] ?? 'modal' );
	$event_id  = sanitize_text_field( $_POST['event_id'] ?? '' );

	if ( empty( $nome ) || empty( $telefone ) ) {
		wp_send_json_error( [ 'message' => 'Preencha nome e WhatsApp.' ], 422 );
	}

	// Cria post lfc_lead
	$title = $nome . ' · ' . $telefone;
	$post_id = wp_insert_post( [
		'post_type'   => 'lfc_lead',
		'post_title'  => $title,
		'post_status' => 'publish',
		'meta_input'  => [
			'nome'       => $nome,
			'telefone'   => $telefone,
			'email'      => $email,
			'interesse'  => $interesse,
			'contexto'   => $contexto,
			'source'     => $source,
			'ip'         => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '',
			'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
			'referrer'   => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '',
		],
	], true );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( [ 'message' => 'Erro ao salvar. Tente novamente.' ], 500 );
	}

	// Salva event_id na meta
	if ( $event_id ) update_post_meta( $post_id, 'event_id', $event_id );

	// Salva campos UTM (capturados pelo JS via URL/localStorage)
	// Spec do cliente — exatamente os parâmetros dos exemplos Meta Ads + Google Ads:
	//   Meta:   utm_source, utm_medium, utm_campaign, utm_content
	//   Google: utm_source, utm_medium, utm_campaign, utm_term, utm_content, utm_device, utm_network
	$utm_keys = [
		'utm_source',
		'utm_medium',
		'utm_campaign',
		'utm_content',
		'utm_term',
		'utm_device',
		'utm_network',
	];
	foreach ( $utm_keys as $key ) {
		$val = sanitize_text_field( $_POST[ $key ] ?? '' );
		if ( $val !== '' ) {
			update_post_meta( $post_id, $key, $val );
		}
	}
	// Salva a landing URL (URL completa da página onde o form foi submetido)
	if ( ! empty( $_POST['landing_url'] ) ) {
		update_post_meta( $post_id, 'landing_url', esc_url_raw( $_POST['landing_url'] ) );
	}

	$opts = lfc_get_options();
	// Se o tema definiu constantes hardcoded, usa elas (prevalecem sobre opções do admin)
	if ( defined( 'FC_META_PIXEL_ID' ) )   $opts['meta_pixel_id']   = FC_META_PIXEL_ID;
	if ( defined( 'FC_META_CAPI_TOKEN' ) ) $opts['meta_capi_token'] = FC_META_CAPI_TOKEN;

	// Dispara Meta Conversions API (server-side Lead event)
	if ( ! empty( $opts['meta_capi_token'] ) && ! empty( $opts['meta_pixel_id'] ) ) {
		lfc_dispatch_meta_capi( $post_id, $opts, compact( 'nome', 'telefone', 'email', 'interesse', 'contexto', 'event_id' ) );
	}

	// Dispara webhook externo (n8n/RD/Zapier/ImobMeet) se configurado
	// Sempre grava webhook_status para facilitar debug — mesmo quando skipped.
	if ( ! empty( $opts['webhook_url'] ) ) {
		lfc_dispatch_webhook( $post_id, $opts );
	} else {
		update_post_meta( $post_id, 'webhook_status', 'skipped: webhook_url vazio em lfc_get_options()' );
	}

	wp_send_json_success( [
		'message'  => 'Lead recebido com sucesso.',
		'lead_id'  => $post_id,
		'event_id' => $event_id,
	] );
}

/**
 * Envia evento Lead para Meta Conversions API (server-side).
 * Usa o MESMO event_id do pixel browser para deduplicação.
 *
 * @param int   $post_id CPT lead ID
 * @param array $opts    Plugin options (com meta_capi_token + meta_pixel_id)
 * @param array $data    Dados do lead (nome, telefone, email, interesse, event_id)
 */
function lfc_dispatch_meta_capi( $post_id, $opts, $data ) {
	$pixel_id = preg_replace( '/\D/', '', $opts['meta_pixel_id'] ?? '' );
	$token    = $opts['meta_capi_token'] ?? '';
	if ( ! $pixel_id || ! $token ) return;

	// Hash de dados do usuário (SHA-256 lowercase, conforme spec Meta)
	$hash = function ( $v ) {
		return $v ? hash( 'sha256', strtolower( trim( $v ) ) ) : '';
	};

	// Telefone: apenas dígitos (com código do país)
	$phone_clean = preg_replace( '/\D/', '', $data['telefone'] ?? '' );
	if ( $phone_clean && strlen( $phone_clean ) < 12 ) {
		$phone_clean = '55' . $phone_clean; // Assume Brasil se sem código país
	}

	// Divide nome em primeiro e último
	$parts     = explode( ' ', trim( $data['nome'] ?? '' ) );
	$first     = $parts[0] ?? '';
	$last      = count( $parts ) > 1 ? end( $parts ) : '';

	// Cookies fbp/fbc (vêm do browser via cookies _fbp e _fbc)
	$fbp = isset( $_COOKIE['_fbp'] ) ? sanitize_text_field( $_COOKIE['_fbp'] ) : '';
	$fbc = isset( $_COOKIE['_fbc'] ) ? sanitize_text_field( $_COOKIE['_fbc'] ) : '';

	$user_data = array_filter( [
		'em'                => $data['email'] ? [ $hash( $data['email'] ) ] : null,
		'ph'                => $phone_clean ? [ $hash( $phone_clean ) ] : null,
		'fn'                => $first ? [ $hash( $first ) ] : null,
		'ln'                => $last ? [ $hash( $last ) ] : null,
		'client_ip_address' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '',
		'client_user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
		'fbp'               => $fbp ?: null,
		'fbc'               => $fbc ?: null,
	] );

	$event = [
		'event_name'       => 'Lead',
		'event_time'       => time(),
		'event_id'         => $data['event_id'] ?: ( 'lead_' . $post_id . '_' . time() ),
		'action_source'    => 'website',
		'event_source_url' => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : home_url( '/' ),
		'user_data'        => $user_data,
		'custom_data'      => array_filter( [
			'content_name'     => 'Formulario LP',
			'content_category' => $data['interesse'] ?: 'Informações gerais',
		] ),
	];

	$payload = [
		'data'         => [ $event ],
		'access_token' => $token,
	];

	$endpoint = 'https://graph.facebook.com/v21.0/' . $pixel_id . '/events';
	$response = wp_remote_post( $endpoint, [
		'headers'  => [ 'Content-Type' => 'application/json' ],
		'body'     => wp_json_encode( $payload ),
		'timeout'  => 6,
		'blocking' => false, // fire-and-forget
	] );

	$status = is_wp_error( $response ) ? 'capi_error: ' . $response->get_error_message() : 'capi_dispatched';
	update_post_meta( $post_id, 'meta_capi_status', $status );
}

/**
 * Dispara POST JSON para webhook externo (n8n/RD/Zapier).
 * Assíncrono: não bloqueia a resposta ao front-end.
 */
function lfc_dispatch_webhook( $post_id, $opts ) {
	$payload = [
		'lead_id'   => $post_id,
		'nome'      => get_post_meta( $post_id, 'nome', true ),
		'telefone'  => get_post_meta( $post_id, 'telefone', true ),
		'email'     => get_post_meta( $post_id, 'email', true ),
		'interesse' => get_post_meta( $post_id, 'interesse', true ),
		'contexto'  => get_post_meta( $post_id, 'contexto', true ),
		'source'    => get_post_meta( $post_id, 'source', true ),
		'timestamp' => current_time( 'c' ),
		'site'      => home_url(),
	];

	// Anexa campos UTM ao payload (string vazia se ausente).
	// Permite que o ImobMeet faça atribuição de canal/campanha por lead.
	$utm_keys = [
		'utm_source',
		'utm_medium',
		'utm_campaign',
		'utm_content',
		'utm_term',
		'utm_device',
		'utm_network',
		'landing_url',
	];
	foreach ( $utm_keys as $key ) {
		$payload[ $key ] = (string) get_post_meta( $post_id, $key, true );
	}

	$headers = [
		'Content-Type' => 'application/json',
	];
	if ( ! empty( $opts['webhook_secret'] ) ) {
		$headers['X-LFC-Secret'] = $opts['webhook_secret'];
	}

	// Síncrono com timeout curto (4s): em alguns hosts FastCGI/FPM, requests
	// async com `blocking:false` são cortadas quando o worker termina via
	// wp_send_json_success(). 4s é tempo suficiente pro ImobMeet/n8n responder.
	$response = wp_remote_post( $opts['webhook_url'], [
		'headers' => $headers,
		'body'    => wp_json_encode( $payload ),
		'timeout' => 4,
	] );

	if ( is_wp_error( $response ) ) {
		$status = 'error: ' . $response->get_error_message();
	} else {
		$code = wp_remote_retrieve_response_code( $response );
		$status = ( $code >= 200 && $code < 300 )
			? 'dispatched (HTTP ' . $code . ')'
			: 'http_error: ' . $code . ' — ' . wp_remote_retrieve_response_message( $response );
	}
	update_post_meta( $post_id, 'webhook_status', $status );
}

/**
 * Nonce público para o form (injetado antes do script do tema)
 * Suporta múltiplos handles: fc-main (LP1 Lago) e vfc-main (LP2 Viva).
 * Fallback: injeta diretamente no <head> se nenhum handle estiver registrado.
 */
add_action( 'wp_enqueue_scripts', function () {
	$inline = 'window.FC_AJAX = { url: "' . esc_url_raw( admin_url( 'admin-ajax.php' ) ) . '", nonce: "' . wp_create_nonce( 'lfc_lead' ) . '" };';
	$attached = false;
	foreach ( [ 'fc-main', 'vfc-main' ] as $handle ) {
		if ( wp_script_is( $handle, 'registered' ) ) {
			wp_add_inline_script( $handle, $inline, 'before' );
			$attached = true;
		}
	}
	if ( ! $attached ) {
		add_action( 'wp_head', function () use ( $inline ) {
			echo "\n<script>{$inline}</script>\n";
		}, 5 );
	}
}, 20 );

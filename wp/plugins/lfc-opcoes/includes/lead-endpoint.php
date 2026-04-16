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

	// Dispara webhook (se configurado)
	$opts = lfc_get_options();
	if ( ! empty( $opts['webhook_url'] ) ) {
		lfc_dispatch_webhook( $post_id, $opts );
	}

	wp_send_json_success( [
		'message' => 'Lead recebido com sucesso.',
		'lead_id' => $post_id,
	] );
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

	$headers = [
		'Content-Type' => 'application/json',
	];
	if ( ! empty( $opts['webhook_secret'] ) ) {
		$headers['X-LFC-Secret'] = $opts['webhook_secret'];
	}

	$response = wp_remote_post( $opts['webhook_url'], [
		'headers'  => $headers,
		'body'     => wp_json_encode( $payload ),
		'timeout'  => 8,
		'blocking' => false, // fire-and-forget
	] );

	$status = is_wp_error( $response ) ? 'error: ' . $response->get_error_message() : 'dispatched';
	update_post_meta( $post_id, 'webhook_status', $status );
}

/**
 * Nonce público para o form (injetado via FC_OPTS)
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! wp_script_is( 'fc-main', 'registered' ) ) return;
	wp_add_inline_script( 'fc-main',
		'window.FC_AJAX = { url: "' . esc_url_raw( admin_url( 'admin-ajax.php' ) ) . '", nonce: "' . wp_create_nonce( 'lfc_lead' ) . '" };',
		'before'
	);
}, 20 );

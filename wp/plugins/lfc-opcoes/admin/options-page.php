<?php
/**
 * Página de opções: Configurações → Fazenda Canoa
 *
 * @package LFC_Opcoes
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adiciona a página de opções no menu Configurações
 */
add_action( 'admin_menu', function () {
	add_options_page(
		__( 'Fazenda Canoa — Opções', 'lfc-opcoes' ),
		__( 'Fazenda Canoa', 'lfc-opcoes' ),
		'manage_options',
		'lfc-opcoes',
		'lfc_render_options_page'
	);
} );

/**
 * Registra os settings
 */
add_action( 'admin_init', function () {
	register_setting( 'lfc_opcoes_group', 'lfc_opcoes', [
		'sanitize_callback' => 'lfc_sanitize_options',
		'default'           => [],
	] );
} );

/**
 * Sanitiza input antes de salvar
 */
function lfc_sanitize_options( $input ) {
	$clean = [];
	$clean['whatsapp']           = preg_replace( '/\D/', '', $input['whatsapp'] ?? '' );
	$clean['email']              = sanitize_email( $input['email'] ?? '' );
	$clean['telefone']           = sanitize_text_field( $input['telefone'] ?? '' );
	$clean['horario']            = sanitize_text_field( $input['horario'] ?? '' );
	$clean['mensagem_wa_padrao'] = sanitize_text_field( $input['mensagem_wa_padrao'] ?? '' );
	$clean['book_url']           = esc_url_raw( $input['book_url'] ?? '' );
	$clean['webhook_url']        = esc_url_raw( $input['webhook_url'] ?? '' );
	$clean['webhook_secret']     = sanitize_text_field( $input['webhook_secret'] ?? '' );
	// SEO / Analytics — aceita apenas alfanuméricos + hífen
	$clean['gsc_verification']   = preg_replace( '/[^A-Za-z0-9_\-]/', '', $input['gsc_verification'] ?? '' );
	$clean['ga4_id']             = preg_replace( '/[^A-Za-z0-9\-]/', '', $input['ga4_id'] ?? '' );
	$clean['meta_pixel_id']      = preg_replace( '/\D/', '', $input['meta_pixel_id'] ?? '' );
	$clean['meta_capi_token']    = sanitize_text_field( $input['meta_capi_token'] ?? '' );
	$clean['google_ads_id']      = preg_replace( '/[^A-Za-z0-9\-]/', '', $input['google_ads_id'] ?? '' );
	$clean['google_ads_conv']    = preg_replace( '/[^A-Za-z0-9\-\/_]/', '', $input['google_ads_conv'] ?? '' );
	return $clean;
}

/**
 * Renderiza a página de opções
 */
function lfc_render_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$opts = lfc_get_options();
	?>
	<div class="wrap">
		<h1>🏡 <?php esc_html_e( 'Fazenda Canoa — Opções', 'lfc-opcoes' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Configurações centrais da landing page. Mudanças aqui refletem em TODOS os botões/links da LP (WhatsApp, formulários, footer, widget flutuante).', 'lfc-opcoes' ); ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'lfc_opcoes_group' ); ?>

			<h2 class="title"><?php esc_html_e( 'Contatos comerciais', 'lfc-opcoes' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="lfc_whatsapp"><?php esc_html_e( 'WhatsApp (formato wa.me)', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="text" id="lfc_whatsapp" name="lfc_opcoes[whatsapp]" value="<?php echo esc_attr( $opts['whatsapp'] ); ?>" class="regular-text" placeholder="5562999999999">
						<p class="description"><?php esc_html_e( 'Apenas números, com código do país. Ex: 5562999593530', 'lfc-opcoes' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="lfc_email"><?php esc_html_e( 'E-mail comercial', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="email" id="lfc_email" name="lfc_opcoes[email]" value="<?php echo esc_attr( $opts['email'] ); ?>" class="regular-text" placeholder="contato@fazendacanoa.com.br">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="lfc_telefone"><?php esc_html_e( 'Telefone fixo (opcional)', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="text" id="lfc_telefone" name="lfc_opcoes[telefone]" value="<?php echo esc_attr( $opts['telefone'] ); ?>" class="regular-text" placeholder="(62) 3000-0000">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="lfc_horario"><?php esc_html_e( 'Horário de atendimento', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="text" id="lfc_horario" name="lfc_opcoes[horario]" value="<?php echo esc_attr( $opts['horario'] ); ?>" class="regular-text" placeholder="Seg a Sáb, 9h às 19h">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="lfc_mensagem"><?php esc_html_e( 'Mensagem padrão do WhatsApp', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="text" id="lfc_mensagem" name="lfc_opcoes[mensagem_wa_padrao]" value="<?php echo esc_attr( $opts['mensagem_wa_padrao'] ); ?>" class="large-text" placeholder="Olá! Vim pela landing page...">
						<p class="description"><?php esc_html_e( 'Esta mensagem é usada quando o usuário clica em CTAs diretos sem contexto. CTAs com contexto (ex: "Quero o lote frente-lago") geram sua própria mensagem.', 'lfc-opcoes' ); ?></p>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Book do empreendimento', 'lfc-opcoes' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="lfc_book"><?php esc_html_e( 'URL do PDF do book', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="url" id="lfc_book" name="lfc_opcoes[book_url]" value="<?php echo esc_attr( $opts['book_url'] ); ?>" class="large-text" placeholder="https://lago.fazendacanoa.com.br/book.pdf">
						<p class="description"><?php esc_html_e( 'Link direto do PDF que o consultor envia no WhatsApp após a captação do book. Upload via Mídia → URL.', 'lfc-opcoes' ); ?></p>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Integração (webhook — opcional)', 'lfc-opcoes' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Quando preenchido, todo lead capturado dispara um POST para este endpoint. Preparado para n8n, RD Station, Zapier, Make etc.', 'lfc-opcoes' ); ?>
			</p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="lfc_webhook"><?php esc_html_e( 'URL do webhook', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="url" id="lfc_webhook" name="lfc_opcoes[webhook_url]" value="<?php echo esc_attr( $opts['webhook_url'] ); ?>" class="large-text" placeholder="https://n8n.seudominio.com/webhook/xxx">
						<p class="description"><?php esc_html_e( 'Deixe vazio para desativar. Leads continuam sendo salvos no WP admin em Leads.', 'lfc-opcoes' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="lfc_secret"><?php esc_html_e( 'Secret do webhook', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="text" id="lfc_secret" name="lfc_opcoes[webhook_secret]" value="<?php echo esc_attr( $opts['webhook_secret'] ); ?>" class="regular-text" placeholder="token-secreto-qualquer">
						<p class="description"><?php esc_html_e( 'Enviado no header X-LFC-Secret. Use no n8n/RD para validar que o request veio deste site.', 'lfc-opcoes' ); ?></p>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'SEO e Analytics', 'lfc-opcoes' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Códigos do Google e Meta para posicionamento orgânico e rastreamento de conversões.', 'lfc-opcoes' ); ?>
			</p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="lfc_gsc"><?php esc_html_e( 'Google Search Console — Código de verificação', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="text" id="lfc_gsc" name="lfc_opcoes[gsc_verification]" value="<?php echo esc_attr( $opts['gsc_verification'] ); ?>" class="regular-text" placeholder="Cole apenas o content do meta tag">
						<p class="description">
							<?php esc_html_e( 'Em search.google.com/search-console, escolha "Meta tag" e copie apenas o valor do atributo content. Não precisa colar a tag inteira.', 'lfc-opcoes' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="lfc_ga4"><?php esc_html_e( 'Google Analytics 4 — ID de Medição', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="text" id="lfc_ga4" name="lfc_opcoes[ga4_id]" value="<?php echo esc_attr( $opts['ga4_id'] ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
						<p class="description">
							<?php esc_html_e( 'Formato G-XXXXXXXXXX. Encontre em Analytics → Admin → Streams.', 'lfc-opcoes' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="lfc_meta_pixel"><?php esc_html_e( 'Meta Pixel ID (Facebook Ads)', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="text" id="lfc_meta_pixel" name="lfc_opcoes[meta_pixel_id]" value="<?php echo esc_attr( $opts['meta_pixel_id'] ); ?>" class="regular-text" placeholder="15 dígitos numéricos">
						<p class="description">
							<?php esc_html_e( 'Apenas números. Dispara PageView em todas as páginas e Lead no envio do formulário.', 'lfc-opcoes' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="lfc_meta_capi"><?php esc_html_e( 'Meta Conversions API — Token de acesso', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="password" id="lfc_meta_capi" name="lfc_opcoes[meta_capi_token]" value="<?php echo esc_attr( $opts['meta_capi_token'] ); ?>" class="large-text" placeholder="EAA... (token longo)" autocomplete="new-password">
						<p class="description">
							<?php esc_html_e( 'Token da Conversions API da Meta. Envia eventos server-to-server com deduplicação via event_id — melhora muito a correspondência mesmo com ad-blockers/iOS.', 'lfc-opcoes' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="lfc_google_ads"><?php esc_html_e( 'Google Ads — Tag ID', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="text" id="lfc_google_ads" name="lfc_opcoes[google_ads_id]" value="<?php echo esc_attr( $opts['google_ads_id'] ); ?>" class="regular-text" placeholder="AW-XXXXXXXXX">
						<p class="description">
							<?php esc_html_e( 'Tag gtag.js do Google Ads, no formato AW-XXXXXXXXX.', 'lfc-opcoes' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="lfc_google_ads_conv"><?php esc_html_e( 'Google Ads — Ação de Conversão (Lead)', 'lfc-opcoes' ); ?></label></th>
					<td>
						<input type="text" id="lfc_google_ads_conv" name="lfc_opcoes[google_ads_conv]" value="<?php echo esc_attr( $opts['google_ads_conv'] ); ?>" class="regular-text" placeholder="AW-XXX/YYYYY">
						<p class="description">
							<?php esc_html_e( 'Valor completo de send_to (formato AW-XXX/YYYYY). Disparado após envio do formulário, antes do redirecionamento para WhatsApp.', 'lfc-opcoes' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>

		<hr>
		<h2><?php esc_html_e( 'Referência rápida — shortcodes disponíveis', 'lfc-opcoes' ); ?></h2>
		<p><?php esc_html_e( 'Use nos conteúdos do WordPress ou em patterns customizados:', 'lfc-opcoes' ); ?></p>
		<ul style="list-style:disc;padding-left:20px">
			<li><code>[lfc_whatsapp]</code> — <?php esc_html_e( 'exibe o número formatado', 'lfc-opcoes' ); ?></li>
			<li><code>[lfc_email]</code> — <?php esc_html_e( 'e-mail comercial', 'lfc-opcoes' ); ?></li>
			<li><code>[lfc_horario]</code> — <?php esc_html_e( 'horário de atendimento', 'lfc-opcoes' ); ?></li>
		</ul>
	</div>
	<?php
}

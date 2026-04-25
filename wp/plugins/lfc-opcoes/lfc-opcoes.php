<?php
/**
 * Plugin Name:       Fazenda Canoa — Opções & Leads
 * Plugin URI:        https://lago.fazendacanoa.com.br
 * Description:       Opções centrais (WhatsApp, e-mail, horário, URL do book) + CPT para captação de leads + endpoint AJAX + webhook ImobMeet CRM (default) com fallback p/ admin. Usado pelos temas fazenda-canoa e viva-fazenda-canoa.
 * Version:           1.0.2
 * Author:            RUCH
 * Author URI:        https://ruch.digital
 * License:           GPL-2.0-or-later
 * Text Domain:       lfc-opcoes
 * Requires at least: 6.5
 * Requires PHP:      7.4
 *
 * @package LFC_Opcoes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'LFC_VERSION', '1.0.2' );
define( 'LFC_DEFAULT_WEBHOOK_URL', 'https://crm.imobmeet.com.br/webhook/formulario/d71ec64fcce5855c24d3ce8a9404113bcea6bc35' );
define( 'LFC_PATH', plugin_dir_path( __FILE__ ) );
define( 'LFC_URL', plugin_dir_url( __FILE__ ) );

require_once LFC_PATH . 'admin/options-page.php';
require_once LFC_PATH . 'includes/cpt-leads.php';
require_once LFC_PATH . 'includes/lead-endpoint.php';

/**
 * Retorna array com todas as opções salvas.
 * Usada pelo tema via function_exists('lfc_get_options').
 */
function lfc_get_options() {
	$defaults = [
		'whatsapp'            => '5562999593530',
		'email'               => 'contato@fazendacanoa.com.br',
		'telefone'            => '',
		'horario'             => 'Seg a Sáb, 9h às 19h',
		'mensagem_wa_padrao'  => 'Olá! Vim pela landing page da Fazenda Canoa',
		'book_url'            => '',
		'webhook_url'         => LFC_DEFAULT_WEBHOOK_URL,
		'webhook_secret'      => '',
		// SEO / Analytics (hardcoded — ativos por padrão, sem precisar configurar no admin)
		'gsc_verification'    => '',
		'ga4_id'              => '',
		'meta_pixel_id'       => '367669074650821',
		'meta_capi_token'     => 'EAATI2pWjzk8BRYfqZCZAWdUyW7HmitemkIZAf99mBy8LaVybwc2WGLXZCvLZBSUZBswdmmZCOWZCdVLdAILcjteBwsLEgBPWxG91btsDblbWa3paIu2j43fwSHp7Blk2ry5Gr99C1gMZAsS5QHKirofZA18irJuTPIOP9lSviKFZB6Trj55cNZC46qon1SEeMHdmUIcfYwZDZD',
		'google_ads_id'       => 'AW-432545598',
		'google_ads_conv'     => 'AW-432545598/FJsnCKPUyaAcEL6-oM4B',
	];
	$saved = get_option( 'lfc_opcoes', [] );
	$opts  = wp_parse_args( $saved, $defaults );

	// Fallback: se admin gravou webhook_url vazio, garante o default (instalações pré-existentes).
	if ( empty( $opts['webhook_url'] ) ) {
		$opts['webhook_url'] = LFC_DEFAULT_WEBHOOK_URL;
	}

	return $opts;
}

/**
 * Helper: WhatsApp URL pronta com mensagem.
 */
function lfc_whatsapp_url( $message = '' ) {
	$opts = lfc_get_options();
	$base = 'https://wa.me/' . preg_replace( '/\D/', '', $opts['whatsapp'] );
	$msg  = $message ?: $opts['mensagem_wa_padrao'];
	return $base . '?text=' . rawurlencode( $msg );
}

/**
 * Shortcode: [lfc_whatsapp] → número (62) 99999-9999
 */
add_shortcode( 'lfc_whatsapp', function () {
	$opts = lfc_get_options();
	$num = preg_replace( '/\D/', '', $opts['whatsapp'] );
	if ( strlen( $num ) >= 12 ) {
		// 55 62 99999 9999
		$ddd = substr( $num, 2, 2 );
		$p1  = substr( $num, 4, 5 );
		$p2  = substr( $num, 9, 4 );
		return "({$ddd}) {$p1}-{$p2}";
	}
	return $opts['whatsapp'];
} );

add_shortcode( 'lfc_email', function () { return esc_html( lfc_get_options()['email'] ); } );
add_shortcode( 'lfc_horario', function () { return esc_html( lfc_get_options()['horario'] ); } );

/**
 * Ativação: registra o CPT, flush rewrite rules
 */
register_activation_hook( __FILE__, function () {
	lfc_register_cpt_leads();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );

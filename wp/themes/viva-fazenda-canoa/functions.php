<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// SEO + Performance
require_once get_theme_file_path( 'inc/seo.php' );
require_once get_theme_file_path( 'inc/performance.php' );

// Theme setup
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'custom-logo', [ 'height' => 80, 'width' => 320, 'flex-width' => true, 'flex-height' => true ] );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'script', 'style' ] );
} );

// Enqueue styles and scripts
add_action( 'wp_enqueue_scripts', function () {
	$ver = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'vfc-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=Manrope:wght@300;400;500;600;700&display=swap', [], null );
	wp_enqueue_style( 'vfc-main', get_theme_file_uri( 'assets/css/main.css' ), [ 'vfc-fonts' ], $ver );
	wp_enqueue_script( 'vfc-main', get_theme_file_uri( 'assets/js/main.js' ), [], $ver, [ 'in_footer' => true, 'strategy' => 'defer' ] );
	$opts = function_exists( 'lfc_get_options' ) ? lfc_get_options() : [];
	wp_localize_script( 'vfc-main', 'FC_OPTS', [
		'whatsapp' => $opts['whatsapp'] ?? '5562999593530',
	] );
} );

// Pattern category
add_action( 'init', function () {
	if ( function_exists( 'register_block_pattern_category' ) ) {
		register_block_pattern_category( 'viva-fazenda-canoa', [
			'label' => __( 'Viva Fazenda Canoa', 'viva-fazenda-canoa' ),
		] );
	}
} );

// Favicon fallback
add_action( 'wp_head', function () {
	if ( has_site_icon() ) return;
	$fav = get_theme_file_uri( 'assets/logos/favicon.png' );
	echo '<link rel="icon" type="image/png" href="' . esc_url( $fav ) . '">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $fav ) . '">' . "\n";
}, 1 );

// Placeholder replacement for template parts (same system as LP1)
add_filter( 'render_block', function ( $content, $block ) {
	if ( empty( $content ) || strpos( $content, '{{' ) === false ) return $content;
	$opts = function_exists( 'lfc_get_options' ) ? lfc_get_options() : [];
	$wa = preg_replace( '/\D/', '', $opts['whatsapp'] ?? '5562999593530' );
	$wf = strlen( $wa ) >= 12 ? '(' . substr($wa,2,2) . ') ' . substr($wa,4,5) . '-' . substr($wa,9,4) : $wa;
	return strtr( $content, [
		'{{THEME_URL}}'        => untrailingslashit( get_theme_file_uri( '' ) ),
		'{{LOGO}}'             => esc_url( get_theme_file_uri( 'assets/logos/Logo Fazenda Reserva incorporador 3.png' ) ),
		'{{VIDEO_URL}}'        => esc_url( get_theme_file_uri( 'assets/video/hero-mashup.mp4' ) ),
		'{{POSTER_URL}}'       => esc_url( get_theme_file_uri( 'assets/video/hero-poster.jpg' ) ),
		'{{WHATSAPP_NUMBER}}'  => esc_html( $wf ),
		'{{YEAR}}'             => esc_html( date( 'Y' ) ),
	] );
}, 10, 2 );

// Helpers
if ( ! function_exists( 'vfc_whatsapp_url' ) ) {
	function vfc_whatsapp_url( $msg = '' ) {
		$opts = function_exists( 'lfc_get_options' ) ? lfc_get_options() : [];
		$num = preg_replace( '/\D/', '', $opts['whatsapp'] ?? '5562999593530' );
		$base = 'https://wa.me/' . $num;
		return $msg ? $base . '?text=' . rawurlencode( $msg ) : $base;
	}
}

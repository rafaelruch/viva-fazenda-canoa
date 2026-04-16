<?php
/**
 * Performance — Resource hints, preload LCP, cleanup WP bloat.
 *
 * Otimizações para Core Web Vitals e limpeza de features
 * desnecessárias do WordPress.
 *
 * @package FazendaCanoa
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ============================================================
 * RESOURCE HINTS (dns-prefetch, preconnect, preload)
 * Ajudam o browser a iniciar conexões antes de serem necessárias.
 * ============================================================
 */
add_action( 'wp_head', function () {
	echo "\n<!-- Resource hints -->\n";
	// Preconnect para fontes (já temos via wp_enqueue, mas reforçamos)
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	// DNS prefetch para Google Maps (iframe na seção localização)
	echo '<link rel="dns-prefetch" href="//maps.google.com">' . "\n";
	echo '<link rel="dns-prefetch" href="//www.google.com">' . "\n";
	// DNS prefetch para WhatsApp (cliques em CTAs)
	echo '<link rel="dns-prefetch" href="//wa.me">' . "\n";

	// Preload da imagem LCP (hero banner) — acelera significativamente o LCP
	$lcp = get_theme_file_uri( 'assets/fotos/22.jpg' );
	echo '<link rel="preload" as="image" href="' . esc_url( $lcp ) . '" fetchpriority="high" type="image/jpeg">' . "\n";
}, 0 );

/**
 * ============================================================
 * CLEANUP — Remove features/meta tags desnecessárias
 * ============================================================
 */

// Remove emoji scripts (~10KB de JS desnecessário em LPs modernas)
remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles',     'print_emoji_styles' );
remove_action( 'admin_print_styles',  'print_emoji_styles' );
remove_filter( 'the_content_feed',    'wp_staticize_emoji' );
remove_filter( 'comment_text_rss',    'wp_staticize_emoji' );
remove_filter( 'wp_mail',             'wp_staticize_emoji_for_email' );
add_filter( 'tiny_mce_plugins', function ( $plugins ) {
	return array_diff( (array) $plugins, [ 'wpemoji' ] );
} );

// Remove generator tag (exposição desnecessária da versão do WP)
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// Remove links RSS (não temos blog nesta LP)
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );

// Remove short/wlwmanifest/rsd links (editores antigos)
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

// Remove REST API link do wp_head (mantém funcionando via URL direta)
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );

// Remove oEmbed discovery (embeds inter-site não são usados)
remove_action( 'wp_head',          'wp_oembed_add_discovery_links' );
remove_action( 'wp_head',          'wp_oembed_add_host_js' );

// Remove links prev/next (não fazem sentido em single-page LP)
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

// Remove dns-prefetch default do s.w.org (ícones emoji externos)
add_filter( 'emoji_svg_url', '__return_false' );

/**
 * ============================================================
 * JS DEFER/ASYNC — Garante que scripts carreguem sem bloquear render
 * ============================================================
 */
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	// Scripts do admin ficam como estão
	if ( is_admin() ) return $tag;

	// Nossa main.js já é deferred via enqueue, mas garante
	if ( in_array( $handle, [ 'fc-main' ], true ) && strpos( $tag, 'defer' ) === false ) {
		$tag = str_replace( ' src', ' defer src', $tag );
	}

	return $tag;
}, 10, 2 );

/**
 * ============================================================
 * LAZY LOADING — Força para todas as imagens exceto above-the-fold
 * ============================================================
 */
// WP 5.5+ já faz isso. Mantemos compatibilidade.
add_filter( 'wp_lazy_loading_enabled', '__return_true' );

/**
 * ============================================================
 * DESATIVA RECURSOS NÃO USADOS NA LP
 * ============================================================
 */

// Remove XML-RPC (superfície de ataque desnecessária)
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );

// Desabilita embeds (iframe oEmbed não é usado)
add_action( 'wp_footer', function () {
	wp_dequeue_script( 'wp-embed' );
}, 100 );

/**
 * ============================================================
 * HEADERS DE SEGURANÇA (ajudam SEO ao passarem checks)
 * ============================================================
 */
add_action( 'send_headers', function () {
	if ( is_admin() ) return;
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
} );

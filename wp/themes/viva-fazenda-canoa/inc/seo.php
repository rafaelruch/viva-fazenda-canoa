<?php
/**
 * SEO — Meta tags e Structured Data (Schema.org JSON-LD)
 *
 * Implementa:
 *  - Canonical URL em todas as páginas
 *  - Robots meta com max-image-preview
 *  - Open Graph + Twitter Cards completos
 *  - JSON-LD: RealEstateListing, Organization, WebSite, Place,
 *    FAQPage, BreadcrumbList, ImageObject
 *  - Integração com Google Search Console, GA4 e Meta Pixel
 *
 * @package FazendaCanoa
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ============================================================
 * META TAGS EXPANDIDAS (robots, canonical, description, theme-color)
 * ============================================================
 */
add_action( 'wp_head', function () {
	$opts = function_exists( 'lfc_get_options' ) ? lfc_get_options() : [];
	$description = 'Condomínio Reserva Fazenda Canoa — Lotes a partir de R$ 360.000 em Silvânia/GO, às margens do Lago Corumbá IV. Infraestrutura de resort entregue: Beach Club, Marina, Heliponto ANAC, Vinícola Costa Cave.';
	$canonical   = home_url( add_query_arg( null, null ) );

	echo "\n<!-- SEO (Fazenda Canoa) -->\n";
	echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";
	echo '<meta name="googlebot" content="index, follow">' . "\n";
	echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
	echo '<meta name="format-detection" content="telephone=no">' . "\n";
	echo '<meta name="author" content="FRSC — Fazenda Reserva">' . "\n";
	echo '<meta name="geo.region" content="BR-GO">' . "\n";
	echo '<meta name="geo.placename" content="Silvânia">' . "\n";
	echo '<meta name="geo.position" content="-16.3195247;-48.4709649">' . "\n";
	echo '<meta name="ICBM" content="-16.3195247, -48.4709649">' . "\n";
}, 1 );

/**
 * ============================================================
 * OPEN GRAPH + TWITTER CARDS (mais completos)
 * ============================================================
 */
add_action( 'wp_head', function () {
	$title = wp_get_document_title();
	$desc  = 'Condomínio Reserva Fazenda Canoa — Lotes a partir de R$ 360.000 em Silvânia/GO, às margens do Lago Corumbá IV. Infraestrutura de resort entregue.';
	$url   = home_url( add_query_arg( null, null ) );
	$og    = get_theme_file_uri( 'assets/fotos/22.jpg' );

	echo "\n<!-- Open Graph -->\n";
	echo '<meta property="og:type" content="website">' . "\n";
	echo '<meta property="og:site_name" content="Reserva Fazenda Canoa">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:image" content="' . esc_url( $og ) . '">' . "\n";
	echo '<meta property="og:image:secure_url" content="' . esc_url( $og ) . '">' . "\n";
	echo '<meta property="og:image:type" content="image/jpeg">' . "\n";
	echo '<meta property="og:image:width" content="1600">' . "\n";
	echo '<meta property="og:image:height" content="1000">' . "\n";
	echo '<meta property="og:image:alt" content="Vista aérea da Reserva Fazenda Canoa com Lago Corumbá IV ao fundo">' . "\n";
	echo '<meta property="og:locale" content="pt_BR">' . "\n";

	echo "\n<!-- Twitter Cards -->\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta name="twitter:image" content="' . esc_url( $og ) . '">' . "\n";
	echo '<meta name="twitter:image:alt" content="Vista aérea da Reserva Fazenda Canoa">' . "\n";
}, 2 );

/**
 * ============================================================
 * STRUCTURED DATA (JSON-LD Schema.org)
 * ============================================================
 * Google usa essa informação para criar rich snippets, painéis
 * de conhecimento e melhor entendimento do conteúdo.
 */
add_action( 'wp_head', function () {
	$opts = function_exists( 'lfc_get_options' ) ? lfc_get_options() : [];
	$site_url  = home_url( '/' );
	$hero_img  = get_theme_file_uri( 'assets/fotos/22.jpg' );
	$logo      = get_theme_file_uri( 'assets/logos/Logo Fazenda Reserva incorporador 3.png' );
	$wa_number = preg_replace( '/\D/', '', $opts['whatsapp'] ?? '5562999593530' );
	$email     = $opts['email'] ?? 'contato@fazendacanoa.com.br';

	// Graph com múltiplas entidades (padrão Google atual)
	$graph = [
		// 1. Website
		[
			'@type'        => 'WebSite',
			'@id'          => $site_url . '#website',
			'url'          => $site_url,
			'name'         => 'Reserva Fazenda Canoa',
			'description'  => 'Landing page oficial do Condomínio Reserva Fazenda Canoa em Silvânia/GO',
			'inLanguage'   => 'pt-BR',
			'publisher'    => [ '@id' => $site_url . '#organization' ],
		],

		// 2. Organização incorporadora (FRSC)
		[
			'@type'       => 'Organization',
			'@id'         => $site_url . '#organization',
			'name'        => 'FRSC — Fazenda Reserva',
			'url'         => 'https://frsc.com.br/',
			'logo'        => [
				'@type'  => 'ImageObject',
				'url'    => $logo,
				'width'  => 1920,
				'height' => 1080,
			],
			'description' => 'Holding que investe e desenvolve projetos de alto padrão no Eixo Goiânia–Anápolis–Brasília',
			'address' => [
				'@type'           => 'PostalAddress',
				'streetAddress'   => 'Av. JK, Ed. Gênesis, Salas 1802-1808',
				'addressLocality' => 'Anápolis',
				'addressRegion'   => 'GO',
				'postalCode'      => '75110-390',
				'addressCountry'  => 'BR',
			],
			'contactPoint' => [
				'@type'             => 'ContactPoint',
				'telephone'         => '+' . $wa_number,
				'contactType'       => 'sales',
				'email'             => $email,
				'areaServed'        => 'BR',
				'availableLanguage' => [ 'Portuguese', 'pt-BR' ],
			],
			'sameAs' => [
				'https://www.instagram.com/fazendacanoa/',
				'https://frsc.com.br/',
				'https://fazendacanoa.com.br/',
			],
		],

		// 3. Empreendimento (RealEstateListing)
		[
			'@type'       => [ 'RealEstateListing', 'Product' ],
			'@id'         => $site_url . '#listing',
			'name'        => 'Condomínio Reserva Fazenda Canoa',
			'description' => 'Condomínio-Reserva de alto padrão às margens do Lago Corumbá IV em Silvânia/GO. Lotes frente-lago, vista-lago e bosque com infraestrutura de resort já entregue: Beach Club com piscina infinita, Marina Jatobá, Heliponto ANAC, Vinícola Costa Cave, Pavilhão Social, Complexo de Tênis (3 quadras oficiais), Praça do Beija-Flor, Arena Theater, Pista de Cooper e Ciclovia.',
			'url'         => $site_url,
			'image'       => [
				$hero_img,
				get_theme_file_uri( 'assets/fotos/15.jpg' ),
				get_theme_file_uri( 'assets/fotos/12.jpg' ),
				get_theme_file_uri( 'assets/fotos/08.jpg' ),
				get_theme_file_uri( 'assets/fotos/28.jpg' ),
			],
			'brand'       => [ '@id' => $site_url . '#organization' ],
			'offers'      => [
				'@type'           => 'Offer',
				'priceCurrency'   => 'BRL',
				'price'           => '360000',
				'availability'    => 'https://schema.org/InStock',
				'validFrom'       => '2026-01-01',
				'priceValidUntil' => '2027-12-31',
				'url'             => $site_url . '#oferta',
				'seller'          => [ '@id' => $site_url . '#organization' ],
			],
			'address' => [
				'@type'           => 'PostalAddress',
				'addressLocality' => 'Silvânia',
				'addressRegion'   => 'GO',
				'addressCountry'  => 'BR',
			],
			'geo' => [
				'@type'     => 'GeoCoordinates',
				'latitude'  => -16.3195247,
				'longitude' => -48.4709649,
			],
			'amenityFeature' => [
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Beach Club com piscina infinita', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => '2.000 m de orla privativa', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Marina Jatobá e 2 píeres', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Heliponto homologado ANAC', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Complexo de Tênis (3 quadras saibro)', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Pavilhão Social com cozinha gourmet', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Vinícola Costa Cave', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Arena Theater (anfiteatro)', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Campo Society + quadras poliesportivas', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Pista de Cooper + Ciclovia', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Fitness Center + SPA com sauna molhada', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Fazendinha (playground infantil)', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Portaria 24h com monitoramento', 'value' => true ],
				[ '@type' => 'LocationFeatureSpecification', 'name' => 'Garagem de 12 barcos com energia solar', 'value' => true ],
			],
		],

		// 4. Place (ponto geográfico)
		[
			'@type'   => 'Place',
			'@id'     => $site_url . '#place',
			'name'    => 'Reserva Fazenda Canoa — Silvânia/GO',
			'address' => [
				'@type'           => 'PostalAddress',
				'addressLocality' => 'Silvânia',
				'addressRegion'   => 'GO',
				'addressCountry'  => 'BR',
			],
			'geo' => [
				'@type'     => 'GeoCoordinates',
				'latitude'  => -16.3195247,
				'longitude' => -48.4709649,
			],
			'hasMap'    => 'https://www.google.com/maps/place/Fazenda+Canoa/@-16.3195247,-48.4709649,17z',
			'telephone' => '+' . $wa_number,
		],

		// 5. BreadcrumbList
		[
			'@type'           => 'BreadcrumbList',
			'@id'             => $site_url . '#breadcrumb',
			'itemListElement' => [
				[
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => 'Goiás',
					'item'     => 'https://fazendacanoa.com.br/',
				],
				[
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => 'Silvânia',
				],
				[
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => 'Condomínios',
				],
				[
					'@type'    => 'ListItem',
					'position' => 4,
					'name'     => 'Reserva Fazenda Canoa',
					'item'     => $site_url,
				],
			],
		],

		// 6. FAQPage (usa as perguntas reais do pattern faq)
		[
			'@type'      => 'FAQPage',
			'@id'        => $site_url . '#faq',
			'mainEntity' => [
				[
					'@type' => 'Question',
					'name'  => 'Onde fica o Condomínio Reserva Fazenda Canoa?',
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => 'Em Silvânia, Goiás, esculpida no topo do planalto central e às margens do Lago Corumbá IV. A 140 km de Brasília, 120 km de Goiânia e 60 km de Anápolis.',
					],
				],
				[
					'@type' => 'Question',
					'name'  => 'Qual a faixa de preço dos lotes?',
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => 'Lotes a partir de R$ 360.000, com valores que variam conforme tipologia (frente-lago, vista-lago, bosque) e disponibilidade. Parcelamento direto com a incorporadora FRSC.',
					],
				],
				[
					'@type' => 'Question',
					'name'  => 'Quais tamanhos (metragens) de lote estão disponíveis?',
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => 'Diversas metragens em três posicionamentos: frente-lago, vista-lago e bosque. Plantas e disponibilidade atualizadas semanalmente pelo time comercial.',
					],
				],
				[
					'@type' => 'Question',
					'name'  => 'Quanto da infraestrutura já foi entregue?',
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => 'Portaria 24h, Pavilhão Social, Complexo de Tênis (3 quadras oficiais), Heliponto homologado ANAC, Praça do Beija-Flor, Praça do Sabiá, Marina com Estaleiro Jatobá, garagem de barcos, píeres, Beach Club com piscina infinita, ciclovia e pista de Cooper — todos em operação.',
					],
				],
				[
					'@type' => 'Question',
					'name'  => 'O Beach Club e a marina estão prontos?',
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => 'Sim. O Beach Club conta com dois lounges, área de jogos, bar e piscina infinita. A marina possui 2 píeres (praia e rampa de barcos) e garagem coberta para 12 embarcações, com energia solar.',
					],
				],
				[
					'@type' => 'Question',
					'name'  => 'Posso financiar meu lote?',
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => 'Sim. Parcelamento direto com a incorporadora FRSC. Condições detalhadas com o consultor.',
					],
				],
				[
					'@type' => 'Question',
					'name'  => 'Quem é a FRSC, incorporadora do empreendimento?',
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => 'A FRSC é uma holding com escritório em Anápolis-GO que investe e desenvolve projetos de alto padrão no Eixo Goiânia–Anápolis–Brasília.',
					],
				],
				[
					'@type' => 'Question',
					'name'  => 'Como agendar uma visita ao empreendimento?',
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => 'Via WhatsApp ou pelo formulário da página. Visitas são agendadas — presenciais (escritório em Anápolis) ou no próprio empreendimento em Silvânia.',
					],
				],
			],
		],
	];

	$json = wp_json_encode( [
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

	echo "\n<!-- Structured Data (Schema.org) -->\n";
	echo '<script type="application/ld+json">' . $json . '</script>' . "\n";
}, 3 );

/**
 * ============================================================
 * META PIXEL + GOOGLE ADS — HARDCODED NO TEMA
 * ============================================================
 * Valores fixos no código. Não lê do banco, não depende de plugin,
 * não pode ser desligado via admin. Garante que está SEMPRE ativo.
 */
if ( ! defined( 'FC_META_PIXEL_ID' ) )   define( 'FC_META_PIXEL_ID',   '367669074650821' );
if ( ! defined( 'FC_GOOGLE_ADS_ID' ) )   define( 'FC_GOOGLE_ADS_ID',   'AW-432545598' );
if ( ! defined( 'FC_GOOGLE_ADS_CONV' ) ) define( 'FC_GOOGLE_ADS_CONV', 'AW-432545598/FJsnCKPUyaAcEL6-oM4B' );
if ( ! defined( 'FC_META_CAPI_TOKEN' ) ) define( 'FC_META_CAPI_TOKEN', 'EAATI2pWjzk8BRYfqZCZAWdUyW7HmitemkIZAf99mBy8LaVybwc2WGLXZCvLZBSUZBswdmmZCOWZCdVLdAILcjteBwsLEgBPWxG91btsDblbWa3paIu2j43fwSHp7Blk2ry5Gr99C1gMZAsS5QHKirofZA18irJuTPIOP9lSviKFZB6Trj55cNZC46qon1SEeMHdmUIcfYwZDZD' );

add_action( 'wp_head', function () {
	$px   = FC_META_PIXEL_ID;
	$aw   = FC_GOOGLE_ADS_ID;
	$conv = FC_GOOGLE_ADS_CONV;

	// Meta Pixel — snippet oficial do PDF
	echo "\n<!-- Meta Pixel Code -->\n";
	echo "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{$px}');fbq('track','PageView');</script>\n";
	echo "<noscript><img height=\"1\" width=\"1\" style=\"display:none\" src=\"https://www.facebook.com/tr?id={$px}&ev=PageView&noscript=1\"/></noscript>\n";
	echo "<!-- End Meta Pixel Code -->\n";

	// Google Ads tag (gtag.js) — snippet oficial do PDF
	echo "\n<!-- Google tag (gtag.js) -->\n";
	echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$aw}\"></script>\n";
	echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$aw}');</script>\n";

	// Config para o front-end (main.js) disparar Lead + conversion no submit
	echo "\n<!-- FC Analytics config -->\n";
	echo '<script>window.FC_ANALYTICS = ' . wp_json_encode([
		'metaPixelId'   => $px,
		'googleAdsId'   => $aw,
		'googleAdsConv' => $conv,
	]) . ';</script>' . "\n";
}, 4 );

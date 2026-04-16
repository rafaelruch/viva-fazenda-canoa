<?php
/**
 * CPT: Leads capturados pela LP
 *
 * @package LFC_Opcoes
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registra o CPT
 */
function lfc_register_cpt_leads() {
	register_post_type( 'lfc_lead', [
		'labels' => [
			'name'               => __( 'Leads', 'lfc-opcoes' ),
			'singular_name'      => __( 'Lead', 'lfc-opcoes' ),
			'menu_name'          => __( 'Leads', 'lfc-opcoes' ),
			'add_new'            => __( 'Adicionar Lead', 'lfc-opcoes' ),
			'add_new_item'       => __( 'Adicionar novo lead', 'lfc-opcoes' ),
			'edit_item'          => __( 'Editar lead', 'lfc-opcoes' ),
			'view_item'          => __( 'Ver lead', 'lfc-opcoes' ),
			'all_items'          => __( 'Todos os leads', 'lfc-opcoes' ),
			'search_items'       => __( 'Buscar leads', 'lfc-opcoes' ),
			'not_found'          => __( 'Nenhum lead encontrado.', 'lfc-opcoes' ),
			'not_found_in_trash' => __( 'Nenhum lead na lixeira.', 'lfc-opcoes' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false,
		'menu_icon'           => 'dashicons-groups',
		'menu_position'       => 25,
		'capability_type'     => 'post',
		'capabilities'        => [ 'create_posts' => 'do_not_allow' ],
		'map_meta_cap'        => true,
		'supports'            => [ 'title', 'custom-fields' ],
		'has_archive'         => false,
		'rewrite'             => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
	] );
}
add_action( 'init', 'lfc_register_cpt_leads' );

/**
 * Colunas customizadas na lista de leads
 */
add_filter( 'manage_lfc_lead_posts_columns', function ( $cols ) {
	$new = [];
	$new['cb']        = $cols['cb'] ?? '';
	$new['title']     = __( 'Nome', 'lfc-opcoes' );
	$new['telefone']  = __( 'WhatsApp', 'lfc-opcoes' );
	$new['email']     = __( 'E-mail', 'lfc-opcoes' );
	$new['interesse'] = __( 'Interesse', 'lfc-opcoes' );
	$new['contexto']  = __( 'Origem', 'lfc-opcoes' );
	$new['date']      = __( 'Data', 'lfc-opcoes' );
	return $new;
} );

add_action( 'manage_lfc_lead_posts_custom_column', function ( $col, $post_id ) {
	$val = get_post_meta( $post_id, $col, true );
	if ( $col === 'telefone' && $val ) {
		$clean = preg_replace( '/\D/', '', $val );
		$wa = 'https://wa.me/' . ( strpos( $clean, '55' ) === 0 ? $clean : '55' . $clean );
		echo '<a href="' . esc_url( $wa ) . '" target="_blank" rel="noopener">' . esc_html( $val ) . ' ↗</a>';
	} elseif ( $col === 'email' && $val ) {
		echo '<a href="mailto:' . esc_attr( $val ) . '">' . esc_html( $val ) . '</a>';
	} else {
		echo esc_html( $val ?: '—' );
	}
}, 10, 2 );

add_filter( 'manage_edit-lfc_lead_sortable_columns', function ( $cols ) {
	$cols['interesse'] = 'interesse';
	$cols['contexto']  = 'contexto';
	return $cols;
} );

/**
 * Metabox para mostrar os dados do lead (read-only)
 */
add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'lfc_lead_data',
		__( 'Dados do lead', 'lfc-opcoes' ),
		'lfc_render_lead_metabox',
		'lfc_lead',
		'normal',
		'high'
	);
} );

function lfc_render_lead_metabox( $post ) {
	$fields = [
		'nome'      => 'Nome',
		'telefone'  => 'WhatsApp',
		'email'     => 'E-mail',
		'interesse' => 'Interesse',
		'contexto'  => 'Contexto (origem da captação)',
		'source'    => 'Origem (fonte do formulário)',
		'user_agent'=> 'User-Agent',
		'referrer'  => 'Referrer',
		'ip'        => 'IP',
	];
	echo '<table class="form-table"><tbody>';
	foreach ( $fields as $key => $label ) {
		$val = get_post_meta( $post->ID, $key, true );
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . ( $val ? esc_html( $val ) : '—' ) . '</td></tr>';
	}
	$webhook = get_post_meta( $post->ID, 'webhook_status', true );
	if ( $webhook ) {
		echo '<tr><th scope="row">Webhook</th><td><code>' . esc_html( $webhook ) . '</code></td></tr>';
	}
	echo '</tbody></table>';
}

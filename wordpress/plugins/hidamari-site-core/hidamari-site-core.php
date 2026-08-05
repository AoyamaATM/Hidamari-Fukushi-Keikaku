<?php
/**
 * Plugin Name: ひだまりサイトコア
 * Description: ひだまりケア旭川の更新データと管理画面を提供します。
 * Version: 0.1.0
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * Author: ひだまりケア旭川 制作チーム
 * Text Domain: hidamari-site-core
 *
 * @package Hidamari_Site_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register content types required by the site.
 *
 * The first implementation slice covers the FAQ data used on the TOP page.
 * Flow and price data will be added with their page migrations.
 *
 * @return void
 */
function hidamari_site_core_register_content_types() {
	register_post_type(
		'hidamari_faq',
		array(
			'labels'              => array(
				'name'          => __( 'よくあるご質問', 'hidamari-site-core' ),
				'singular_name' => __( 'よくあるご質問', 'hidamari-site-core' ),
				'add_new_item'  => __( '質問を追加', 'hidamari-site-core' ),
				'edit_item'     => __( '質問を編集', 'hidamari-site-core' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-editor-help',
			'supports'            => array( 'title', 'editor', 'page-attributes' ),
			'has_archive'         => false,
			'exclude_from_search' => true,
			'rewrite'             => false,
		)
	);

	register_taxonomy(
		'hidamari_faq_cat',
		'hidamari_faq',
		array(
			'labels'            => array(
				'name'          => __( 'FAQカテゴリー', 'hidamari-site-core' ),
				'singular_name' => __( 'FAQカテゴリー', 'hidamari-site-core' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'rewrite'           => false,
		)
	);

	register_post_meta(
		'hidamari_faq',
		'hidamari_show_on_front',
		array(
			'type'              => 'boolean',
			'single'            => true,
			'default'           => false,
			'show_in_rest'      => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'hidamari_site_core_register_content_types' );

/**
 * Add the TOP display setting to the FAQ edit screen.
 *
 * @return void
 */
function hidamari_site_core_add_faq_meta_box() {
	add_meta_box(
		'hidamari-faq-front',
		__( 'TOPページ表示', 'hidamari-site-core' ),
		'hidamari_site_core_render_faq_meta_box',
		'hidamari_faq',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes_hidamari_faq', 'hidamari_site_core_add_faq_meta_box' );

/**
 * Render the FAQ TOP display checkbox.
 *
 * @param WP_Post $post FAQ post.
 * @return void
 */
function hidamari_site_core_render_faq_meta_box( $post ) {
	$show_on_front = (bool) get_post_meta( $post->ID, 'hidamari_show_on_front', true );
	wp_nonce_field( 'hidamari_save_faq_front', 'hidamari_faq_front_nonce' );
	?>
	<label>
		<input type="checkbox" name="hidamari_show_on_front" value="1" <?php checked( $show_on_front ); ?>>
		<?php esc_html_e( 'TOPページに掲載する', 'hidamari-site-core' ); ?>
	</label>
	<?php
}

/**
 * Save the FAQ TOP display setting.
 *
 * @param int $post_id FAQ post ID.
 * @return void
 */
function hidamari_site_core_save_faq_meta( $post_id ) {
	$nonce = isset( $_POST['hidamari_faq_front_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['hidamari_faq_front_nonce'] ) ) : '';

	if ( ! wp_verify_nonce( $nonce, 'hidamari_save_faq_front' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, 'hidamari_show_on_front', isset( $_POST['hidamari_show_on_front'] ) );
}
add_action( 'save_post_hidamari_faq', 'hidamari_site_core_save_faq_meta' );

/**
 * Refresh rewrite rules when the plugin is activated.
 *
 * @return void
 */
function hidamari_site_core_activate() {
	hidamari_site_core_register_content_types();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'hidamari_site_core_activate' );

/**
 * Refresh rewrite rules when the plugin is deactivated.
 *
 * @return void
 */
function hidamari_site_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'hidamari_site_core_deactivate' );

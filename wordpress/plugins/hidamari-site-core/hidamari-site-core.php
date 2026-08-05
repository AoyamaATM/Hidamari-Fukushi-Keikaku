<?php
/**
 * Plugin Name: ひだまりサイトコア
 * Description: ひだまりケア旭川の更新データと管理画面を提供します。
 * Version: 0.2.0
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
 * Check whether the current user may edit site content meta.
 *
 * @return bool
 */
function hidamari_site_core_can_edit_meta() {
	return current_user_can( 'edit_posts' );
}

/**
 * Check whether a post meta save should be skipped.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function hidamari_site_core_should_skip_save( $post_id ) {
	return ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || (bool) wp_is_post_revision( $post_id );
}

/**
 * Register content types and their stored fields.
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
			'supports'            => array( 'title', 'editor', 'page-attributes', 'custom-fields' ),
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

	register_post_type(
		'hidamari_flow',
		array(
			'labels'              => array(
				'name'          => __( 'ご利用の流れ', 'hidamari-site-core' ),
				'singular_name' => __( 'ご利用の流れ', 'hidamari-site-core' ),
				'add_new_item'  => __( 'ステップを追加', 'hidamari-site-core' ),
				'edit_item'     => __( 'ステップを編集', 'hidamari-site-core' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-list-view',
			'supports'            => array( 'title', 'editor', 'page-attributes', 'custom-fields' ),
			'has_archive'         => false,
			'exclude_from_search' => true,
			'rewrite'             => false,
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
			'auth_callback'     => 'hidamari_site_core_can_edit_meta',
		)
	);

	$flow_meta = array(
		'hidamari_flow_note'       => 'sanitize_textarea_field',
		'hidamari_flow_link_label' => 'sanitize_text_field',
		'hidamari_flow_link_url'   => 'esc_url_raw',
	);

	foreach ( $flow_meta as $meta_key => $sanitize_callback ) {
		register_post_meta(
			'hidamari_flow',
			$meta_key,
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => $sanitize_callback,
				'auth_callback'     => 'hidamari_site_core_can_edit_meta',
			)
		);
	}

	register_post_meta(
		'page',
		'hidamari_page_lead',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_textarea_field',
			'auth_callback'     => 'hidamari_site_core_can_edit_meta',
		)
	);

	register_post_meta(
		'page',
		'hidamari_hero_mobile_id',
		array(
			'type'              => 'integer',
			'single'            => true,
			'default'           => 0,
			'show_in_rest'      => true,
			'sanitize_callback' => 'absint',
			'auth_callback'     => 'hidamari_site_core_can_edit_meta',
		)
	);

	foreach ( hidamari_site_core_about_image_slots() as $image_key => $label ) {
		register_post_meta(
			'page',
			'hidamari_page_' . $image_key . '_image_id',
			array(
				'type'              => 'integer',
				'single'            => true,
				'default'           => 0,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => 'hidamari_site_core_can_edit_meta',
			)
		);
	}
}
add_action( 'init', 'hidamari_site_core_register_content_types' );

/**
 * Return image slots managed on the facility introduction page.
 *
 * @return array<string, string>
 */
function hidamari_site_core_about_image_slots() {
	return array(
		'profile'       => __( '法人情報の写真', 'hidamari-site-core' ),
		'service_01'    => __( 'デイサービスの写真', 'hidamari-site-core' ),
		'schedule'      => __( 'タイムスケジュール画像', 'hidamari-site-core' ),
		'dayservice_01' => __( 'デイサービス写真1', 'hidamari-site-core' ),
		'dayservice_02' => __( 'デイサービス写真2', 'hidamari-site-core' ),
		'dayservice_03' => __( 'デイサービス写真3', 'hidamari-site-core' ),
		'service_02'    => __( '訪問介護の写真', 'hidamari-site-core' ),
		'service_03'    => __( '介護相談の写真', 'hidamari-site-core' ),
	);
}

/**
 * Add content meta boxes.
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
	$raw_nonce = isset( $_POST['hidamari_faq_front_nonce'] ) ? wp_unslash( $_POST['hidamari_faq_front_nonce'] ) : '';
	$nonce     = is_string( $raw_nonce ) ? sanitize_text_field( $raw_nonce ) : '';

	if ( hidamari_site_core_should_skip_save( $post_id ) || ! wp_verify_nonce( $nonce, 'hidamari_save_faq_front' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, 'hidamari_show_on_front', isset( $_POST['hidamari_show_on_front'] ) );
}
add_action( 'save_post_hidamari_faq', 'hidamari_site_core_save_faq_meta' );

/**
 * Add the optional flow detail fields.
 *
 * @return void
 */
function hidamari_site_core_add_flow_meta_box() {
	add_meta_box(
		'hidamari-flow-details',
		__( 'ステップの補足とリンク', 'hidamari-site-core' ),
		'hidamari_site_core_render_flow_meta_box',
		'hidamari_flow',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_hidamari_flow', 'hidamari_site_core_add_flow_meta_box' );

/**
 * Render optional flow fields.
 *
 * @param WP_Post $post Flow post.
 * @return void
 */
function hidamari_site_core_render_flow_meta_box( $post ) {
	$note       = (string) get_post_meta( $post->ID, 'hidamari_flow_note', true );
	$link_label = (string) get_post_meta( $post->ID, 'hidamari_flow_link_label', true );
	$link_url   = (string) get_post_meta( $post->ID, 'hidamari_flow_link_url', true );
	wp_nonce_field( 'hidamari_save_flow_details', 'hidamari_flow_details_nonce' );
	?>
	<p>
		<label for="hidamari_flow_note"><strong><?php esc_html_e( '補足文', 'hidamari-site-core' ); ?></strong></label><br>
		<textarea class="widefat" id="hidamari_flow_note" name="hidamari_flow_note" rows="3"><?php echo esc_textarea( $note ); ?></textarea>
	</p>
	<p>
		<label for="hidamari_flow_link_label"><strong><?php esc_html_e( 'リンク表示名', 'hidamari-site-core' ); ?></strong></label><br>
		<input class="widefat" id="hidamari_flow_link_label" name="hidamari_flow_link_label" type="text" value="<?php echo esc_attr( $link_label ); ?>">
	</p>
	<p>
		<label for="hidamari_flow_link_url"><strong><?php esc_html_e( 'リンクURL', 'hidamari-site-core' ); ?></strong></label><br>
		<input class="widefat" id="hidamari_flow_link_url" name="hidamari_flow_link_url" type="url" value="<?php echo esc_attr( $link_url ); ?>">
	</p>
	<?php
}

/**
 * Save optional flow fields.
 *
 * @param int $post_id Flow post ID.
 * @return void
 */
function hidamari_site_core_save_flow_meta( $post_id ) {
	$raw_nonce = isset( $_POST['hidamari_flow_details_nonce'] ) ? wp_unslash( $_POST['hidamari_flow_details_nonce'] ) : '';
	$nonce     = is_string( $raw_nonce ) ? sanitize_text_field( $raw_nonce ) : '';

	if ( hidamari_site_core_should_skip_save( $post_id ) || ! wp_verify_nonce( $nonce, 'hidamari_save_flow_details' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'hidamari_flow_note'       => 'sanitize_textarea_field',
		'hidamari_flow_link_label' => 'sanitize_text_field',
		'hidamari_flow_link_url'   => 'esc_url_raw',
	);

	foreach ( $fields as $meta_key => $sanitize_callback ) {
		$raw_value = isset( $_POST[ $meta_key ] ) ? wp_unslash( $_POST[ $meta_key ] ) : '';
		$raw_value = is_string( $raw_value ) ? $raw_value : '';
		$value     = call_user_func( $sanitize_callback, $raw_value );
		update_post_meta( $post_id, $meta_key, $value );
	}
}
add_action( 'save_post_hidamari_flow', 'hidamari_site_core_save_flow_meta' );

/**
 * Add page introduction and image fields.
 *
 * @return void
 */
function hidamari_site_core_add_page_meta_box() {
	add_meta_box(
		'hidamari-page-media',
		__( 'ページ導入情報・画像', 'hidamari-site-core' ),
		'hidamari_site_core_render_page_meta_box',
		'page',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_page', 'hidamari_site_core_add_page_meta_box' );

/**
 * Render a reusable media-library selector.
 *
 * @param int    $post_id  Page ID.
 * @param string $meta_key Meta key.
 * @param string $label    Field label.
 * @return void
 */
function hidamari_site_core_render_media_field( $post_id, $meta_key, $label ) {
	$attachment_id = (int) get_post_meta( $post_id, $meta_key, true );
	$preview_url   = $attachment_id > 0 ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
	$preview_id    = $meta_key . '_preview';
	?>
	<div style="margin: 0 0 18px;">
		<p><strong><?php echo esc_html( $label ); ?></strong></p>
		<img id="<?php echo esc_attr( $preview_id ); ?>" src="<?php echo esc_url( $preview_url ); ?>" alt="" style="display:block;max-width:240px;height:auto;margin-bottom:8px;" <?php hidden( '' === $preview_url ); ?>>
		<input id="<?php echo esc_attr( $meta_key ); ?>" name="<?php echo esc_attr( $meta_key ); ?>" type="hidden" value="<?php echo esc_attr( $attachment_id ); ?>">
		<button class="button hidamari-media-select" type="button" data-target="<?php echo esc_attr( $meta_key ); ?>" data-preview="<?php echo esc_attr( $preview_id ); ?>"><?php esc_html_e( '画像を選択', 'hidamari-site-core' ); ?></button>
		<button class="button-link-delete hidamari-media-remove" type="button" data-target="<?php echo esc_attr( $meta_key ); ?>" data-preview="<?php echo esc_attr( $preview_id ); ?>"><?php esc_html_e( '画像を解除', 'hidamari-site-core' ); ?></button>
	</div>
	<?php
}

/**
 * Render page introduction and image fields.
 *
 * @param WP_Post $post Page post.
 * @return void
 */
function hidamari_site_core_render_page_meta_box( $post ) {
	$lead = (string) get_post_meta( $post->ID, 'hidamari_page_lead', true );
	wp_nonce_field( 'hidamari_save_page_media', 'hidamari_page_media_nonce' );
	?>
	<p>
		<label for="hidamari_page_lead"><strong><?php esc_html_e( '導入文', 'hidamari-site-core' ); ?></strong></label><br>
		<textarea class="widefat" id="hidamari_page_lead" name="hidamari_page_lead" rows="4"><?php echo esc_textarea( $lead ); ?></textarea>
	</p>
	<p><?php esc_html_e( 'PCヒーロー画像は右側の「アイキャッチ画像」で設定します。', 'hidamari-site-core' ); ?></p>
	<?php
	hidamari_site_core_render_media_field( $post->ID, 'hidamari_hero_mobile_id', __( 'SPヒーロー画像', 'hidamari-site-core' ) );

	if ( 'about-us' === $post->post_name ) {
		foreach ( hidamari_site_core_about_image_slots() as $image_key => $label ) {
			hidamari_site_core_render_media_field( $post->ID, 'hidamari_page_' . $image_key . '_image_id', $label );
		}
	}
}

/**
 * Save page introduction and image fields.
 *
 * @param int $post_id Page ID.
 * @return void
 */
function hidamari_site_core_save_page_meta( $post_id ) {
	$raw_nonce = isset( $_POST['hidamari_page_media_nonce'] ) ? wp_unslash( $_POST['hidamari_page_media_nonce'] ) : '';
	$nonce     = is_string( $raw_nonce ) ? sanitize_text_field( $raw_nonce ) : '';

	if ( hidamari_site_core_should_skip_save( $post_id ) || ! wp_verify_nonce( $nonce, 'hidamari_save_page_media' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$raw_lead = isset( $_POST['hidamari_page_lead'] ) ? wp_unslash( $_POST['hidamari_page_lead'] ) : '';
	$lead     = is_string( $raw_lead ) ? sanitize_textarea_field( $raw_lead ) : '';
	update_post_meta( $post_id, 'hidamari_page_lead', $lead );

	$image_meta_keys = array( 'hidamari_hero_mobile_id' );
	foreach ( hidamari_site_core_about_image_slots() as $image_key => $label ) {
		$image_meta_keys[] = 'hidamari_page_' . $image_key . '_image_id';
	}

	foreach ( $image_meta_keys as $meta_key ) {
		$attachment_id = isset( $_POST[ $meta_key ] ) ? absint( $_POST[ $meta_key ] ) : 0;
		update_post_meta( $post_id, $meta_key, $attachment_id );
	}
}
add_action( 'save_post_page', 'hidamari_site_core_save_page_meta' );

/**
 * Load the WordPress media selector on page edit screens.
 *
 * @param string $hook_suffix Admin screen hook.
 * @return void
 */
function hidamari_site_core_enqueue_page_media( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'jquery' );
	wp_add_inline_script(
		'jquery',
		<<<'JS'
jQuery(function ($) {
	$('.hidamari-media-select').on('click', function () {
		const button = $(this);
		const target = $('#' + button.data('target'));
		const preview = $('#' + button.data('preview'));
		const frame = wp.media({
			title: '画像を選択',
			button: { text: 'この画像を使用' },
			multiple: false
		});
		frame.on('select', function () {
			const attachment = frame.state().get('selection').first().toJSON();
			const previewUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
			target.val(attachment.id);
			preview.attr('src', previewUrl).prop('hidden', false);
		});
		frame.open();
	});

	$('.hidamari-media-remove').on('click', function () {
		$('#' + $(this).data('target')).val('0');
		$('#' + $(this).data('preview')).attr('src', '').prop('hidden', true);
	});
});
JS
	);
}
add_action( 'admin_enqueue_scripts', 'hidamari_site_core_enqueue_page_media' );

/**
 * Sanitize shared facility settings.
 *
 * @param mixed $input Submitted value.
 * @return array<string, string>
 */
function hidamari_site_core_sanitize_settings( $input ) {
	$input = is_array( $input ) ? $input : array();
	$keys  = array( 'organization_name', 'facility_name', 'services_label', 'address', 'facility_phone_display', 'facility_phone_link', 'phone_display', 'phone_link', 'business_hours' );
	$value = array();

	foreach ( $keys as $key ) {
		$raw_value     = isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ? (string) $input[ $key ] : '';
		$value[ $key ] = sanitize_text_field( $raw_value );
	}

	return $value;
}

/**
 * Register the shared facility settings page.
 *
 * @return void
 */
function hidamari_site_core_register_settings() {
	register_setting(
		'hidamari_settings_group',
		'hidamari_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'hidamari_site_core_sanitize_settings',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'hidamari_site_core_register_settings' );

/**
 * Add the shared facility settings page.
 *
 * @return void
 */
function hidamari_site_core_add_settings_page() {
	add_options_page(
		__( 'ひだまり設定', 'hidamari-site-core' ),
		__( 'ひだまり設定', 'hidamari-site-core' ),
		'manage_options',
		'hidamari-settings',
		'hidamari_site_core_render_settings_page'
	);
}
add_action( 'admin_menu', 'hidamari_site_core_add_settings_page' );

/**
 * Render the shared facility settings page.
 *
 * @return void
 */
function hidamari_site_core_render_settings_page() {
	$settings = get_option( 'hidamari_settings', array() );
	$settings = is_array( $settings ) ? $settings : array();
	$fields   = array(
		'organization_name'      => __( '法人名', 'hidamari-site-core' ),
		'facility_name'          => __( '施設名', 'hidamari-site-core' ),
		'services_label'         => __( '提供サービス表記', 'hidamari-site-core' ),
		'address'                => __( '住所', 'hidamari-site-core' ),
		'facility_phone_display' => __( '施設電話番号（表示用）', 'hidamari-site-core' ),
		'facility_phone_link'    => __( '施設電話番号（発信用）', 'hidamari-site-core' ),
		'phone_display'          => __( 'お問い合わせ電話番号（表示用）', 'hidamari-site-core' ),
		'phone_link'             => __( 'お問い合わせ電話番号（発信用）', 'hidamari-site-core' ),
		'business_hours'         => __( '受付時間', 'hidamari-site-core' ),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'ひだまり設定', 'hidamari-site-core' ); ?></h1>
		<form action="options.php" method="post">
			<?php settings_fields( 'hidamari_settings_group' ); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<?php foreach ( $fields as $key => $label ) : ?>
						<tr>
							<th scope="row"><label for="hidamari_settings_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
							<td><input class="regular-text" id="hidamari_settings_<?php echo esc_attr( $key ); ?>" name="hidamari_settings[<?php echo esc_attr( $key ); ?>]" type="text" value="<?php echo esc_attr( isset( $settings[ $key ] ) ? $settings[ $key ] : '' ); ?>"></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

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

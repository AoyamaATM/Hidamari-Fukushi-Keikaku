<?php
/**
 * Plugin Name: ひだまりサイトコア
 * Description: ひだまりケア旭川の更新データと管理画面を提供します。
 * Version: 0.6.0
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
 * @param bool   $allowed   Whether access is currently allowed.
 * @param string $meta_key  Meta key being checked.
 * @param int    $object_id Object ID being checked.
 * @return bool
 */
function hidamari_site_core_can_edit_meta( $allowed = false, $meta_key = '', $object_id = 0 ) {
	unset( $allowed, $meta_key );

	return $object_id > 0 ? current_user_can( 'edit_post', $object_id ) : current_user_can( 'edit_posts' );
}

/**
 * Return the fixed pages that facility editors may update.
 *
 * Titles, slugs, publication state and page structure remain administrator-managed.
 *
 * @return string[]
 */
function hidamari_site_core_editable_page_slugs() {
	return array( 'home', 'about-us', 'facilities', 'price', 'faq', 'contact', 'privacy-policy' );
}

/**
 * Check whether a user is a non-administrator who maintains facility content.
 *
 * @param int $user_id User ID. Defaults to the current user.
 * @return bool
 */
function hidamari_site_core_is_facility_editor( $user_id = 0 ) {
	$user = $user_id > 0 ? get_userdata( $user_id ) : wp_get_current_user();

	return $user instanceof WP_User && $user->exists() && ! empty( $user->allcaps['edit_pages'] ) && empty( $user->allcaps['manage_options'] );
}

/**
 * Check whether a fixed page is in the facility-editor update scope.
 *
 * @param int|WP_Post $post Page ID or object.
 * @return bool
 */
function hidamari_site_core_is_editable_page( $post ) {
	$post = get_post( $post );

	return $post instanceof WP_Post && 'page' === $post->post_type && in_array( $post->post_name, hidamari_site_core_editable_page_slugs(), true );
}

/**
 * Limit facility editors to the designated fixed pages and prevent page deletion.
 *
 * @param string[] $caps    Primitive capabilities required by WordPress.
 * @param string   $cap     Requested meta capability.
 * @param int      $user_id User ID.
 * @param mixed[]  $args    Capability arguments. The first item is the post ID.
 * @return string[]
 */
function hidamari_site_core_restrict_page_capabilities( $caps, $cap, $user_id, $args ) {
	if ( ! in_array( $cap, array( 'edit_post', 'edit_page', 'delete_post', 'delete_page' ), true ) || empty( $args[0] ) ) {
		return $caps;
	}

	if ( ! hidamari_site_core_is_facility_editor( $user_id ) ) {
		return $caps;
	}

	$post = get_post( (int) $args[0] );
	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return $caps;
	}

	if ( in_array( $cap, array( 'delete_post', 'delete_page' ), true ) || ! hidamari_site_core_is_editable_page( $post ) ) {
		return array( 'do_not_allow' );
	}

	return array_values( array_diff( $caps, array( 'manage_options', 'manage_privacy_options' ) ) );
}
add_filter( 'map_meta_cap', 'hidamari_site_core_restrict_page_capabilities', 10, 4 );

/**
 * Prevent facility editors from creating additional fixed pages.
 *
 * @param bool  $maybe_empty Whether WordPress should reject the post as empty.
 * @param array $postarr     Submitted post data.
 * @return bool
 */
function hidamari_site_core_prevent_page_creation( $maybe_empty, $postarr ) {
	if ( hidamari_site_core_is_facility_editor() && 'page' === ( $postarr['post_type'] ?? '' ) && empty( $postarr['ID'] ) ) {
		return true;
	}

	return $maybe_empty;
}
add_filter( 'wp_insert_post_empty_content', 'hidamari_site_core_prevent_page_creation', 10, 2 );

/**
 * Preserve administrator-managed fixed-page fields during facility-editor updates.
 *
 * @param array $data    Sanitized post data.
 * @param array $postarr Raw submitted post data.
 * @return array
 */
function hidamari_site_core_protect_page_structure( $data, $postarr ) {
	if ( ! hidamari_site_core_is_facility_editor() || 'page' !== ( $data['post_type'] ?? '' ) || empty( $postarr['ID'] ) ) {
		return $data;
	}

	$original = get_post( (int) $postarr['ID'], ARRAY_A );
	if ( ! is_array( $original ) ) {
		return $data;
	}

	$protected_fields = array( 'post_title', 'post_name', 'post_status', 'post_parent', 'menu_order', 'post_author', 'post_password', 'comment_status', 'ping_status' );
	if ( ! hidamari_site_core_is_editable_page( (int) $postarr['ID'] ) ) {
		$protected_fields[] = 'post_content';
		$protected_fields[] = 'post_excerpt';
	}

	foreach ( $protected_fields as $field ) {
		if ( array_key_exists( $field, $original ) ) {
			$data[ $field ] = $original[ $field ];
		}
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'hidamari_site_core_protect_page_structure', 10, 2 );

/**
 * Keep page templates administrator-managed for facility editors.
 *
 * @param null|bool $check      Short-circuit value.
 * @param int       $object_id  Post ID.
 * @param string    $meta_key   Meta key.
 * @param mixed     $meta_value Submitted value.
 * @return null|bool
 */
function hidamari_site_core_protect_page_template( $check, $object_id, $meta_key, $meta_value ) {
	unset( $meta_value );

	if ( hidamari_site_core_is_facility_editor() && '_wp_page_template' === $meta_key && 'page' === get_post_type( $object_id ) ) {
		return true;
	}

	return $check;
}
add_filter( 'update_post_metadata', 'hidamari_site_core_protect_page_template', 10, 4 );

/**
 * Remove fixed-page creation, bulk editing and destructive row actions for facility editors.
 *
 * @return void
 */
function hidamari_site_core_restrict_page_admin_menu() {
	if ( hidamari_site_core_is_facility_editor() ) {
		remove_submenu_page( 'edit.php?post_type=page', 'post-new.php?post_type=page' );
	}
}
add_action( 'admin_menu', 'hidamari_site_core_restrict_page_admin_menu', 999 );

/**
 * Filter fixed-page row actions for facility editors.
 *
 * @param array   $actions Row actions.
 * @param WP_Post $post    Page post.
 * @return array
 */
function hidamari_site_core_filter_page_row_actions( $actions, $post ) {
	if ( ! hidamari_site_core_is_facility_editor() ) {
		return $actions;
	}

	unset( $actions['inline hide-if-no-js'], $actions['trash'], $actions['delete'] );
	if ( ! hidamari_site_core_is_editable_page( $post ) ) {
		unset( $actions['edit'] );
	}

	return $actions;
}
add_filter( 'page_row_actions', 'hidamari_site_core_filter_page_row_actions', 10, 2 );

/**
 * Remove fixed-page bulk actions for facility editors.
 *
 * @param array $actions Bulk actions.
 * @return array
 */
function hidamari_site_core_filter_page_bulk_actions( $actions ) {
	if ( hidamari_site_core_is_facility_editor() ) {
		unset( $actions['edit'], $actions['trash'] );
	}

	return $actions;
}
add_filter( 'bulk_actions-edit-page', 'hidamari_site_core_filter_page_bulk_actions' );

/**
 * Hide administrator-managed page controls from facility editors.
 *
 * @return void
 */
function hidamari_site_core_limit_page_editor_supports() {
	if ( is_admin() && hidamari_site_core_is_facility_editor() ) {
		remove_post_type_support( 'page', 'title' );
		remove_post_type_support( 'page', 'page-attributes' );
	}
}
add_action( 'init', 'hidamari_site_core_limit_page_editor_supports', 100 );

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
 * Return the fixed price-table groups.
 *
 * @return array<string, string>
 */
function hidamari_site_core_price_groups() {
	return array(
		'day-basic'        => __( 'デイサービス・基本料金', 'hidamari-site-core' ),
		'day-addition'     => __( 'デイサービス・主な加算料金', 'hidamari-site-core' ),
		'day-outside'      => __( 'デイサービス・介護保険外料金', 'hidamari-site-core' ),
		'visit-physical'   => __( '訪問介護・身体介護', 'hidamari-site-core' ),
		'visit-housework'  => __( '訪問介護・生活援助', 'hidamari-site-core' ),
		'visit-prevention' => __( '訪問介護・介護予防訪問サービス', 'hidamari-site-core' ),
		'visit-addition'   => __( '訪問介護・主な加算料金', 'hidamari-site-core' ),
		'visit-outside'    => __( '訪問介護・介護保険外料金', 'hidamari-site-core' ),
	);
}

/**
 * Sanitize a price-table group key.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function hidamari_site_core_sanitize_price_group( $value ) {
	$value = is_string( $value ) ? sanitize_key( $value ) : '';
	return isset( hidamari_site_core_price_groups()[ $value ] ) ? $value : '';
}

/**
 * Sanitize a price-row display type.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function hidamari_site_core_sanitize_price_row_type( $value ) {
	$value = is_string( $value ) ? sanitize_key( $value ) : '';
	return in_array( $value, array( 'normal', 'description', 'note' ), true ) ? $value : 'normal';
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

	register_post_type(
		'hidamari_price',
		array(
			'labels'              => array(
				'name'          => __( '料金表', 'hidamari-site-core' ),
				'singular_name' => __( '料金行', 'hidamari-site-core' ),
				'add_new_item'  => __( '料金行を追加', 'hidamari-site-core' ),
				'edit_item'     => __( '料金行を編集', 'hidamari-site-core' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-money-alt',
			'supports'            => array( 'title', 'page-attributes', 'custom-fields' ),
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

	register_post_meta(
		'hidamari_faq',
		'hidamari_front_order',
		array(
			'type'              => 'integer',
			'single'            => true,
			'default'           => 0,
			'show_in_rest'      => true,
			'sanitize_callback' => 'absint',
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

	$price_meta = array(
		'hidamari_price_group'    => 'hidamari_site_core_sanitize_price_group',
		'hidamari_price_row_type' => 'hidamari_site_core_sanitize_price_row_type',
		'hidamari_price_cell_1'   => 'sanitize_text_field',
		'hidamari_price_cell_2'   => 'sanitize_text_field',
		'hidamari_price_cell_3'   => 'sanitize_text_field',
		'hidamari_price_cell_4'   => 'sanitize_text_field',
	);

	foreach ( $price_meta as $meta_key => $sanitize_callback ) {
		register_post_meta(
			'hidamari_price',
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

	foreach ( hidamari_site_core_all_page_image_slots() as $image_key => $label ) {
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
 * Return image slots managed on the facilities page.
 *
 * @return array<string, string>
 */
function hidamari_site_core_facilities_image_slots() {
	return array(
		'facilities_organization' => __( '法人情報の写真', 'hidamari-site-core' ),
		'facilities_facility'     => __( '施設情報の写真', 'hidamari-site-core' ),
		'facilities_staff'        => __( 'スタッフ紹介の写真', 'hidamari-site-core' ),
	);
}

/**
 * Return image slots managed on the price page.
 *
 * @return array<string, string>
 */
function hidamari_site_core_price_image_slots() {
	return array(
		'price_day_anchor'   => __( 'デイサービス料金表へのリンク画像', 'hidamari-site-core' ),
		'price_visit_anchor' => __( '訪問介護料金表へのリンク画像', 'hidamari-site-core' ),
	);
}

/**
 * Return image slots managed on the FAQ page.
 *
 * @return array<string, string>
 */
function hidamari_site_core_faq_image_slots() {
	return array(
		'faq_payment_anchor'      => __( '費用・お支払いカテゴリー画像', 'hidamari-site-core' ),
		'faq_day_care_anchor'     => __( '訪問介護・生活援助カテゴリー画像', 'hidamari-site-core' ),
		'faq_consultation_anchor' => __( 'ご相談・居宅介護支援カテゴリー画像', 'hidamari-site-core' ),
		'faq_facility_anchor'     => __( '施設での生活カテゴリー画像', 'hidamari-site-core' ),
	);
}

/**
 * Return page-specific image slots for one fixed-page slug.
 *
 * @param string $page_slug Fixed-page slug.
 * @return array<string, string>
 */
function hidamari_site_core_page_image_slots( $page_slug ) {
	$slot_groups = array(
		'about-us'   => hidamari_site_core_about_image_slots(),
		'facilities' => hidamari_site_core_facilities_image_slots(),
		'price'      => hidamari_site_core_price_image_slots(),
		'faq'        => hidamari_site_core_faq_image_slots(),
	);

	return isset( $slot_groups[ $page_slug ] ) ? $slot_groups[ $page_slug ] : array();
}

/**
 * Return every registered fixed-page image slot.
 *
 * @return array<string, string>
 */
function hidamari_site_core_all_page_image_slots() {
	return array_merge(
		hidamari_site_core_about_image_slots(),
		hidamari_site_core_facilities_image_slots(),
		hidamari_site_core_price_image_slots(),
		hidamari_site_core_faq_image_slots()
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
	$front_order   = (int) get_post_meta( $post->ID, 'hidamari_front_order', true );
	wp_nonce_field( 'hidamari_save_faq_front', 'hidamari_faq_front_nonce' );
	?>
	<p><label>
		<input type="checkbox" name="hidamari_show_on_front" value="1" <?php checked( $show_on_front ); ?>>
		<?php esc_html_e( 'TOPページに掲載する', 'hidamari-site-core' ); ?>
	</label></p>
	<p>
		<label for="hidamari_front_order"><strong><?php esc_html_e( 'TOP表示順', 'hidamari-site-core' ); ?></strong></label><br>
		<input id="hidamari_front_order" name="hidamari_front_order" type="number" min="0" step="1" value="<?php echo esc_attr( $front_order ); ?>" style="width:100%;">
	</p>
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

	$show_on_front   = isset( $_POST['hidamari_show_on_front'] );
	$raw_front_order = isset( $_POST['hidamari_front_order'] ) ? wp_unslash( $_POST['hidamari_front_order'] ) : 0;
	$front_order     = $show_on_front ? absint( $raw_front_order ) : 0;

	update_post_meta( $post_id, 'hidamari_show_on_front', $show_on_front );
	update_post_meta( $post_id, 'hidamari_front_order', $front_order );
}
add_action( 'save_post_hidamari_faq', 'hidamari_site_core_save_faq_meta' );

/**
 * Add the fixed-structure price-row fields.
 *
 * @return void
 */
function hidamari_site_core_add_price_meta_box() {
	add_meta_box(
		'hidamari-price-details',
		__( '料金行の内容', 'hidamari-site-core' ),
		'hidamari_site_core_render_price_meta_box',
		'hidamari_price',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_hidamari_price', 'hidamari_site_core_add_price_meta_box' );

/**
 * Render the fixed-structure price-row fields.
 *
 * @param WP_Post $post Price-row post.
 * @return void
 */
function hidamari_site_core_render_price_meta_box( $post ) {
	$group_key = (string) get_post_meta( $post->ID, 'hidamari_price_group', true );
	$row_type  = (string) get_post_meta( $post->ID, 'hidamari_price_row_type', true );
	$row_type  = hidamari_site_core_sanitize_price_row_type( $row_type );
	wp_nonce_field( 'hidamari_save_price_details', 'hidamari_price_details_nonce' );
	?>
	<p>
		<label for="hidamari_price_group"><strong><?php esc_html_e( '料金表グループ', 'hidamari-site-core' ); ?></strong></label><br>
		<select class="widefat" id="hidamari_price_group" name="hidamari_price_group" required>
			<option value=""><?php esc_html_e( '選択してください', 'hidamari-site-core' ); ?></option>
			<?php foreach ( hidamari_site_core_price_groups() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $group_key, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="hidamari_price_row_type"><strong><?php esc_html_e( '行タイプ', 'hidamari-site-core' ); ?></strong></label><br>
		<select class="widefat" id="hidamari_price_row_type" name="hidamari_price_row_type">
			<option value="normal" <?php selected( $row_type, 'normal' ); ?>><?php esc_html_e( '通常行', 'hidamari-site-core' ); ?></option>
			<option value="description" <?php selected( $row_type, 'description' ); ?>><?php esc_html_e( '説明行', 'hidamari-site-core' ); ?></option>
			<option value="note" <?php selected( $row_type, 'note' ); ?>><?php esc_html_e( '注記行', 'hidamari-site-core' ); ?></option>
		</select>
	</p>
	<?php for ( $cell_number = 1; $cell_number <= 4; $cell_number++ ) : ?>
		<?php
		$meta_key   = 'hidamari_price_cell_' . $cell_number;
		$cell_value = (string) get_post_meta( $post->ID, $meta_key, true );
		?>
		<p>
			<label for="<?php echo esc_attr( $meta_key ); ?>"><strong><?php echo esc_html( sprintf( __( 'セル%d', 'hidamari-site-core' ), $cell_number ) ); ?></strong></label><br>
			<input class="widefat" id="<?php echo esc_attr( $meta_key ); ?>" name="<?php echo esc_attr( $meta_key ); ?>" type="text" value="<?php echo esc_attr( $cell_value ); ?>">
		</p>
	<?php endfor; ?>
	<p><?php esc_html_e( '列見出しと列数はテーマで固定されています。表示順は右側の「順序」で調整してください。', 'hidamari-site-core' ); ?></p>
	<?php
}

/**
 * Save the fixed-structure price-row fields.
 *
 * @param int $post_id Price-row post ID.
 * @return void
 */
function hidamari_site_core_save_price_meta( $post_id ) {
	$raw_nonce = isset( $_POST['hidamari_price_details_nonce'] ) ? wp_unslash( $_POST['hidamari_price_details_nonce'] ) : '';
	$nonce     = is_string( $raw_nonce ) ? sanitize_text_field( $raw_nonce ) : '';

	if ( hidamari_site_core_should_skip_save( $post_id ) || ! wp_verify_nonce( $nonce, 'hidamari_save_price_details' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'hidamari_price_group'    => 'hidamari_site_core_sanitize_price_group',
		'hidamari_price_row_type' => 'hidamari_site_core_sanitize_price_row_type',
		'hidamari_price_cell_1'   => 'sanitize_text_field',
		'hidamari_price_cell_2'   => 'sanitize_text_field',
		'hidamari_price_cell_3'   => 'sanitize_text_field',
		'hidamari_price_cell_4'   => 'sanitize_text_field',
	);

	foreach ( $fields as $meta_key => $sanitize_callback ) {
		$raw_value = isset( $_POST[ $meta_key ] ) ? wp_unslash( $_POST[ $meta_key ] ) : '';
		$raw_value = is_string( $raw_value ) ? $raw_value : '';
		$value     = call_user_func( $sanitize_callback, $raw_value );
		update_post_meta( $post_id, $meta_key, $value );
	}
}
add_action( 'save_post_hidamari_price', 'hidamari_site_core_save_price_meta' );

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

	foreach ( hidamari_site_core_page_image_slots( $post->post_name ) as $image_key => $label ) {
		hidamari_site_core_render_media_field( $post->ID, 'hidamari_page_' . $image_key . '_image_id', $label );
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

	$page = get_post( $post_id );
	if ( ! $page instanceof WP_Post ) {
		return;
	}

	$image_meta_keys = array( 'hidamari_hero_mobile_id' );
	foreach ( hidamari_site_core_page_image_slots( $page->post_name ) as $image_key => $label ) {
		$image_meta_keys[] = 'hidamari_page_' . $image_key . '_image_id';
	}

	foreach ( $image_meta_keys as $meta_key ) {
		if ( ! isset( $_POST[ $meta_key ] ) ) {
			continue;
		}

		$attachment_id = absint( wp_unslash( $_POST[ $meta_key ] ) );
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

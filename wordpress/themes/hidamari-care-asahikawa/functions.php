<?php
/**
 * Theme setup.
 *
 * @package Hidamari_Care_Asahikawa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the WordPress features used by the theme.
 *
 * @return void
 */
function hidamari_care_asahikawa_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'hidamari-hero', 1728, 600, true );
	add_image_size( 'hidamari-card', 640, 427, true );
	add_theme_support(
		'html5',
		array(
			'comment-list',
			'comment-form',
			'search-form',
			'gallery',
			'caption',
			'script',
			'style',
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'ヘッダーナビゲーション', 'hidamari-care-asahikawa' ),
			'footer'  => __( 'フッターナビゲーション', 'hidamari-care-asahikawa' ),
		)
	);
}
add_action( 'after_setup_theme', 'hidamari_care_asahikawa_setup' );

/**
 * Return a URI for a theme asset.
 *
 * Escape the returned value for the output context in the calling template.
 *
 * @param string $path Path relative to the assets directory.
 * @return string
 */
function hidamari_care_asahikawa_asset_uri( $path ) {
	return get_theme_file_uri( 'assets/' . ltrim( $path, '/' ) );
}

/**
 * Return an option from the shared facility settings with a fallback.
 *
 * @param string $key     Setting key.
 * @param string $default Fallback value.
 * @return string
 */
function hidamari_care_asahikawa_setting( $key, $default = '' ) {
	$settings = get_option( 'hidamari_settings', array() );

	if ( ! is_array( $settings ) || ! isset( $settings[ $key ] ) || '' === $settings[ $key ] ) {
		return $default;
	}

	return (string) $settings[ $key ];
}

/**
 * Return a TOP content image attachment ID stored on the front page.
 *
 * @param string $key Image key.
 * @return int
 */
function hidamari_care_asahikawa_home_image_id( $key ) {
	$page_id = (int) get_option( 'page_on_front' );

	if ( $page_id <= 0 ) {
		$page_id = get_queried_object_id();
	}

	return (int) get_post_meta( $page_id, 'hidamari_home_' . sanitize_key( $key ) . '_image_id', true );
}

/**
 * Render a responsive TOP content image from the media library.
 *
 * @param string               $key        Image key.
 * @param string|array<string> $size       Registered image size or dimensions.
 * @param array<string, mixed> $attributes Image attributes.
 * @return string
 */
function hidamari_care_asahikawa_home_image( $key, $size = 'full', $attributes = array() ) {
	$image_id = hidamari_care_asahikawa_home_image_id( $key );

	if ( $image_id <= 0 ) {
		return '';
	}

	return wp_get_attachment_image( $image_id, $size, false, $attributes );
}

/**
 * Return a content image attachment ID stored on a fixed page.
 *
 * @param int    $page_id Page ID.
 * @param string $key     Image key.
 * @return int
 */
function hidamari_care_asahikawa_page_image_id( $page_id, $key ) {
	return (int) get_post_meta( $page_id, 'hidamari_page_' . sanitize_key( $key ) . '_image_id', true );
}

/**
 * Render a responsive content image stored on a fixed page.
 *
 * @param int                  $page_id    Page ID.
 * @param string               $key        Image key.
 * @param string|array<string> $size       Registered image size or dimensions.
 * @param array<string, mixed> $attributes Image attributes.
 * @return string
 */
function hidamari_care_asahikawa_page_image( $page_id, $key, $size = 'full', $attributes = array() ) {
	$image_id = hidamari_care_asahikawa_page_image_id( $page_id, $key );

	if ( $image_id <= 0 ) {
		return '';
	}

	return wp_get_attachment_image( $image_id, $size, false, $attributes );
}

/**
 * Return a page URL, including a predictable fallback before content migration.
 *
 * @param string $path Page path.
 * @return string
 */
function hidamari_care_asahikawa_page_url( $path ) {
	$page = get_page_by_path( $path );

	if ( $page instanceof WP_Post ) {
		return get_permalink( $page );
	}

	return home_url( user_trailingslashit( $path ) );
}

/**
 * Return the posts page URL.
 *
 * @return string
 */
function hidamari_care_asahikawa_posts_url() {
	$posts_page_id = (int) get_option( 'page_for_posts' );

	if ( $posts_page_id > 0 ) {
		return get_permalink( $posts_page_id );
	}

	return hidamari_care_asahikawa_page_url( 'news' );
}

/**
 * Return the static-site page key for the current WordPress request.
 *
 * @return string
 */
function hidamari_care_asahikawa_page_type() {
	if ( is_front_page() ) {
		return 'home';
	}

	if ( is_home() || is_archive() || is_search() ) {
		return 'archive';
	}

	if ( is_singular( 'post' ) ) {
		return 'post';
	}

	if ( is_page() ) {
		$page_types = array(
			'about-us'       => 'aboutus',
			'facilities'     => 'facilities',
			'price'          => 'price',
			'faq'            => 'faq',
			'contact'        => 'contact',
			'privacy-policy' => 'privacy',
		);
		$page_slug  = get_post_field( 'post_name', get_queried_object_id() );

		return isset( $page_types[ $page_slug ] ) ? $page_types[ $page_slug ] : 'page';
	}

	return is_404() ? 'not-found' : 'default';
}

/**
 * Return the navigation key that represents the current request.
 *
 * @return string
 */
function hidamari_care_asahikawa_current_navigation_key() {
	$page_type = hidamari_care_asahikawa_page_type();

	if ( 'post' === $page_type ) {
		return 'archive';
	}

	if ( 'privacy' === $page_type ) {
		return 'contact';
	}

	return $page_type;
}

/**
 * Return the default navigation links used before menus are assigned.
 *
 * @param string $location Registered menu location.
 * @return array<int, array<string, string>>
 */
function hidamari_care_asahikawa_default_menu_items( $location ) {
	$primary_items = array(
		array(
			'key'   => 'aboutus',
			'label' => __( '施設紹介', 'hidamari-care-asahikawa' ),
			'url'   => hidamari_care_asahikawa_page_url( 'about-us' ),
		),
		array(
			'key'   => 'flow',
			'label' => __( 'ご利用の流れ', 'hidamari-care-asahikawa' ),
			'url'   => hidamari_care_asahikawa_page_url( 'about-us' ) . '#flow__about',
		),
		array(
			'key'   => 'price',
			'label' => __( '料金表', 'hidamari-care-asahikawa' ),
			'url'   => hidamari_care_asahikawa_page_url( 'price' ),
		),
		array(
			'key'   => 'facilities',
			'label' => __( '全施設一覧', 'hidamari-care-asahikawa' ),
			'url'   => hidamari_care_asahikawa_page_url( 'facilities' ),
		),
		array(
			'key'   => 'faq',
			'label' => __( 'よくあるご質問', 'hidamari-care-asahikawa' ),
			'url'   => hidamari_care_asahikawa_page_url( 'faq' ),
		),
		array(
			'key'   => 'contact',
			'label' => __( 'お問い合わせ', 'hidamari-care-asahikawa' ),
			'url'   => hidamari_care_asahikawa_page_url( 'contact' ),
		),
	);

	if ( 'primary' === $location ) {
		return $primary_items;
	}

	return array(
		$primary_items[0],
		array(
			'key'   => 'home',
			'label' => __( 'ご利用の流れ', 'hidamari-care-asahikawa' ),
			'url'   => home_url( '/#flow' ),
		),
		$primary_items[2],
		$primary_items[3],
		array(
			'key'   => 'archive',
			'label' => __( 'お知らせ一覧', 'hidamari-care-asahikawa' ),
			'url'   => hidamari_care_asahikawa_posts_url(),
		),
		$primary_items[4],
		$primary_items[5],
		array(
			'key'   => 'privacy',
			'label' => __( 'プライバシーポリシー', 'hidamari-care-asahikawa' ),
			'url'   => hidamari_care_asahikawa_page_url( 'privacy-policy' ),
		),
	);
}

/**
 * Render the default list when a menu location has not been assigned.
 *
 * @param array<string, mixed> $args Menu arguments.
 * @return void
 */
function hidamari_care_asahikawa_menu_fallback( $args ) {
	$location    = isset( $args['theme_location'] ) ? $args['theme_location'] : 'primary';
	$menu_class  = isset( $args['menu_class'] ) ? $args['menu_class'] : 'menu';
	$current_key = hidamari_care_asahikawa_current_navigation_key();
	$page_type   = hidamari_care_asahikawa_page_type();

	echo '<ul class="' . esc_attr( $menu_class ) . '">';
	foreach ( hidamari_care_asahikawa_default_menu_items( $location ) as $item ) {
		$is_current = $current_key === $item['key'] || $page_type === $item['key'];
		$classes    = $is_current ? 'menu-item current-menu-item' : 'menu-item';
		$current    = $is_current ? ' aria-current="page"' : '';

		echo '<li class="' . esc_attr( $classes ) . '">';
		echo '<a href="' . esc_url( $item['url'] ) . '" data-nav="' . esc_attr( $item['key'] ) . '"' . $current . '>';
		echo esc_html( $item['label'] );
		echo '</a></li>';
	}
	echo '</ul>';
}

/**
 * Add the related current state to Contact on the privacy policy page.
 *
 * @param string[] $classes Menu item classes.
 * @param WP_Post  $item    Menu item object.
 * @param stdClass $args    Menu arguments.
 * @return string[]
 */
function hidamari_care_asahikawa_nav_menu_classes( $classes, $item, $args ) {
	if ( ! isset( $args->theme_location ) || ! in_array( $args->theme_location, array( 'primary', 'footer' ), true ) ) {
		return $classes;
	}

	if ( 'privacy' !== hidamari_care_asahikawa_page_type() ) {
		return $classes;
	}

	$contact_path = untrailingslashit( (string) wp_parse_url( hidamari_care_asahikawa_page_url( 'contact' ), PHP_URL_PATH ) );
	$item_path    = untrailingslashit( (string) wp_parse_url( $item->url, PHP_URL_PATH ) );

	if ( $contact_path === $item_path ) {
		$classes[] = 'is-related-current';
	}

	return array_unique( $classes );
}
add_filter( 'nav_menu_css_class', 'hidamari_care_asahikawa_nav_menu_classes', 10, 3 );

/**
 * Enqueue the shared fonts, stylesheet, and behavior script.
 *
 * @return void
 */
function hidamari_care_asahikawa_enqueue_assets() {
	$theme_version   = wp_get_theme()->get( 'Version' );
	$stylesheet_path = get_theme_file_path( 'assets/css/style.css' );
	$script_path     = get_theme_file_path( 'assets/js/main.js' );
	$style_version   = file_exists( $stylesheet_path ) ? (string) filemtime( $stylesheet_path ) : $theme_version;
	$script_version  = file_exists( $script_path ) ? (string) filemtime( $script_path ) : $theme_version;

	wp_enqueue_style(
		'hidamari-care-asahikawa-fonts',
		'https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700;900&family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'hidamari-care-asahikawa-style',
		hidamari_care_asahikawa_asset_uri( 'css/style.css' ),
		array( 'hidamari-care-asahikawa-fonts' ),
		$style_version
	);

	wp_enqueue_script(
		'hidamari-care-asahikawa-script',
		hidamari_care_asahikawa_asset_uri( 'js/main.js' ),
		array(),
		$script_version,
		true
	);
	wp_script_add_data( 'hidamari-care-asahikawa-script', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'hidamari_care_asahikawa_enqueue_assets' );

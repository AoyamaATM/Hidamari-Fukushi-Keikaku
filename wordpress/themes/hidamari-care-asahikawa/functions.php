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

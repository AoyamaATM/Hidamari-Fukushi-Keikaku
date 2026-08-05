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

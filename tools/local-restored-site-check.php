<?php
/**
 * Read-only state check for a disposable WPvivid restore environment.
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	throw new RuntimeException( 'Run this file with WP-CLI against the disposable restore site.' );
}

if ( 0 !== strpos( DB_NAME, 'hidamari_restore_' ) ) {
	throw new RuntimeException( 'Refusing to inspect a database outside the disposable restore namespace.' );
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

$count_published = static function ( $post_type ) {
	$counts = wp_count_posts( $post_type );
	return isset( $counts->publish ) ? (int) $counts->publish : 0;
};

$form_id     = (int) get_option( 'hidamari_forminator_form_id', 0 );
$form        = class_exists( 'Forminator_API' ) && $form_id > 0 ? Forminator_API::get_form( $form_id ) : null;
$form_valid  = is_object( $form ) && ! is_wp_error( $form );
$settings    = $form_valid && is_array( $form->settings ) ? $form->settings : array();
$notifications = $form_valid && is_array( $form->notifications ) ? $form->notifications : array();
$theme       = wp_get_theme( 'hidamari-care-asahikawa' );
$site_plugin = get_plugin_data( WP_PLUGIN_DIR . '/hidamari-site-core/hidamari-site-core.php', false, false );
$page_slugs  = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);
$page_slugs  = array_map( static fn( $post_id ) => get_post_field( 'post_name', $post_id ), $page_slugs );
sort( $page_slugs );
$expected_page_slugs = array( 'about-us', 'contact', 'facilities', 'faq', 'home', 'news', 'price', 'privacy-policy' );

global $wpdb;
$entry_count = $form_id > 0 && $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}frmt_form_entry'" )
	? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}frmt_form_entry WHERE form_id = %d", $form_id ) )
	: -1;

$checks = array(
	'home_url_replaced'          => 0 === strpos( home_url( '/' ), 'http://127.0.0.1:' ),
	'published_pages_8'          => 8 === $count_published( 'page' ),
	'published_posts_10'         => 10 === $count_published( 'post' ),
	'published_faq_13'           => 13 === $count_published( 'hidamari_faq' ),
	'published_flow_8'           => 8 === $count_published( 'hidamari_flow' ),
	'published_price_30'         => 30 === $count_published( 'hidamari_price' ),
	'page_slugs_match'           => $expected_page_slugs === $page_slugs,
	'theme_active'               => 'hidamari-care-asahikawa' === get_stylesheet(),
	'theme_version_0_11_2'       => '0.11.2' === $theme->get( 'Version' ),
	'site_plugin_active'         => is_plugin_active( 'hidamari-site-core/hidamari-site-core.php' ),
	'site_plugin_version_0_6_0'  => isset( $site_plugin['Version'] ) && '0.6.0' === $site_plugin['Version'],
	'forminator_active'          => is_plugin_active( 'forminator/forminator.php' ),
	'forminator_version_1_57_0'  => defined( 'FORMINATOR_VERSION' ) && '1.57.0' === FORMINATOR_VERSION,
	'seo_plugin_active'          => is_plugin_active( 'seo-simple-pack/seo-simple-pack.php' ),
	'form_restored'              => $form_valid,
	'form_notifications_0'       => $form_valid && 0 === count( $notifications ),
	'form_storage_disabled'      => $form_valid && empty( $settings['store_submissions'] ),
	'form_honeypot_enabled'      => $form_valid && ! empty( $settings['honeypot'] ),
	'form_demo_completion'       => $form_valid && isset( $settings['thankyou-message'] ) && false !== strpos( $settings['thankyou-message'], 'メール送信・保存されていません' ),
	'form_entries_0'             => 0 === $entry_count,
	'site_icon_restored'         => (int) get_option( 'site_icon', 0 ) > 0,
	'administrator_exists'       => count( get_users( array( 'role' => 'administrator', 'fields' => 'ids' ) ) ) > 0,
);

$failed = array_keys( array_filter( $checks, static fn( $passed ) => ! $passed ) );
$result = array(
	'passed'       => count( $checks ) - count( $failed ),
	'total'        => count( $checks ),
	'failed'       => $failed,
	'home_url'     => home_url( '/' ),
	'form_id'      => $form_id,
	'entry_count'  => $entry_count,
	'page_slugs'   => $page_slugs,
);

WP_CLI::line( wp_json_encode( $result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );

if ( $failed ) {
	throw new RuntimeException( 'Restored-site checks failed: ' . implode( ', ', $failed ) );
}

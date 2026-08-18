<?php
/**
 * Build the idempotent Local data required by the facilities page.
 *
 * Run with Local's WP-CLI:
 * wp eval-file C:/Users/user/Documents/Codex_Akutsu/tools/local-facilities-fixtures.php
 *
 * @package Hidamari_Care_Asahikawa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$local_host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
if ( 'hidamari-care-asahikawa.local' !== $local_host ) {
	throw new RuntimeException( 'This migration script may only run on hidamari-care-asahikawa.local.' );
}

if ( ! function_exists( 'hidamari_site_core_facilities_image_slots' ) ) {
	throw new RuntimeException( 'Update and activate hidamari-site-core before running the Facilities migration.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Return statuses that keep fixture lookup idempotent after manual changes.
 *
 * @param string $post_type Post type.
 * @return array<string>
 */
function hidamari_facilities_post_statuses( $post_type ) {
	return 'attachment' === $post_type
		? array( 'inherit', 'private', 'trash' )
		: array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'trash' );
}

/**
 * Find one migrated record by its stable key.
 *
 * @param string $post_type     Post type.
 * @param string $migration_key Migration key.
 * @return int
 */
function hidamari_facilities_find_post( $post_type, $migration_key ) {
	$post_ids = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => hidamari_facilities_post_statuses( $post_type ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_hidamari_migration_key',
			'meta_value'     => $migration_key,
		)
	);

	return ! empty( $post_ids ) ? (int) $post_ids[0] : 0;
}

/**
 * Count migrated records matching the expected stable keys.
 *
 * @param string        $post_type      Post type.
 * @param array<string> $migration_keys Migration keys.
 * @return int
 */
function hidamari_facilities_count_posts( $post_type, $migration_keys ) {
	return count(
		get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => hidamari_facilities_post_statuses( $post_type ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => '_hidamari_migration_key',
						'value'   => $migration_keys,
						'compare' => 'IN',
					),
				),
			)
		)
	);
}

/**
 * Import one media-library image once.
 *
 * @param string $key       Stable image key.
 * @param string $file      Source file.
 * @param int    $parent_id Parent page ID.
 * @param string $title     Media title.
 * @param string $alt       Alternative text.
 * @return int
 */
function hidamari_facilities_import_image( $key, $file, $parent_id, $title, $alt ) {
	$migration_key = 'facilities-image-' . $key;
	$attachment_id = hidamari_facilities_find_post( 'attachment', $migration_key );

	if ( $attachment_id > 0 ) {
		wp_update_post(
			array(
				'ID'          => $attachment_id,
				'post_parent' => $parent_id,
				'post_status' => 'inherit',
				'post_title'  => $title,
			)
		);
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
		return $attachment_id;
	}

	if ( ! is_readable( $file ) ) {
		throw new RuntimeException( 'Image source is not readable: ' . $file );
	}

	$contents = file_get_contents( $file );
	if ( false === $contents ) {
		throw new RuntimeException( 'Unable to read image source: ' . $file );
	}

	$upload = wp_upload_bits( wp_basename( $file ), null, $contents );
	if ( ! empty( $upload['error'] ) ) {
		throw new RuntimeException( $upload['error'] );
	}

	$file_type     = wp_check_filetype( $upload['file'] );
	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $file_type['type'],
			'post_title'     => $title,
			'post_status'    => 'inherit',
			'post_parent'    => $parent_id,
		),
		$upload['file'],
		$parent_id,
		true
	);

	if ( is_wp_error( $attachment_id ) ) {
		throw new RuntimeException( $attachment_id->get_error_message() );
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
	if ( ! empty( $metadata ) ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $attachment_id, '_hidamari_migration_key', $migration_key );

	return (int) $attachment_id;
}

$facilities_page = get_page_by_path( 'facilities', OBJECT, 'page' );
if ( ! $facilities_page instanceof WP_Post ) {
	throw new RuntimeException( 'Run local-top-fixtures.php before the Facilities migration.' );
}

$facilities_page_id = wp_update_post(
	array(
		'ID'          => $facilities_page->ID,
		'post_status' => 'publish',
		'post_title'  => '全施設一覧',
	),
	true
);
if ( is_wp_error( $facilities_page_id ) ) {
	throw new RuntimeException( $facilities_page_id->get_error_message() );
}
$facilities_page_id = (int) $facilities_page_id;

$image_root = dirname( __DIR__ ) . '/docs/img/';
$new_images = array(
	'hero-desktop' => array( 'mv-Facilities_PC.webp', '全施設一覧 PCヒーロー', '' ),
	'hero-mobile'  => array( 'mv-Facilities_SP.webp', '全施設一覧 SPヒーロー', '' ),
	'staff'        => array( 'stuffs.webp', 'スタッフ紹介', 'ひだまりケア旭川のスタッフ' ),
);

$new_image_ids = array();
foreach ( $new_images as $key => $image ) {
	$new_image_ids[ $key ] = hidamari_facilities_import_image(
		$key,
		$image_root . $image[0],
		$facilities_page_id,
		$image[1],
		$image[2]
	);
}

$support_image_id = hidamari_facilities_find_post( 'attachment', 'top-image-reason_02' );
if ( $support_image_id <= 0 ) {
	throw new RuntimeException( 'Required TOP image is missing: top-image-reason_02' );
}

set_post_thumbnail( $facilities_page_id, $new_image_ids['hero-desktop'] );
update_post_meta( $facilities_page_id, 'hidamari_hero_mobile_id', $new_image_ids['hero-mobile'] );
update_post_meta( $facilities_page_id, 'hidamari_page_facilities_organization_image_id', $support_image_id );
update_post_meta( $facilities_page_id, 'hidamari_page_facilities_facility_image_id', $support_image_id );
update_post_meta( $facilities_page_id, 'hidamari_page_facilities_staff_image_id', $new_image_ids['staff'] );

$image_migration_keys = array_map(
	static function ( $key ) {
		return 'facilities-image-' . $key;
	},
	array_keys( $new_images )
);

flush_rewrite_rules( false );

echo 'facilities_page=' . $facilities_page_id . PHP_EOL;
echo 'new_images=' . hidamari_facilities_count_posts( 'attachment', $image_migration_keys ) . PHP_EOL;
echo 'reused_images=1' . PHP_EOL;
echo 'desktop_hero=' . ( has_post_thumbnail( $facilities_page_id ) ? 'yes' : 'no' ) . PHP_EOL;
echo 'mobile_hero=' . ( (int) get_post_meta( $facilities_page_id, 'hidamari_hero_mobile_id', true ) > 0 ? 'yes' : 'no' ) . PHP_EOL;
echo 'content_images=' . ( (int) get_post_meta( $facilities_page_id, 'hidamari_page_facilities_staff_image_id', true ) > 0 ? 'yes' : 'no' ) . PHP_EOL;
echo 'theme_version=' . wp_get_theme()->get( 'Version' ) . PHP_EOL;

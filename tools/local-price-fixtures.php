<?php
/**
 * Build the idempotent Local data required by the price page.
 *
 * Run with Local's WP-CLI:
 * wp eval-file C:/Users/user/Documents/Codex_Akutsu/tools/local-price-fixtures.php
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

if ( ! post_type_exists( 'hidamari_price' ) || ! function_exists( 'hidamari_site_core_price_image_slots' ) ) {
	throw new RuntimeException( 'Update and activate hidamari-site-core before running the Price migration.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Return statuses that keep fixture lookup idempotent after manual changes.
 *
 * @param string $post_type Post type.
 * @return array<string>
 */
function hidamari_price_post_statuses( $post_type ) {
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
function hidamari_price_find_post( $post_type, $migration_key ) {
	$post_ids = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => hidamari_price_post_statuses( $post_type ),
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
function hidamari_price_count_posts( $post_type, $migration_keys ) {
	return count(
		get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => hidamari_price_post_statuses( $post_type ),
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
 * Create or update one migrated record.
 *
 * @param string               $post_type     Post type.
 * @param string               $migration_key Migration key.
 * @param array<string, mixed> $data          Post data.
 * @return int
 */
function hidamari_price_upsert_post( $post_type, $migration_key, $data ) {
	$post_id           = hidamari_price_find_post( $post_type, $migration_key );
	$data['post_type'] = $post_type;

	if ( $post_id > 0 ) {
		$data['ID'] = $post_id;
	}

	$post_id = wp_insert_post( $data, true );
	if ( is_wp_error( $post_id ) ) {
		throw new RuntimeException( $post_id->get_error_message() );
	}

	update_post_meta( $post_id, '_hidamari_migration_key', $migration_key );

	return (int) $post_id;
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
function hidamari_price_import_image( $key, $file, $parent_id, $title, $alt ) {
	$migration_key = 'price-image-' . $key;
	$attachment_id = hidamari_price_find_post( 'attachment', $migration_key );

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

$price_page = get_page_by_path( 'price', OBJECT, 'page' );
if ( ! $price_page instanceof WP_Post ) {
	throw new RuntimeException( 'Run local-top-fixtures.php before the Price migration.' );
}

$price_page_id = wp_update_post(
	array(
		'ID'          => $price_page->ID,
		'post_status' => 'publish',
		'post_title'  => '料金表',
	),
	true
);
if ( is_wp_error( $price_page_id ) ) {
	throw new RuntimeException( $price_page_id->get_error_message() );
}
$price_page_id = (int) $price_page_id;

$image_root = dirname( __DIR__ ) . '/docs/img/';
$new_images = array(
	'hero-desktop' => array( 'mv-PriceList_PC.webp', '料金表 PCヒーロー', '' ),
	'hero-mobile'  => array( 'mv-PriceList_SP.webp', '料金表 SPヒーロー', '' ),
	'day-anchor'   => array( 'AnchorLink_PriceList-DayService.webp', 'デイサービス料金表へのリンク', 'デイサービス料金表' ),
	'visit-anchor' => array( 'AnchorLink_PriceList-HomeCare.webp', '訪問介護料金表へのリンク', '訪問介護料金表' ),
);

$new_image_ids = array();
foreach ( $new_images as $key => $image ) {
	$new_image_ids[ $key ] = hidamari_price_import_image(
		$key,
		$image_root . $image[0],
		$price_page_id,
		$image[1],
		$image[2]
	);
}

set_post_thumbnail( $price_page_id, $new_image_ids['hero-desktop'] );
update_post_meta( $price_page_id, 'hidamari_hero_mobile_id', $new_image_ids['hero-mobile'] );
update_post_meta( $price_page_id, 'hidamari_page_price_day_anchor_image_id', $new_image_ids['day-anchor'] );
update_post_meta( $price_page_id, 'hidamari_page_price_visit_anchor_image_id', $new_image_ids['visit-anchor'] );

$price_data = array(
	'day-basic'        => array(
		array( '要支援2（月額）', '3,428単位', '¥3,428', '¥6,856' ),
		array( '要介護1（日額）', '754単位', '¥754', '¥1,508' ),
		array( '要介護2（日額）', '891単位', '¥891', '¥1,782' ),
		array( '要介護3（日額）', '1,034単位', '¥1,034', '¥2,068' ),
		array( '要介護4（日額）', '1,176単位', '¥1,176', '¥2,352' ),
		array( '要介護5（日額）', '1,320単位', '¥1,320', '¥2,640' ),
	),
	'day-addition'     => array(
		array( '入浴介助加算（1）', '40単位 / 日', '一般入浴介助を行った際に加算' ),
		array( '個別機能訓練加算（1）', '56単位 / 日', '理学療法士等のリハビリを行った場合に加算' ),
		array( '科学的介護推進体制加算', '40単位 / 日', '利用時のデータの提出、フィードバックの活用' ),
		array( '処遇改善加算', '総単位数の5％', '職員の処遇改善のための加算' ),
	),
	'day-outside'      => array(
		array( '昼食代', '¥650 / 日', 'ご飯、おかず、椀物' ),
		array( 'おやつ代', '¥100 / 日', 'お茶会のお飲み物・お菓子、特別メニュー積立金' ),
		array( '延長料金', '¥500 / 30分', '規定時間を超えた場合に発生' ),
		array( '教材費', '実費', '工作やイベント等にかかる費用' ),
	),
	'visit-physical'   => array(
		array( '20分未満', '167単位', '¥167', '¥334' ),
		array( '30分未満', '250単位', '¥250', '¥500' ),
		array( '60分未満', '396単位', '¥396', '¥792' ),
		array( '90分未満', '579単位', '¥579', '¥1,158' ),
		array( '90分以降 30分毎', '+150単位', '¥150', '¥300' ),
	),
	'visit-housework'  => array(
		array( '20分以上45分未満', '183単位', '¥183', '¥366' ),
		array( '45分以上', '225単位', '¥225', '¥450' ),
	),
	'visit-prevention' => array(
		array( '要支援1', '週1回程度', '¥1,176 / 月', '¥2,352 / 月' ),
		array( '要支援2', '週2回程度', '¥2,349 / 月', '¥4,698 / 月' ),
	),
	'visit-addition'   => array(
		array( '初回加算', '200単位', '新規にサービスを開始した月に発生' ),
		array( '計画外訪問加算', '100単位', 'ケアプランにない訪問時に発生' ),
		array( '夜間・早朝加算', '基本料金の25%', '18時〜22時／6時〜9時 での訪問' ),
		array( '深夜加算', '基本料金の50%', '22時〜翌6時 での訪問' ),
		array( '処遇改善加算', '総単位数の5％', '職員の処遇改善のため' ),
	),
	'visit-outside'    => array(
		array( '自費生活支援', '¥3,000 / 60分', '30分単位での延長可（＋¥1,500 / 30分）' ),
		array( '外出の付き添い', '¥3,500 / 60分', '交通費は別途実費' ),
	),
);

$price_row_keys = array();
foreach ( $price_data as $group_key => $rows ) {
	foreach ( $rows as $row_index => $cells ) {
		$migration_key   = sprintf( 'price-row-%s-%02d', $group_key, $row_index + 1 );
		$price_row_keys[] = $migration_key;
		$cells            = array_pad( $cells, 4, '' );
		$post_id          = hidamari_price_upsert_post(
			'hidamari_price',
			$migration_key,
			array(
				'post_status' => 'publish',
				'post_title'  => $cells[0],
				'menu_order'  => $row_index + 1,
			)
		);

		update_post_meta( $post_id, 'hidamari_price_group', $group_key );
		for ( $cell_number = 1; $cell_number <= 4; $cell_number++ ) {
			update_post_meta( $post_id, 'hidamari_price_cell_' . $cell_number, $cells[ $cell_number - 1 ] );
		}
		update_post_meta( $post_id, 'hidamari_price_row_type', 'normal' );
	}
}

$image_migration_keys = array_map(
	static function ( $key ) {
		return 'price-image-' . $key;
	},
	array_keys( $new_images )
);

flush_rewrite_rules( false );

echo 'price_page=' . $price_page_id . PHP_EOL;
echo 'price_groups=' . count( $price_data ) . PHP_EOL;
echo 'price_rows=' . hidamari_price_count_posts( 'hidamari_price', $price_row_keys ) . PHP_EOL;
echo 'new_images=' . hidamari_price_count_posts( 'attachment', $image_migration_keys ) . PHP_EOL;
echo 'desktop_hero=' . ( has_post_thumbnail( $price_page_id ) ? 'yes' : 'no' ) . PHP_EOL;
echo 'mobile_hero=' . ( (int) get_post_meta( $price_page_id, 'hidamari_hero_mobile_id', true ) > 0 ? 'yes' : 'no' ) . PHP_EOL;
echo 'anchor_images=' . ( (int) get_post_meta( $price_page_id, 'hidamari_page_price_day_anchor_image_id', true ) > 0 && (int) get_post_meta( $price_page_id, 'hidamari_page_price_visit_anchor_image_id', true ) > 0 ? 'yes' : 'no' ) . PHP_EOL;
echo 'theme_version=' . wp_get_theme()->get( 'Version' ) . PHP_EOL;

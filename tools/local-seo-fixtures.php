<?php
/**
 * Configure SEO SIMPLE PACK and the site icon for the Local migration site.
 *
 * Run with Local's WP-CLI:
 * wp eval-file C:/Users/lihui/Documents/Codex_Akutsu/tools/local-seo-fixtures.php
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

if ( ! class_exists( 'SSP_Data' ) || ! defined( 'SSP_VERSION' ) ) {
	throw new RuntimeException( 'Activate SEO SIMPLE PACK before running the SEO migration.' );
}

if ( ! in_array( 'ja', get_available_languages(), true ) ) {
	throw new RuntimeException( 'Install the WordPress Japanese language pack before running the SEO migration.' );
}
update_option( 'WPLANG', 'ja' );

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Find one migrated attachment by its stable migration key.
 *
 * @param string $migration_key Migration key.
 * @return int
 */
function hidamari_seo_local_find_attachment( $migration_key ) {
	$attachment_ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => array( 'inherit', 'private', 'trash' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_hidamari_migration_key',
			'meta_value'     => $migration_key,
		)
	);

	return ! empty( $attachment_ids ) ? (int) $attachment_ids[0] : 0;
}

/**
 * Import the square site icon once and return its attachment ID.
 *
 * @param string $file Source file path.
 * @return int
 */
function hidamari_seo_local_import_site_icon( $file ) {
	$attachment_id = hidamari_seo_local_find_attachment( 'seo-site-icon' );
	if ( $attachment_id > 0 ) {
		wp_update_post(
			array(
				'ID'         => $attachment_id,
				'post_title' => 'ひだまりケア旭川 サイトアイコン',
			)
		);
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'ひだまりケア旭川' );
		return $attachment_id;
	}

	if ( ! is_readable( $file ) ) {
		throw new RuntimeException( 'Site icon source is not readable: ' . $file );
	}

	$image_size = getimagesize( $file );
	if ( false === $image_size || 512 !== (int) $image_size[0] || 512 !== (int) $image_size[1] ) {
		throw new RuntimeException( 'Site icon source must be a 512 x 512 image.' );
	}

	$contents = file_get_contents( $file );
	if ( false === $contents ) {
		throw new RuntimeException( 'Unable to read site icon source.' );
	}

	$upload = wp_upload_bits( 'hidamari-site-icon.png', null, $contents );
	if ( ! empty( $upload['error'] ) ) {
		throw new RuntimeException( $upload['error'] );
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => 'ひだまりケア旭川 サイトアイコン',
			'post_name'      => 'hidamari-site-icon',
			'post_status'    => 'inherit',
		),
		$upload['file'],
		0,
		true
	);
	if ( is_wp_error( $attachment_id ) ) {
		throw new RuntimeException( $attachment_id->get_error_message() );
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
	if ( ! empty( $metadata ) ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'ひだまりケア旭川' );
	update_post_meta( $attachment_id, '_hidamari_migration_key', 'seo-site-icon' );

	return (int) $attachment_id;
}

$page_metadata = array(
	'home'           => array(
		'title'       => '旭川市のデイサービス・訪問介護 | ひだまりケア旭川',
		'description' => '旭川市周辺でデイサービス・訪問介護・居宅介護支援を提供する「ひだまりケア旭川」。見学・介護相談を受け付けています。',
	),
	'news'           => array(
		'title'       => 'お知らせ一覧 | ひだまりケア旭川',
		'description' => 'ひだまりケア旭川からの営業日、施設見学、介護相談、季節の取り組みなどに関するお知らせを掲載しています。',
	),
	'about-us'       => array(
		'title'       => '施設紹介 | ひだまりケア旭川',
		'description' => '社会福祉法人ひだまり福祉計画が運営する「ひだまりケア旭川」の概要、提供サービス、デイサービスの一日、ご利用開始までの流れをご紹介します。',
	),
	'facilities'     => array(
		'title'       => '全施設一覧 | ひだまりケア旭川',
		'description' => '社会福祉法人ひだまり福祉計画の理念と施設情報、ひだまりケア旭川の所在地・提供サービス・スタッフ情報をご案内します。',
	),
	'price'          => array(
		'title'       => '料金表 | ひだまりケア旭川',
		'description' => 'ひだまりケア旭川のデイサービスと訪問介護の料金目安をご案内します。介護度や自己負担割合ごとの費用をご確認いただけます。',
	),
	'faq'            => array(
		'title'       => 'よくあるご質問 | ひだまりケア旭川',
		'description' => 'ひだまりケア旭川の利用料金、訪問介護、介護保険の申請、見学・相談、施設での生活に関するよくあるご質問にお答えします。',
	),
	'contact'        => array(
		'title'       => 'お問い合わせ | ひだまりケア旭川',
		'description' => 'ひだまりケア旭川への見学予約・介護相談はこちら。お電話またはお問い合わせフォームからご連絡いただけます。',
	),
	'privacy-policy' => array(
		'title'       => 'プライバシーポリシー | ひだまりケア旭川',
		'description' => '社会福祉法人ひだまり福祉計画における個人情報の取得、利用目的、第三者提供、安全管理、開示請求などの方針をご案内します。',
	),
);

$og_attachment_id = hidamari_seo_local_find_attachment( 'top-image-hero' );
$og_image_url      = $og_attachment_id > 0 ? wp_get_attachment_url( $og_attachment_id ) : '';
if ( '' === $og_image_url ) {
	throw new RuntimeException( 'Run local-top-fixtures.php before migrating SEO settings.' );
}

$settings = get_option( SSP_Data::DB_NAME['settings'], array() );
$settings = is_array( $settings ) ? $settings : array();
$settings = array_merge(
	$settings,
	array(
		'home_title'         => $page_metadata['home']['title'],
		'home_desc'          => $page_metadata['home']['description'],
		'separator'          => 'line',
		'post_noindex'       => false,
		'post_title'         => '%_page_title_% %_sep_% %_site_title_%',
		'post_desc'          => '%_page_contents_%',
		'page_noindex'       => false,
		'page_title'         => '%_page_title_% %_sep_% %_site_title_%',
		'page_desc'          => '%_page_contents_%',
		'cat_noindex'        => false,
		'cat_title'          => '%_term_name_%の記事一覧 %_sep_% %_site_title_%',
		'cat_desc'           => '%_term_description_%',
		'date_title'         => '%_date_%のお知らせ %_sep_% %_site_title_%',
		'date_desc'          => '%_date_%のひだまりケア旭川のお知らせ一覧です。営業日、施設見学、介護相談、季節の取り組みなどを掲載しています。',
		'404_title'          => 'ページが見つかりません %_sep_% %_site_title_%',
		'search_title'       => '「%_search_phrase_%」の検索結果 %_sep_% %_site_title_%',
		'author_disable'     => true,
		'attachment_disable' => true,
	)
);
update_option( SSP_Data::DB_NAME['settings'], $settings );

$ogp = get_option( SSP_Data::DB_NAME['ogp'], array() );
$ogp = is_array( $ogp ) ? $ogp : array();
$ogp = array_merge(
	$ogp,
	array(
		'og_image'  => $og_image_url,
		'fb_active' => true,
		'tw_active' => true,
		'tw_card'   => 'summary_large_image',
	)
);
update_option( SSP_Data::DB_NAME['ogp'], $ogp );

$page_ids = array();
foreach ( $page_metadata as $slug => $metadata ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( ! $page instanceof WP_Post ) {
		throw new RuntimeException( 'Required page is missing: ' . $slug );
	}

	$page_ids[ $slug ] = $page->ID;
	update_post_meta( $page->ID, SSP_MetaBox::POST_META_KEYS['title'], $metadata['title'] );
	update_post_meta( $page->ID, SSP_MetaBox::POST_META_KEYS['description'], $metadata['description'] );
	update_post_meta( $page->ID, SSP_MetaBox::POST_META_KEYS['image'], $og_image_url );
	delete_post_meta( $page->ID, SSP_MetaBox::POST_META_KEYS['canonical'] );
	delete_post_meta( $page->ID, SSP_MetaBox::POST_META_KEYS['robots'] );
}

$posts = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	)
);
foreach ( $posts as $post ) {
	$description = 'top-news-2026-06-15' === get_post_meta( $post->ID, '_hidamari_migration_key', true )
		? 'ひだまりケア旭川の2026年7月の営業予定をご案内します。デイサービス・訪問介護ともに通常どおり営業予定です。'
		: (string) $post->post_excerpt;

	if ( '' !== $description ) {
		update_post_meta( $post->ID, SSP_MetaBox::POST_META_KEYS['description'], $description );
	}
	delete_post_meta( $post->ID, SSP_MetaBox::POST_META_KEYS['canonical'] );
	delete_post_meta( $post->ID, SSP_MetaBox::POST_META_KEYS['robots'] );
}

$archive_description = $page_metadata['news']['description'];
foreach ( array( 'news', 'blog' ) as $category_slug ) {
	$category = get_category_by_slug( $category_slug );
	if ( ! $category instanceof WP_Term ) {
		throw new RuntimeException( 'Required category is missing: ' . $category_slug );
	}
	update_term_meta( $category->term_id, SSP_MetaBox::TERM_META_KEYS['description'], $archive_description );
	delete_term_meta( $category->term_id, SSP_MetaBox::TERM_META_KEYS['canonical'] );
	delete_term_meta( $category->term_id, SSP_MetaBox::TERM_META_KEYS['robots'] );
}

$site_icon_file = dirname( __DIR__ ) . '/wordpress/assets/site-icon.png';
$site_icon_id   = hidamari_seo_local_import_site_icon( $site_icon_file );
update_option( 'site_icon', $site_icon_id );

echo 'plugin_version=' . SSP_VERSION . PHP_EOL;
echo 'pages=' . count( $page_ids ) . PHP_EOL;
echo 'posts=' . count( $posts ) . PHP_EOL;
echo 'og_image=' . $og_image_url . PHP_EOL;
echo 'site_icon_id=' . $site_icon_id . PHP_EOL;

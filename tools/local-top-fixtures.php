<?php
/**
 * Build the idempotent Local data required by the TOP page migration.
 *
 * Run with Local's WP-CLI:
 * wp eval-file C:/Users/lihui/Documents/Codex_Akutsu/tools/local-top-fixtures.php
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

if ( ! post_type_exists( 'hidamari_faq' ) ) {
	throw new RuntimeException( 'Activate hidamari-site-core before running the TOP migration.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Find one migrated post by its stable migration key.
 *
 * @param string $post_type     Post type.
 * @param string $migration_key Migration key.
 * @return int
 */
function hidamari_local_find_post( $post_type, $migration_key ) {
	$post_statuses = 'attachment' === $post_type
		? array( 'inherit', 'private', 'trash' )
		: array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'trash' );
	$post_ids = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => $post_statuses,
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
 * This makes a second run useful as an idempotence check: duplicate records
 * would increase the reported count beyond the number of expected keys.
 *
 * @param string        $post_type     Post type.
 * @param array<string> $migration_keys Migration keys.
 * @return int
 */
function hidamari_local_count_posts( $post_type, $migration_keys ) {
	$post_statuses = 'attachment' === $post_type
		? array( 'inherit', 'private', 'trash' )
		: array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'trash' );

	return count(
		get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => $post_statuses,
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
 * Create or update a page by its stable slug.
 *
 * @param string $title Page title.
 * @param string $slug  Page slug.
 * @return int
 */
function hidamari_local_upsert_page( $title, $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	$data = array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'post_title'  => $title,
		'post_name'   => $slug,
	);

	if ( $page instanceof WP_Post ) {
		$data['ID'] = $page->ID;
	}

	$page_id = wp_insert_post( $data, true );
	if ( is_wp_error( $page_id ) ) {
		throw new RuntimeException( $page_id->get_error_message() );
	}

	return (int) $page_id;
}

/**
 * Create or update a migrated post.
 *
 * @param string               $post_type     Post type.
 * @param string               $migration_key Migration key.
 * @param array<string, mixed> $data          Post data.
 * @return int
 */
function hidamari_local_upsert_post( $post_type, $migration_key, $data ) {
	$post_id           = hidamari_local_find_post( $post_type, $migration_key );
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
 * Import one media-library image once and return its attachment ID.
 *
 * @param string $key       Stable image key.
 * @param string $file      Source file path.
 * @param int    $parent_id Parent page ID.
 * @param string $title     Attachment title.
 * @param string $alt       Alternative text.
 * @return int
 */
function hidamari_local_import_image( $key, $file, $parent_id, $title, $alt ) {
	$attachment_id = hidamari_local_find_post( 'attachment', 'top-image-' . $key );
	if ( $attachment_id > 0 ) {
		wp_update_post(
			array(
				'ID'          => $attachment_id,
				'post_parent' => $parent_id,
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
			'post_name'      => 'hidamari-top-' . sanitize_title( $key ),
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
	update_post_meta( $attachment_id, '_hidamari_migration_key', 'top-image-' . $key );

	return (int) $attachment_id;
}

/**
 * Ensure a category or taxonomy term exists and return its term ID.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $name     Term name.
 * @param string $slug     Term slug.
 * @return int
 */
function hidamari_local_ensure_term( $taxonomy, $name, $slug ) {
	$existing = term_exists( $slug, $taxonomy );
	if ( is_array( $existing ) ) {
		return (int) $existing['term_id'];
	}

	if ( is_int( $existing ) ) {
		return $existing;
	}

	$created = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
	if ( is_wp_error( $created ) ) {
		throw new RuntimeException( $created->get_error_message() );
	}

	return (int) $created['term_id'];
}

$pages = array(
	'home'           => 'ホーム',
	'news'           => 'お知らせ',
	'about-us'       => '施設紹介',
	'facilities'     => '全施設一覧',
	'price'          => '料金表',
	'faq'            => 'よくあるご質問',
	'contact'        => 'お問い合わせ',
	'privacy-policy' => 'プライバシーポリシー',
);

$page_ids = array();
foreach ( $pages as $slug => $title ) {
	$page_ids[ $slug ] = hidamari_local_upsert_page( $title, $slug );
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $page_ids['home'] );
update_option( 'page_for_posts', $page_ids['news'] );
update_option( 'wp_page_for_privacy_policy', $page_ids['privacy-policy'] );
update_option( 'blogname', 'ひだまりケア旭川' );
update_option( 'blogdescription', '旭川市のデイサービス・訪問介護・介護相談' );

update_post_meta(
	$page_ids['home'],
	'hidamari_page_lead',
	"旭川市周辺で、通所（デイ）と訪問介護を提供しています。\n見学・ご相談はお気軽にどうぞ。"
);

$settings = get_option( 'hidamari_settings', array() );
$settings = is_array( $settings ) ? $settings : array();
$settings = wp_parse_args(
	$settings,
	array(
		'organization_name' => '社会福祉法人 ひだまり福祉計画',
		'facility_name'     => 'ひだまりケア旭川',
		'phone_display'     => '0166-xx-xxxx',
		'phone_link'        => '',
		'business_hours'    => '平日 9:00 ～ 17:00',
	)
);
update_option( 'hidamari_settings', $settings );

$image_root = dirname( __DIR__ ) . '/docs/img/';
$images     = array(
	'hero'       => array( 'MainVisual_pc.webp', 'TOPメインビジュアル', '車いすの利用者に寄り添う介護スタッフ' ),
	'reason_01'  => array( 'reason_01.webp', '選ばれる理由1', '利用者と家族に説明する介護スタッフ' ),
	'reason_02'  => array( 'reason_02.webp', '選ばれる理由2', '車いすの利用者に寄り添う介護スタッフ' ),
	'reason_03'  => array( 'reason_03.webp', '選ばれる理由3', '家族の相談を受ける介護スタッフ' ),
	'service_01' => array( 'service_01.webp', 'デイサービス', 'デイサービスで体操をする利用者' ),
	'service_02' => array( 'service_02.webp', '訪問介護', '訪問介護で相談する利用者とスタッフ' ),
	'service_03' => array( 'service_03.webp', '介護相談', '介護相談で話を聞くスタッフ' ),
);

$image_ids = array();
foreach ( $images as $key => $image ) {
	$image_ids[ $key ] = hidamari_local_import_image(
		$key,
		$image_root . $image[0],
		$page_ids['home'],
		$image[1],
		$image[2]
	);

	if ( 'hero' !== $key ) {
		update_post_meta( $page_ids['home'], 'hidamari_home_' . $key . '_image_id', $image_ids[ $key ] );
	}
}
set_post_thumbnail( $page_ids['home'], $image_ids['hero'] );

$news_category_id = hidamari_local_ensure_term( 'category', 'ニュース', 'news' );
$blog_category_id = hidamari_local_ensure_term( 'category', 'ブログ', 'blog' );
$news_posts        = array(
	array( 'top-news-2026-06-15', '7月の営業日についてのお知らせ', '2026-06-15 09:00:00', $news_category_id ),
	array( 'top-news-2026-06-12', '暑い季節の体調管理について', '2026-06-12 09:00:00', $news_category_id ),
	array( 'top-blog-2026-06-10', '初夏のレクリエーションを行いました', '2026-06-10 09:00:00', $blog_category_id ),
);

foreach ( $news_posts as $news_post ) {
	$post_id = hidamari_local_upsert_post(
		'post',
		$news_post[0],
		array(
			'post_status'  => 'publish',
			'post_title'   => $news_post[1],
			'post_name'    => sanitize_title( $news_post[1] ),
			'post_content' => '',
			'post_date'    => $news_post[2],
		)
	);
	wp_set_post_categories( $post_id, array( $news_post[3] ), false );
}

$hello_world = get_page_by_path( 'hello-world', OBJECT, 'post' );
if ( $hello_world instanceof WP_Post && 'Hello world!' === $hello_world->post_title ) {
	wp_trash_post( $hello_world->ID );
}

$faq_terms = array(
	'consultation' => hidamari_local_ensure_term( 'hidamari_faq_cat', 'ご相談・居宅介護支援', 'consultation' ),
	'daily-life'   => hidamari_local_ensure_term( 'hidamari_faq_cat', '施設での生活', 'daily-life' ),
);

$faq_items = array(
	array(
		'key'      => 'top-faq-consult-1',
		'question' => '見学・相談だけでもいいですか？',
		'answer'   => 'もちろん大歓迎です！<br>見学だけでなく、ご相談だけでも大丈夫ですのでお気軽にお問い合わせください。',
		'category' => 'consultation',
	),
	array(
		'key'      => 'top-faq-life-3',
		'question' => '送迎はありますか？',
		'answer'   => '対応エリア内でしたら、ご自宅まで送迎いたします！<br><br>《対応エリア》<br>○旭川市<br>　・神居古潭、江丹別、東旭川、上雨紛、西神楽地区を除く<br>○東神楽町<br>　・ひじり野地区のみ<br>○鷹栖町<br>　・市街地、北野地区のみ',
		'category' => 'daily-life',
	),
	array(
		'key'      => 'top-faq-consult-3',
		'question' => '介護保険はどうやったら使えるようになりますか？',
		'answer'   => '市役所・地域包括支援センターなどで手続きが必要になります。<br><br>手続きのお手伝い・代行もいたしますのであわせてご相談ください！',
		'category' => 'consultation',
	),
	array(
		'key'      => 'top-faq-life-1',
		'question' => 'どんな方が利用されていますか？',
		'answer'   => '要支援1〜2、要介護1〜4の方を対象としております。<br><br>現在該当していない方でも見学・ご相談は承っておりますので、お気軽にご相談ください！',
		'category' => 'daily-life',
	),
	array(
		'key'      => 'top-faq-life-2',
		'question' => '家族への連絡や報告はありますか？',
		'answer'   => 'もちろんいたします！<br>契約時にご家族様との連絡・ご報告の頻度についてもお話させていただきます。<br>また、頻度の変更についても対応いたしますのでご安心ください。',
		'category' => 'daily-life',
	),
	array(
		'key'      => 'top-faq-life-4',
		'question' => '急な体調変化の場合はどうなりますか？',
		'answer'   => '基本的には常駐の医師と相談のうえ、必要があれば提携している病院を受診させていただきます。<br>なお、そのときの容態により他の病院を受診する場合がございますので、あらかじめご了承ください。<br><br>《提携先病院》<br>○森山病院<br>　旭川市宮前2条1丁目1−6<br>　Tel. 0166-45-2020',
		'category' => 'daily-life',
	),
);

foreach ( $faq_items as $index => $faq_item ) {
	$faq_id = hidamari_local_upsert_post(
		'hidamari_faq',
		$faq_item['key'],
		array(
			'post_status'  => 'publish',
			'post_title'   => $faq_item['question'],
			'post_content' => $faq_item['answer'],
			'menu_order'   => $index + 1,
		)
	);
	update_post_meta( $faq_id, 'hidamari_show_on_front', true );
	wp_set_object_terms( $faq_id, array( $faq_terms[ $faq_item['category'] ] ), 'hidamari_faq_cat', false );
}

flush_rewrite_rules( false );

$image_migration_keys = array_map(
	static function ( $key ) {
		return 'top-image-' . $key;
	},
	array_keys( $images )
);
$post_migration_keys  = array_column( $news_posts, 0 );
$faq_migration_keys   = array_column( $faq_items, 'key' );

echo 'pages=' . count( $page_ids ) . PHP_EOL;
echo 'images=' . hidamari_local_count_posts( 'attachment', $image_migration_keys ) . PHP_EOL;
echo 'posts=' . hidamari_local_count_posts( 'post', $post_migration_keys ) . PHP_EOL;
echo 'faqs=' . hidamari_local_count_posts( 'hidamari_faq', $faq_migration_keys ) . PHP_EOL;
echo 'front_page=' . $page_ids['home'] . PHP_EOL;
echo 'front_image=' . ( has_post_thumbnail( $page_ids['home'] ) ? 'yes' : 'no' ) . PHP_EOL;
echo 'theme_version=' . wp_get_theme()->get( 'Version' ) . PHP_EOL;
echo 'mysql=' . $GLOBALS['wpdb']->db_version() . PHP_EOL;

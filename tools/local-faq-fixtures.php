<?php
/**
 * Build the idempotent Local data required by the FAQ page.
 *
 * Run with Local's WP-CLI:
 * wp eval-file C:/Users/user/Documents/Codex_Akutsu/tools/local-faq-fixtures.php
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

if ( ! post_type_exists( 'hidamari_faq' ) || ! taxonomy_exists( 'hidamari_faq_cat' ) || ! function_exists( 'hidamari_site_core_faq_image_slots' ) ) {
	throw new RuntimeException( 'Update and activate hidamari-site-core before running the FAQ migration.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Return statuses that keep fixture lookup idempotent after manual changes.
 *
 * @param string $post_type Post type.
 * @return array<string>
 */
function hidamari_faq_post_statuses( $post_type ) {
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
function hidamari_faq_find_post( $post_type, $migration_key ) {
	$post_ids = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => hidamari_faq_post_statuses( $post_type ),
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
function hidamari_faq_count_posts( $post_type, $migration_keys ) {
	return count(
		get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => hidamari_faq_post_statuses( $post_type ),
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
function hidamari_faq_upsert_post( $post_type, $migration_key, $data ) {
	$post_id           = hidamari_faq_find_post( $post_type, $migration_key );
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
 * Ensure one FAQ category exists.
 *
 * @param string $name Category name.
 * @param string $slug Category slug.
 * @return int
 */
function hidamari_faq_ensure_term( $name, $slug ) {
	$term = get_term_by( 'slug', $slug, 'hidamari_faq_cat' );
	if ( $term instanceof WP_Term ) {
		return (int) $term->term_id;
	}

	$result = wp_insert_term( $name, 'hidamari_faq_cat', array( 'slug' => $slug ) );
	if ( is_wp_error( $result ) ) {
		throw new RuntimeException( $result->get_error_message() );
	}

	return (int) $result['term_id'];
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
function hidamari_faq_import_image( $key, $file, $parent_id, $title, $alt ) {
	$migration_key = 'faq-image-' . $key;
	$attachment_id = hidamari_faq_find_post( 'attachment', $migration_key );

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

$faq_page = get_page_by_path( 'faq', OBJECT, 'page' );
if ( ! $faq_page instanceof WP_Post ) {
	throw new RuntimeException( 'Run local-top-fixtures.php before the FAQ migration.' );
}

$faq_page_id = wp_update_post(
	array(
		'ID'          => $faq_page->ID,
		'post_status' => 'publish',
		'post_title'  => 'よくあるご質問',
	),
	true
);
if ( is_wp_error( $faq_page_id ) ) {
	throw new RuntimeException( $faq_page_id->get_error_message() );
}
$faq_page_id = (int) $faq_page_id;

$image_root = dirname( __DIR__ ) . '/docs/img/';
$new_images = array(
	'hero-desktop' => array( 'mv-FAQ_PC.webp', 'よくあるご質問 PCヒーロー', '' ),
	'hero-mobile'  => array( 'mv-faq_SP.webp', 'よくあるご質問 SPヒーロー', '' ),
	'payment'      => array( 'anchorLink__faq01-PC.webp', '費用・お支払いへのリンク', '費用・お支払い' ),
	'day-care'     => array( 'anchorLink__faq02-PC.webp', '訪問介護・生活援助へのリンク', '訪問介護・生活援助' ),
	'consultation' => array( 'anchorLink__faq03-PC.webp', 'ご相談・居宅介護支援へのリンク', 'ご相談・居宅介護支援' ),
	'facility'     => array( 'anchorLink__faq04-PC.webp', '施設での生活へのリンク', '施設での生活' ),
);

$new_image_ids = array();
foreach ( $new_images as $key => $image ) {
	$new_image_ids[ $key ] = hidamari_faq_import_image(
		$key,
		$image_root . $image[0],
		$faq_page_id,
		$image[1],
		$image[2]
	);
}

set_post_thumbnail( $faq_page_id, $new_image_ids['hero-desktop'] );
update_post_meta( $faq_page_id, 'hidamari_hero_mobile_id', $new_image_ids['hero-mobile'] );
update_post_meta( $faq_page_id, 'hidamari_page_faq_payment_anchor_image_id', $new_image_ids['payment'] );
update_post_meta( $faq_page_id, 'hidamari_page_faq_day_care_anchor_image_id', $new_image_ids['day-care'] );
update_post_meta( $faq_page_id, 'hidamari_page_faq_consultation_anchor_image_id', $new_image_ids['consultation'] );
update_post_meta( $faq_page_id, 'hidamari_page_faq_facility_anchor_image_id', $new_image_ids['facility'] );

$faq_data = array(
	'payment'     => array(
		'name'  => '費用・お支払い',
		'items' => array(
			array( 'faq-payment-1', '利用料金はどれくらいかかりますか？', 'ご利用されるサービス内容や要介護度により異なります。<br>ご見学・ご相談の際に、ご希望のプランに合わせた詳細なシミュレーションを作成し、丁寧にご説明します。', 0 ),
			array( 'faq-payment-2', '支払方法はどんな種類がありますか？', '基本的には以下の方法でお支払いいただきます。<br>詳細なお手続きについては、ご契約時にご案内します。<br><br>《支払方法》<br>・銀行口座からの自動引き落とし<br>・銀行振込', 0 ),
		),
	),
	'day-care'    => array(
		'name'  => '訪問介護・生活援助',
		'items' => array(
			array( 'faq-day-1', '具体的にどんなことをしてくれますか？', 'お食事や入浴、排せつの介助といった「身体介護」、お掃除や買い出しなどの「生活援助」まで、幅広く対応しております。<br>利用者様の「自分らしく生活したい」という気持ちに寄り添ってサポートいたします。', 0 ),
			array( 'faq-day-2', '家族が同居していても生活援助はお願いできますか？', '同居されているかご家族様がいらっしゃる場合は、介護保険制度のルール上、一部の生活援助をご利用いただけない場合がございます。<br><br>ご家庭の状況をお伺いし、利用可能なサービスをご提案いたしますので、まずはご相談ください！', 0 ),
			array( 'faq-day-3', '食事の準備で、アレルギーや刻み食などの対応は可能ですか？', 'はい、対応可能です。<br>利用者様のお体の状況やお好みに合わせて、安全でおいしく召し上がれるお食事の準備をいたします。', 0 ),
		),
	),
	'consultation' => array(
		'name'  => 'ご相談・居宅介護支援',
		'items' => array(
			array( 'top-faq-consult-1', '見学・相談だけでもいいですか？', 'もちろん大歓迎です！<br>見学だけでなく、ご相談だけでも大丈夫ですのでお気軽にお問い合わせください。', 1 ),
			array( 'faq-consult-2', 'まだ介護保険の申請をしていないのですが、相談してもいいですか？', 'はい、もちろんです！<br>介護保険の申請方法がわからないといった基礎的なご相談からお受けしております。<br><br>申請手続きのお手伝い・代行もいたしますのでお気軽にご相談ください！', 0 ),
			array( 'top-faq-consult-3', '介護保険はどうやったら使えるようになりますか？', '市役所・地域包括支援センターなどで手続きが必要になります。<br><br>手続きのお手伝い・代行もいたしますのであわせてご相談ください！', 3 ),
			array( 'faq-consult-4', 'ケアマネジャー（介護支援専門員）はどんなことをしてくれますか？', 'ご本人様とご家族様のご意向を丁寧にヒヤリングし、最適な介護サービスの組合せ（ケアプラン）を作成いたします。<br><br>また、各種サービス事業所との連絡や手配もすべて代行いたしますので、ご家族様の負担を軽減いたします。', 0 ),
		),
	),
	'daily-life'  => array(
		'name'  => '施設での生活',
		'items' => array(
			array( 'top-faq-life-1', 'どんな方が利用されていますか？', '要支援1〜2、要介護1〜4の方を対象としております。<br><br>現在該当していない方でも見学・ご相談は承っておりますので、お気軽にご相談ください！', 4 ),
			array( 'top-faq-life-2', '家族への連絡や報告はありますか？', 'もちろんいたします！<br>契約時にご家族様との連絡・ご報告の頻度についてもお話させていただきます。<br>また、頻度の変更についても対応いたしますのでご安心ください。', 5 ),
			array( 'top-faq-life-3', '送迎はありますか？', '対応エリア内でしたら、ご自宅まで送迎いたします！<br><br>《対応エリア》<br>○旭川市<br>　・神居古潭、江丹別、東旭川、上雨紛、<br>　　西神楽地区を除く<br>○東神楽町<br>　・ひじり野地区のみ<br>○鷹栖町<br>　・市街地、北野地区のみ', 2 ),
			array( 'top-faq-life-4', '急な体調変化の場合はどうなりますか？', '急な体調変化があった場合は、状態を確認してご家族へ連絡し、必要に応じて救急要請または協力医療機関を受診します。<br><br>《協力医療機関（架空設定）》<br>○ひだまり旭川メディカルセンター<br>※ポートフォリオ用の架空名称です。', 6 ),
		),
	),
);

$faq_keys  = array();
$term_ids  = array();
$front_ids = array();
foreach ( $faq_data as $term_slug => $group ) {
	$term_ids[ $term_slug ] = hidamari_faq_ensure_term( $group['name'], $term_slug );

	foreach ( $group['items'] as $item_index => $item ) {
		$faq_id     = hidamari_faq_upsert_post(
			'hidamari_faq',
			$item[0],
			array(
				'post_status'  => 'publish',
				'post_title'   => $item[1],
				'post_content' => $item[2],
				'menu_order'   => $item_index + 1,
			)
		);
		$faq_keys[] = $item[0];

		$show_on_front = $item[3] > 0;
		update_post_meta( $faq_id, 'hidamari_show_on_front', $show_on_front );
		update_post_meta( $faq_id, 'hidamari_front_order', $show_on_front ? $item[3] : 0 );
		wp_set_object_terms( $faq_id, array( $term_ids[ $term_slug ] ), 'hidamari_faq_cat', false );

		if ( $show_on_front ) {
			$front_ids[] = $faq_id;
		}
	}
}

$image_migration_keys = array_map(
	static function ( $key ) {
		return 'faq-image-' . $key;
	},
	array_keys( $new_images )
);

flush_rewrite_rules( false );

echo 'faq_page=' . $faq_page_id . PHP_EOL;
echo 'faq_categories=' . count( $term_ids ) . PHP_EOL;
echo 'faqs=' . hidamari_faq_count_posts( 'hidamari_faq', $faq_keys ) . PHP_EOL;
echo 'front_faqs=' . count( $front_ids ) . PHP_EOL;
echo 'new_images=' . hidamari_faq_count_posts( 'attachment', $image_migration_keys ) . PHP_EOL;
echo 'desktop_hero=' . ( has_post_thumbnail( $faq_page_id ) ? 'yes' : 'no' ) . PHP_EOL;
echo 'mobile_hero=' . ( (int) get_post_meta( $faq_page_id, 'hidamari_hero_mobile_id', true ) > 0 ? 'yes' : 'no' ) . PHP_EOL;
echo 'category_images=' . ( (int) get_post_meta( $faq_page_id, 'hidamari_page_faq_payment_anchor_image_id', true ) > 0 && (int) get_post_meta( $faq_page_id, 'hidamari_page_faq_facility_anchor_image_id', true ) > 0 ? 'yes' : 'no' ) . PHP_EOL;
echo 'theme_version=' . wp_get_theme()->get( 'Version' ) . PHP_EOL;

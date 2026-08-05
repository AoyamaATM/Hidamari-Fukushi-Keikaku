<?php
/**
 * Build the idempotent Local data required by the facility introduction page.
 *
 * Run with Local's WP-CLI:
 * wp eval-file C:/Users/lihui/Documents/Codex_Akutsu/tools/local-about-fixtures.php
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

if ( ! post_type_exists( 'hidamari_flow' ) ) {
	throw new RuntimeException( 'Update and activate hidamari-site-core before running the About migration.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Return statuses that keep fixture lookup idempotent after manual changes.
 *
 * @param string $post_type Post type.
 * @return array<string>
 */
function hidamari_about_post_statuses( $post_type ) {
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
function hidamari_about_find_post( $post_type, $migration_key ) {
	$post_ids = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => hidamari_about_post_statuses( $post_type ),
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
function hidamari_about_count_posts( $post_type, $migration_keys ) {
	return count(
		get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => hidamari_about_post_statuses( $post_type ),
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
 * Create or update one migrated content record.
 *
 * @param string               $post_type     Post type.
 * @param string               $migration_key Migration key.
 * @param array<string, mixed> $data          Post data.
 * @return int
 */
function hidamari_about_upsert_post( $post_type, $migration_key, $data ) {
	$post_id           = hidamari_about_find_post( $post_type, $migration_key );
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
function hidamari_about_import_image( $key, $file, $parent_id, $title, $alt ) {
	$migration_key = 'about-image-' . $key;
	$attachment_id = hidamari_about_find_post( 'attachment', $migration_key );

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

$about_page = get_page_by_path( 'about-us', OBJECT, 'page' );
if ( ! $about_page instanceof WP_Post ) {
	throw new RuntimeException( 'Run local-top-fixtures.php before the About migration.' );
}

$about_page_id = wp_update_post(
	array(
		'ID'          => $about_page->ID,
		'post_status' => 'publish',
		'post_title'  => '施設紹介',
	),
	true
);
if ( is_wp_error( $about_page_id ) ) {
	throw new RuntimeException( $about_page_id->get_error_message() );
}
$about_page_id = (int) $about_page_id;

$settings = get_option( 'hidamari_settings', array() );
$settings = is_array( $settings ) ? $settings : array();
$settings = wp_parse_args(
	$settings,
	array(
		'organization_name'      => '社会福祉法人 ひだまり福祉計画',
		'facility_name'          => 'ひだまりケア旭川',
		'services_label'         => '通所介護・訪問介護・居宅介護支援事業所',
		'address'                => '北海道旭川市 旭町2条7丁目 12-77',
		'facility_phone_display' => '0166-xx-yyyy',
		'facility_phone_link'    => '',
		'phone_display'          => '0166-xx-xxxx',
		'phone_link'             => '',
		'business_hours'         => '平日 9:00 ～ 17:00',
	)
);
update_option( 'hidamari_settings', $settings );

$image_root = dirname( __DIR__ ) . '/docs/img/';
$new_images = array(
	'hero-desktop' => array( 'mv-AboutUs_PC.webp', '施設紹介 PCヒーロー', '' ),
	'hero-mobile'  => array( 'mv-AboutUs_SP.webp', '施設紹介 SPヒーロー', '' ),
	'schedule'     => array( 'TimeSchedule.png', 'デイサービスのタイムスケジュール', 'デイサービスのタイムスケジュール' ),
	'dayservice-01' => array( 'dayservice_01.webp', 'デイサービス写真1', '通路で挨拶するスタッフ' ),
	'dayservice-02' => array( 'dayservice_02.webp', 'デイサービス写真2', '屋外で過ごす利用者' ),
	'dayservice-03' => array( 'dayservice_03.webp', 'デイサービス写真3', '窓辺で会話する利用者とスタッフ' ),
);

$new_image_ids = array();
foreach ( $new_images as $key => $image ) {
	$new_image_ids[ $key ] = hidamari_about_import_image(
		$key,
		$image_root . $image[0],
		$about_page_id,
		$image[1],
		$image[2]
	);
}

$reused_image_keys = array(
	'profile'    => 'top-image-reason_02',
	'service_01' => 'top-image-service_01',
	'service_02' => 'top-image-service_02',
	'service_03' => 'top-image-service_03',
);
$reused_image_ids  = array();

foreach ( $reused_image_keys as $slot => $migration_key ) {
	$attachment_id = hidamari_about_find_post( 'attachment', $migration_key );
	if ( $attachment_id <= 0 ) {
		throw new RuntimeException( 'Required TOP image is missing: ' . $migration_key );
	}
	$reused_image_ids[ $slot ] = $attachment_id;
}

set_post_thumbnail( $about_page_id, $new_image_ids['hero-desktop'] );
update_post_meta( $about_page_id, 'hidamari_hero_mobile_id', $new_image_ids['hero-mobile'] );
update_post_meta( $about_page_id, 'hidamari_page_profile_image_id', $reused_image_ids['profile'] );
update_post_meta( $about_page_id, 'hidamari_page_service_01_image_id', $reused_image_ids['service_01'] );
update_post_meta( $about_page_id, 'hidamari_page_schedule_image_id', $new_image_ids['schedule'] );
update_post_meta( $about_page_id, 'hidamari_page_dayservice_01_image_id', $new_image_ids['dayservice-01'] );
update_post_meta( $about_page_id, 'hidamari_page_dayservice_02_image_id', $new_image_ids['dayservice-02'] );
update_post_meta( $about_page_id, 'hidamari_page_dayservice_03_image_id', $new_image_ids['dayservice-03'] );
update_post_meta( $about_page_id, 'hidamari_page_service_02_image_id', $reused_image_ids['service_02'] );
update_post_meta( $about_page_id, 'hidamari_page_service_03_image_id', $reused_image_ids['service_03'] );

$step_one_content = <<<'HTML'
<p>当施設のご利用には介護保険で要支援２認定以上が必要です。</p>
<ol class="flow-substeps">
	<li>
		<h4 class="flow-substeps__title"><span lang="en">Step 1.</span> 申請</h4>
		<p>市役所（町役場）、地域包括支援センターにて要介護認定の申請ができます。</p>
	</li>
	<li>
		<h4 class="flow-substeps__title"><span lang="en">Step 2.</span> 要介護認定</h4>
		<p>申請後は行政職員による訪問調査や主治医による意見書作成、介護認定審査会による審査を経て決定されます。</p>
		<p>※ 申請から結果が通知されるまで１か月程度がかかります。</p>
	</li>
</ol>
HTML;

$contact_page = get_page_by_path( 'contact', OBJECT, 'page' );
$contact_url  = $contact_page instanceof WP_Post ? get_permalink( $contact_page ) : home_url( '/contact/' );
$flow_items   = array(
	array(
		'key'     => 'about-flow-01',
		'title'   => '介護保険の申請・認定',
		'content' => $step_one_content,
	),
	array(
		'key'     => 'about-flow-02',
		'title'   => '担当ケアマネジャーと相談',
		'content' => '<p>要介護度が認定されると、担当ケアマネジャーを紹介されます。<br>ケアマネジャーは、必要なサービスを決める「ケアプラン」という計画書を作成する役割を担っています。</p><p>ケアマネジャーが作成した計画をもとに、利用する施設を決めていきます。<br>施設との契約はケアマネジャーを通じて行われます。</p>',
	),
	array(
		'key'        => 'about-flow-03',
		'title'      => '見学予約',
		'content'    => '<p>お電話・お問い合わせフォームから承っております。<br>パンフレットなどの資料請求・見学のご案内をいたします。</p>',
		'link_label' => 'お問い合わせフォーム',
		'link_url'   => $contact_url,
	),
	array(
		'key'     => 'about-flow-04',
		'title'   => '見学・面談',
		'content' => '<p>施設の雰囲気・支援内容などをご覧いただきます。<br>その上で、ケアマネジャーを含めた三者でご利用までの流れ、必要な書類・料金のご説明をいたします。</p>',
	),
	array(
		'key'     => 'about-flow-05',
		'title'   => '利用申込み',
		'content' => '<p>指定の様式で「健康診断書」「入居申込書 兼 個人情報使用同意書」、医療機関様式で「診療情報提供書」をご用意いただきます。</p>',
	),
	array(
		'key'     => 'about-flow-06',
		'title'   => '利用審査・提供サービス選考',
		'content' => '<p>ご用意いただいた書類などをもとに、施設を利用できるかの審査をいたします。<br>また、ケアプランや見学時の記録から提供するサービスの内容を選考いたします。</p>',
	),
	array(
		'key'     => 'about-flow-07',
		'title'   => '重要事項のご説明・ご契約',
		'content' => '<p>選考したサービス内容・重要事項・施設規定をご説明します。<br>内容について十分にご納得いただけましたら、契約書にご署名・ご捺印いただきます。</p><p>利用開始日の決定後、開始日までに初回利用料を指定口座へお振込みください。</p>',
	),
	array(
		'key'     => 'about-flow-08',
		'title'   => 'ご利用開始',
		'content' => '<p>職員一同、万全の準備を整えてお迎えいたします。</p>',
	),
);

foreach ( $flow_items as $index => $flow_item ) {
	$flow_id = hidamari_about_upsert_post(
		'hidamari_flow',
		$flow_item['key'],
		array(
			'post_status'  => 'publish',
			'post_title'   => $flow_item['title'],
			'post_content' => $flow_item['content'],
			'menu_order'   => $index + 1,
		)
	);
	update_post_meta( $flow_id, 'hidamari_flow_note', isset( $flow_item['note'] ) ? $flow_item['note'] : '' );
	update_post_meta( $flow_id, 'hidamari_flow_link_label', isset( $flow_item['link_label'] ) ? $flow_item['link_label'] : '' );
	update_post_meta( $flow_id, 'hidamari_flow_link_url', isset( $flow_item['link_url'] ) ? $flow_item['link_url'] : '' );
}

$image_migration_keys = array_map(
	static function ( $key ) {
		return 'about-image-' . $key;
	},
	array_keys( $new_images )
);
$flow_migration_keys  = array_column( $flow_items, 'key' );

flush_rewrite_rules( false );

echo 'about_page=' . $about_page_id . PHP_EOL;
echo 'new_images=' . hidamari_about_count_posts( 'attachment', $image_migration_keys ) . PHP_EOL;
echo 'reused_images=' . count( $reused_image_ids ) . PHP_EOL;
echo 'flows=' . hidamari_about_count_posts( 'hidamari_flow', $flow_migration_keys ) . PHP_EOL;
echo 'desktop_hero=' . ( has_post_thumbnail( $about_page_id ) ? 'yes' : 'no' ) . PHP_EOL;
echo 'mobile_hero=' . ( (int) get_post_meta( $about_page_id, 'hidamari_hero_mobile_id', true ) > 0 ? 'yes' : 'no' ) . PHP_EOL;
echo 'theme_version=' . wp_get_theme()->get( 'Version' ) . PHP_EOL;

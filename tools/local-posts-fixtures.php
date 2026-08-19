<?php
/**
 * Build the idempotent Local posts required by the news archive migration.
 *
 * Run with Local's WP-CLI:
 * wp eval-file C:/Users/lihui/Documents/Codex_Akutsu/tools/local-posts-fixtures.php
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

/**
 * Return one migrated post ID by its stable key.
 *
 * @param string $migration_key Migration key.
 * @return int
 */
function hidamari_posts_local_find_post( $migration_key ) {
	$post_ids = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private', 'trash' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_hidamari_migration_key',
			'meta_value'     => $migration_key,
		)
	);

	return ! empty( $post_ids ) ? (int) $post_ids[0] : 0;
}

/**
 * Create or update one migrated post.
 *
 * @param array<string, string> $post_data Post fixture.
 * @param int                   $category_id Category term ID.
 * @return int
 */
function hidamari_posts_local_upsert_post( $post_data, $category_id ) {
	$post_id = hidamari_posts_local_find_post( $post_data['key'] );
	$data    = array(
		'post_type'     => 'post',
		'post_status'   => 'publish',
		'post_title'    => $post_data['title'],
		'post_name'     => $post_data['slug'],
		'post_content'  => $post_data['content'],
		'post_excerpt'  => $post_data['excerpt'],
		'post_date'     => $post_data['date'],
		'post_date_gmt' => get_gmt_from_date( $post_data['date'] ),
	);

	if ( $post_id > 0 ) {
		$data['ID'] = $post_id;
	}

	$post_id = wp_insert_post( $data, true );
	if ( is_wp_error( $post_id ) ) {
		throw new RuntimeException( $post_id->get_error_message() );
	}

	update_post_meta( $post_id, '_hidamari_migration_key', $post_data['key'] );
	wp_set_post_categories( $post_id, array( $category_id ), false );

	return (int) $post_id;
}

/**
 * Ensure a standard post category exists.
 *
 * @param string $name Category name.
 * @param string $slug Category slug.
 * @return int
 */
function hidamari_posts_local_ensure_category( $name, $slug ) {
	$existing = term_exists( $slug, 'category' );
	if ( is_array( $existing ) ) {
		return (int) $existing['term_id'];
	}

	if ( is_int( $existing ) ) {
		return $existing;
	}

	$created = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
	if ( is_wp_error( $created ) ) {
		throw new RuntimeException( $created->get_error_message() );
	}

	return (int) $created['term_id'];
}

$news_page = get_page_by_path( 'news', OBJECT, 'page' );
if ( ! $news_page instanceof WP_Post ) {
	throw new RuntimeException( 'Run local-top-fixtures.php before migrating posts.' );
}

$category_ids = array(
	'news' => hidamari_posts_local_ensure_category( 'ニュース', 'news' ),
	'blog' => hidamari_posts_local_ensure_category( 'ブログ', 'blog' ),
);

$posts = array(
	array(
		'key'      => 'top-news-2026-06-15',
		'title'    => '7月の営業日についてのお知らせ',
		'slug'     => 'july-business-days-2026',
		'date'     => '2026-06-15 09:00:00',
		'category' => 'news',
		'excerpt'  => '2026年7月の営業予定と、ご利用・見学に関するご相談窓口をご案内します。',
		'content'  => '<p>いつもひだまりケア旭川をご利用いただき、ありがとうございます。</p><p>7月は、デイサービス・訪問介護ともに通常どおり営業する想定です。<br>見学やご利用に関するご相談は、お問い合わせフォームのデモ画面をご確認ください。入力内容は送信・保存されません。</p><p>本記事の営業日とサービス内容は、ポートフォリオ用の架空設定です。</p>',
	),
	array(
		'key'      => 'top-news-2026-06-12',
		'title'    => '暑い季節の体調管理について',
		'slug'     => 'summer-health-care',
		'date'     => '2026-06-12 09:00:00',
		'category' => 'news',
		'excerpt'  => '暑い季節を安全に過ごすための水分補給や室温管理についてご案内します。',
		'content'  => '<p>気温と湿度が高い日が増えてきました。こまめな水分補給と、無理のない範囲での室温調整をお願いいたします。</p><p>デイサービスでは、到着時や活動中の体調確認を行い、室内環境にも配慮しています。体調に気になる点がある場合は、事前にスタッフへお知らせください。</p>',
	),
	array(
		'key'      => 'top-blog-2026-06-10',
		'title'    => '初夏のレクリエーションを行いました',
		'slug'     => 'early-summer-recreation',
		'date'     => '2026-06-10 09:00:00',
		'category' => 'blog',
		'excerpt'  => '季節を感じる飾りづくりと軽い体操を、皆さまと一緒に楽しみました。',
		'content'  => '<p>初夏のレクリエーションとして、季節の飾りづくりと軽い体操を行いました。</p><p>色や形を相談しながら作品を仕上げ、完成後は皆さまで記念撮影をしました。これからも季節を感じられる活動を企画してまいります。</p>',
	),
	array(
		'key'      => 'news-2026-06-05',
		'title'    => '施設見学・個別相談を受け付けています',
		'slug'     => 'facility-tour-consultation',
		'date'     => '2026-06-05 09:00:00',
		'category' => 'news',
		'excerpt'  => '施設見学やサービス利用に関する個別相談を受け付けています。',
		'content'  => '<p>デイサービスの施設見学と、ご利用に関する個別相談を想定したサンプル記事です。</p><p>お問い合わせフォームのデモ画面では入力・確認操作ができますが、内容は送信・保存されません。実際の見学予約は受け付けていません。</p>',
	),
	array(
		'key'      => 'news-2026-06-01',
		'title'    => '6月の営業日についてのお知らせ',
		'slug'     => 'june-business-days-2026',
		'date'     => '2026-06-01 09:00:00',
		'category' => 'news',
		'excerpt'  => '2026年6月のデイサービス・訪問介護の営業予定をご案内します。',
		'content'  => '<p>6月は、デイサービス・訪問介護ともに通常どおり営業する予定です。</p><p>予定変更や臨時のお知らせがある場合は、担当スタッフから個別にご連絡します。ご不明な点はお気軽にお問い合わせください。</p>',
	),
	array(
		'key'      => 'blog-2026-05-28',
		'title'    => '園芸活動で花の苗を植えました',
		'slug'     => 'gardening-activity',
		'date'     => '2026-05-28 09:00:00',
		'category' => 'blog',
		'excerpt'  => '園芸活動の時間に、色とりどりの花の苗を植えました。',
		'content'  => '<p>園芸活動の時間に、利用者の皆さまと花の苗を植えました。</p><p>土に触れながら花の色や成長を楽しみに作業を進めました。水やりを続け、季節の変化を一緒に見守っていきます。</p>',
	),
	array(
		'key'      => 'news-2026-05-20',
		'title'    => '送迎車を1台増車しました',
		'slug'     => 'new-transport-vehicle',
		'date'     => '2026-05-20 09:00:00',
		'category' => 'news',
		'excerpt'  => 'より安全で円滑な送迎のため、送迎車を1台増車しました。',
		'content'  => '<p>利用者の皆さまをより安全かつ円滑に送迎するため、送迎車を1台増車しました。</p><p>運行前点検と安全確認を徹底し、今後も安心してご利用いただける送迎体制づくりに努めてまいります。</p>',
	),
	array(
		'key'      => 'blog-2026-05-15',
		'title'    => 'スタッフ研修を実施しました',
		'slug'     => 'staff-training',
		'date'     => '2026-05-15 09:00:00',
		'category' => 'blog',
		'excerpt'  => '安全な介助と緊急時対応をテーマに、スタッフ研修を実施しました。',
		'content'  => '<p>安全な介助と緊急時の対応をテーマに、スタッフ研修を実施しました。</p><p>日々の支援を振り返りながら手順を確認し、事例をもとに意見交換を行いました。今後も継続的な研修に取り組みます。</p>',
	),
	array(
		'key'      => 'news-2026-05-08',
		'title'    => '採用情報を更新しました',
		'slug'     => 'recruitment-update',
		'date'     => '2026-05-08 09:00:00',
		'category' => 'news',
		'excerpt'  => 'ひだまりケア旭川の採用情報を更新しました。',
		'content'  => '<p>ひだまりケア旭川の採用情報を想定したサンプル記事です。</p><p>実際の求人募集、施設見学、事前相談、お問い合わせ受付は行っていません。</p>',
	),
	array(
		'key'      => 'news-2026-05-01',
		'title'    => '5月の営業日についてのお知らせ',
		'slug'     => 'may-business-days-2026',
		'date'     => '2026-05-01 09:00:00',
		'category' => 'news',
		'excerpt'  => '2026年5月のデイサービス・訪問介護の営業予定をご案内します。',
		'content'  => '<p>5月は、デイサービス・訪問介護ともに通常どおり営業する予定です。</p><p>祝日前後のご利用予定や送迎時間について確認が必要な場合は、担当スタッフまでお気軽にご相談ください。</p>',
	),
);

$post_ids = array();
foreach ( $posts as $post_data ) {
	$post_ids[] = hidamari_posts_local_upsert_post( $post_data, $category_ids[ $post_data['category'] ] );
}

$hello_world = get_page_by_path( 'hello-world', OBJECT, 'post' );
if ( $hello_world instanceof WP_Post && 'Hello world!' === $hello_world->post_title ) {
	wp_trash_post( $hello_world->ID );
}

update_option( 'page_for_posts', $news_page->ID );
update_option( 'posts_per_page', 10 );
flush_rewrite_rules( false );

$migration_keys = array_column( $posts, 'key' );
$migrated_ids   = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private', 'trash' ),
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
);

echo 'posts=' . count( $migrated_ids ) . PHP_EOL;
echo 'news=' . get_category( $category_ids['news'] )->count . PHP_EOL;
echo 'blog=' . get_category( $category_ids['blog'] )->count . PHP_EOL;
echo 'posts_page=' . (int) get_option( 'page_for_posts' ) . PHP_EOL;
echo 'posts_per_page=' . (int) get_option( 'posts_per_page' ) . PHP_EOL;

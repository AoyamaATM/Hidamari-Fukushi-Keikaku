<?php
/**
 * Build the idempotent Local data required by the privacy policy page.
 *
 * Run with Local's WP-CLI:
 * wp eval-file C:/Users/user/Documents/Codex_Akutsu/tools/local-privacy-fixtures.php
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

$privacy_content = <<<'HTML'
<p>本サイトはWeb制作実績を紹介するためのポートフォリオ用サンプルサイトです。掲載している法人名、施設名、住所、電話番号、サービス内容等は架空のものであり、実在する法人・施設とは関係ありません。</p>

<h2 class="section-heading">1. 個人情報の入力について</h2>
<p>本サイトは実際の介護相談やお問い合わせを受け付けていません。お問い合わせフォームを試す場合も、氏名、メールアドレス、健康状態、介護状況など、実在する方の個人情報や機微な情報を入力しないでください。</p>

<h2 class="section-heading">2. デモフォームの取り扱い</h2>
<p>お問い合わせフォームへ入力した内容は、入力確認と完了表示のためにブラウザーからWordPressへ送信されます。現行のデモ設定では、管理者へのメール通知、入力者への自動返信、WordPressデータベースへの送信内容の保存を行いません。</p>

<h2 class="section-heading">3. アクセスログ</h2>
<p>サイトの安定運用や不正アクセス対策のため、利用するサーバーやホスティング環境により、IPアドレス、ブラウザー情報、アクセス日時、閲覧URL等がアクセスログへ記録される場合があります。本サイトでは、広告配信や利用者を追跡する目的のアクセス解析ツールを設定していません。</p>

<h2 class="section-heading">4. 外部サービス</h2>
<p>本サイトは表示用フォントとしてGoogle Fontsを利用しています。ページ表示時にフォントデータを取得するため、利用者のブラウザーからGoogleへ通信が行われます。Googleによる情報の取り扱いは、<a href="https://policies.google.com/privacy" rel="external noopener noreferrer">Google プライバシーポリシー</a>をご確認ください。</p>

<h2 class="section-heading">5. Cookieについて</h2>
<p>本サイトは広告配信や行動追跡を目的としたCookieを使用しません。WordPressや使用プラグインが、画面表示、フォーム機能、管理画面へのログイン、セキュリティ等に必要なCookieを使用する場合があります。</p>

<h2 class="section-heading">6. お問い合わせ窓口</h2>
<p>本サイトに掲載している住所・電話番号は架空情報であり、連絡先として利用できません。ポートフォリオ運営者の公開連絡先は本ページに掲載していません。</p>

<p>最終更新日：2026年8月19日</p>
HTML;

$privacy_page = get_page_by_path( 'privacy-policy', OBJECT, 'page' );
$page_data    = array(
	'post_type'    => 'page',
	'post_status'  => 'publish',
	'post_title'   => 'プライバシーポリシー',
	'post_name'    => 'privacy-policy',
	'post_content' => $privacy_content,
);

if ( $privacy_page instanceof WP_Post ) {
	$page_data['ID'] = $privacy_page->ID;
}

$privacy_page_id = wp_insert_post( wp_slash( $page_data ), true );
if ( is_wp_error( $privacy_page_id ) ) {
	throw new RuntimeException( $privacy_page_id->get_error_message() );
}

update_post_meta( $privacy_page_id, '_hidamari_migration_key', 'privacy-page' );
update_option( 'wp_page_for_privacy_policy', $privacy_page_id );

$stored_content = (string) get_post_field( 'post_content', $privacy_page_id );
$theme          = wp_get_theme( 'hidamari-care-asahikawa' );

printf( "privacy_page=%d\n", $privacy_page_id );
printf( "privacy_policy_option=%d\n", (int) get_option( 'wp_page_for_privacy_policy' ) );
printf( "body_sections=%d\n", substr_count( $stored_content, '<h2' ) );
printf( "body_list_items=%d\n", substr_count( $stored_content, '<li>' ) );
printf( "body_matches=%s\n", $stored_content === $privacy_content ? 'yes' : 'no' );
printf( "theme_version=%s\n", $theme->get( 'Version' ) );

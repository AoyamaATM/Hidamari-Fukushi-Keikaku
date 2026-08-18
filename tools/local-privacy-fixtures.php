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
<p>社会福祉法人 ひだまり福祉計画（以下、「当法人」といいます）は、ご利用者様およびご家族様の個人情報の重要性を認識し、以下の通り個人情報保護方針を定め、全職員が一体となって個人情報の適切な保護に努めます。</p>

<h2 class="section-heading">1. 法令等の遵守</h2>
<p>当法人は、個人情報の保護に関する法律、介護保険法、および厚生労働省が定める指針等の関連法令を遵守いたします。</p>

<h2 class="section-heading">2. 個人情報の収集・利用目的</h2>
<p>当法人は、以下の目的のために必要な範囲で、適正かつ公正な手段によって個人情報を収集し、利用いたします。</p>
<ul>
<li>介護サービスの提供
<ul>
<li>通所介護、訪問介護、居宅介護支援の各事業所におけるサービスの提供</li>
<li>ケアプラン（居宅サービス計画）の作成、およびサービス提供会議等の実施</li>
</ul>
</li>
<li>事務・管理運営
<ul>
<li>入退所等の管理、会計・経理事務</li>
<li>介護事故等の報告、およびサービスの質向上に向けた分析</li>
</ul>
</li>
<li>外部への情報提供
<ul>
<li>他の介護サービス事業者や医療機関等との連携、照会への回答</li>
<li>ご家族様等への心身の状況説明</li>
<li>介護保険事務（審査支払機関へのレセプト提出、照会への回答）</li>
<li>損害賠償保険などに係る保険会社等への相談または届出</li>
</ul>
</li>
</ul>

<h2 class="section-heading">3. 個人情報の安全管理</h2>
<p>当法人は、取り扱う個人情報の漏洩、滅失、または毀損を防止するため、情報セキュリティ対策をはじめとする安全管理措置を講じ、厳重に管理いたします。また、職員に対して継続的な教育・啓発活動を行い、意識の向上を図ります。</p>

<h2 class="section-heading">4. 第三者への提供</h2>
<p>当法人は、法令に定める場合や、上記「利用目的」の範囲内で連携機関に提供する場合を除き、あらかじめご本人の同意を得ることなく、個人情報を第三者に提供いたしません。</p>

<h2 class="section-heading">5. 開示・訂正・利用停止</h2>
<p>当法人が保有する個人情報について、ご本人または代理人様から開示・訂正・利用停止等の請求があった場合には、速やかに対応いたします。</p>

<h2 class="section-heading">6. お問い合わせ窓口</h2>
<p>当法人の個人情報の取り扱いに関するご質問や苦情、ご相談については、以下の窓口までお問い合わせください。</p>
<p>【お問い合わせ先】<br>
社会福祉法人 ひだまり福祉計画（担当：法務部）<br><br>
住所：北海道旭川市旭町2条7丁目 12番77号<br>
電話番号：0166-aa-bbbb（法務部直通）<br>
受付時間：平日　9:00〜17:30</p>
HTML;

$privacy_page = get_page_by_path( 'privacy-policy', OBJECT, 'page' );
$page_data    = array(
	'post_type'    => 'page',
	'post_status'  => 'publish',
	'post_title'   => '個人情報保護方針（プライバシーポリシー）',
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

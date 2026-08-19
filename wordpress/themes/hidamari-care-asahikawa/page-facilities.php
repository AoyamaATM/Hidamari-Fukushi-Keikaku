<?php
/**
 * Facilities page template.
 *
 * @package Hidamari_Care_Asahikawa
 */

$page_id           = get_queried_object_id();
$organization_name = hidamari_care_asahikawa_setting( 'organization_name', '社会福祉法人 ひだまり福祉計画' );
$facility_name     = hidamari_care_asahikawa_setting( 'facility_name', 'ひだまりケア旭川' );
$services_label    = hidamari_care_asahikawa_setting( 'services_label', '通所介護・訪問介護・居宅介護支援事業所' );
$address           = hidamari_care_asahikawa_setting( 'address', '北海道旭川市（架空設定）' );
$phone_display     = hidamari_care_asahikawa_setting( 'facility_phone_display', '掲載なし（デモ）' );
$phone_link        = preg_replace( '/[^0-9+]/', '', hidamari_care_asahikawa_setting( 'facility_phone_link', '' ) );

get_header();
?>
<main class="page-shell" id="main">
	<?php get_template_part( 'template-parts/common/subpage-hero', null, array( 'page_id' => $page_id, 'title' => '全施設一覧' ) ); ?>
	<?php get_template_part( 'template-parts/common/breadcrumb', null, array( 'label' => '全施設一覧' ) ); ?>

	<div class="skip-target" id="main-content" tabindex="-1"></div>

	<section class="section intro-surface">
		<div class="content-width profile-grid">
			<?php echo hidamari_care_asahikawa_page_image( $page_id, 'facilities_organization', 'hidamari-card', array( 'class' => 'section-photo', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div>
				<h2 class="section-heading"><?php echo esc_html( $organization_name ); ?></h2>
				<dl class="data-list">
					<div><dt>法人代表</dt><dd>旭川 太郎</dd></div>
					<div><dt>設立</dt><dd>平成27年 3月 1日</dd></div>
					<div><dt>住所</dt><dd><?php echo esc_html( $address ); ?></dd></div>
					<div>
						<dt>電話番号</dt>
						<dd>
							<?php if ( '' !== $phone_link ) : ?>
								<a href="tel:<?php echo esc_attr( $phone_link ); ?>"><?php echo esc_html( $phone_display ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $phone_display ); ?>
							<?php endif; ?>
						</dd>
					</div>
				</dl>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="content-width philosophy">
			<h2 class="section-heading">法人理念</h2>
			<strong>日だまりのような温かい介護を、地域に。</strong>
			<p>
				私たちは、ご利用者様お一人おひとりが、住み慣れた地域で自分らしく、日向ぼっこをしている時のような安心感を持って過ごせる場所を目指しています。<br>
				全施設共通の想いで、ご家族に寄り添い続けます。
			</p>
		</div>
	</section>

	<section class="section">
		<div class="content-width profile-grid">
			<?php echo hidamari_care_asahikawa_page_image( $page_id, 'facilities_facility', 'hidamari-card', array( 'class' => 'section-photo', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div>
				<h2 class="section-heading"><?php echo esc_html( $facility_name ); ?></h2>
				<dl class="data-list">
					<div><dt>事業</dt><dd><?php echo esc_html( $services_label ); ?></dd></div>
					<div><dt>管理者</dt><dd>旭川 太郎</dd></div>
					<div><dt>住所</dt><dd><?php echo esc_html( $address ); ?></dd></div>
					<div>
						<dt>電話番号</dt>
						<dd>
							<?php if ( '' !== $phone_link ) : ?>
								<a href="tel:<?php echo esc_attr( $phone_link ); ?>"><?php echo esc_html( $phone_display ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $phone_display ); ?>
							<?php endif; ?>
						</dd>
					</div>
				</dl>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="content-width">
			<h2 class="section-heading center-heading">スタッフ紹介</h2>
			<div class="grid-staff">
				<?php echo hidamari_care_asahikawa_page_image( $page_id, 'facilities_staff', 'hidamari-card', array( 'class' => 'section-photo', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<dl class="data-list">
					<div><dt>管理者</dt><dd>旭川 太郎（写真左）</dd></div>
					<div><dt>生活相談員</dt><dd>旭町 花子（写真右）</dd></div>
					<div><dt>介護スタッフ</dt><dd>10名</dd></div>
					<div><dt>管理栄養士</dt><dd>2名</dd></div>
					<div><dt>医師・看護師</dt><dd>各1名</dd></div>
				</dl>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();

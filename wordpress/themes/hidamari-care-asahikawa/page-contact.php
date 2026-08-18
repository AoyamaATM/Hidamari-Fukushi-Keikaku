<?php
/**
 * Contact page template.
 *
 * @package Hidamari_Care_Asahikawa
 */

$page_id        = get_queried_object_id();
$phone_display  = hidamari_care_asahikawa_setting( 'phone_display', '0166-xx-xxxx' );
$phone_link     = hidamari_care_asahikawa_setting( 'phone_link', '' );
$business_hours = hidamari_care_asahikawa_setting( 'business_hours', '平日 9:00 ～ 17:00' );

get_header();
?>
<main class="page-shell" id="main">
	<?php get_template_part( 'template-parts/common/subpage-hero', null, array( 'page_id' => $page_id, 'title' => 'お問い合わせ' ) ); ?>
	<?php get_template_part( 'template-parts/common/breadcrumb', null, array( 'label' => 'お問い合わせ' ) ); ?>

	<div class="skip-target" id="main-content" tabindex="-1"></div>

	<section class="section contact-band contact-page-band">
		<div class="content-width">
			<p class="lead contact-intro">
				<?php esc_html_e( 'はじめての介護で不安なこと、わからないことなど、どんな些細なことでもお気軽にご相談ください。', 'hidamari-care-asahikawa' ); ?><br>
				<?php esc_html_e( '専門スタッフが、ご本人様とご家族様の気持ちに寄り添って丁寧にお答えいたします。', 'hidamari-care-asahikawa' ); ?>
			</p>
			<p class="contact-note">
				<?php esc_html_e( '※ こちらはデモサイトです。フォームには実在する個人情報を入力しないでください。個人情報の取り扱い方針は「', 'hidamari-care-asahikawa' ); ?><a href="<?php echo esc_url( hidamari_care_asahikawa_page_url( 'privacy-policy' ) ); ?>"><?php esc_html_e( 'プライバシーポリシー', 'hidamari-care-asahikawa' ); ?></a><?php esc_html_e( '」をご覧ください。', 'hidamari-care-asahikawa' ); ?>
			</p>

			<div class="phone-card">
				<h2 class="phone-card__label"><?php esc_html_e( 'お電話でのお問い合わせ', 'hidamari-care-asahikawa' ); ?></h2>
				<p class="phone-card__text">
					<?php esc_html_e( 'お急ぎの方、直接お話したい方は', 'hidamari-care-asahikawa' ); ?><br>
					<?php esc_html_e( 'こちらからご連絡ください。', 'hidamari-care-asahikawa' ); ?>
				</p>
				<span class="phone-card__note"><?php esc_html_e( '総合窓口', 'hidamari-care-asahikawa' ); ?></span>
				<strong class="phone-card__number">
					<?php if ( '' !== $phone_link ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone_link ) ); ?>"><?php echo esc_html( $phone_display ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $phone_display ); ?>
					<?php endif; ?>
				</strong>
				<span class="phone-card__note"><?php echo esc_html( '受付時間　' . $business_hours ); ?></span>
			</div>

			<p class="contact-faq-copy">
				<?php esc_html_e( 'よく寄せられるご質問と回答をまとめています。', 'hidamari-care-asahikawa' ); ?><br>
				<?php esc_html_e( 'ぜひ一度ご確認ください。', 'hidamari-care-asahikawa' ); ?>
			</p>
			<a class="button button--cta" href="<?php echo esc_url( hidamari_care_asahikawa_page_url( 'faq' ) ); ?>"><?php esc_html_e( 'よくあるご質問', 'hidamari-care-asahikawa' ); ?></a>

			<?php get_template_part( 'template-parts/common/contact-form' ); ?>
		</div>
	</section>
</main>
<?php
get_footer();

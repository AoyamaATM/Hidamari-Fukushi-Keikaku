<?php
/**
 * Shared contact information and form integration point.
 *
 * @package Hidamari_Care_Asahikawa
 */

$show_form      = ! empty( $args['show_form'] );
$phone_display  = hidamari_care_asahikawa_setting( 'phone_display', '0166-xx-xxxx' );
$phone_link     = hidamari_care_asahikawa_setting( 'phone_link', '' );
$business_hours = hidamari_care_asahikawa_setting( 'business_hours', '平日 9:00 ～ 17:00' );
$form_id        = (int) get_option( 'hidamari_forminator_form_id' );
?>
<div class="content-width">
	<h2 class="section-heading center-heading"><?php esc_html_e( 'お問い合わせ', 'hidamari-care-asahikawa' ); ?></h2>
	<div class="phone-card">
		<span class="phone-card__label"><?php esc_html_e( 'お電話でのお問い合わせ', 'hidamari-care-asahikawa' ); ?></span>
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
	<p class="policy-note">
		<?php esc_html_e( '正確かつ迅速な対応を行うため、お問い合わせ内容を記録し当法人内で共有・利用させていただくことがございます。個人情報の取り扱いについては「', 'hidamari-care-asahikawa' ); ?><a href="<?php echo esc_url( hidamari_care_asahikawa_page_url( 'privacy-policy' ) ); ?>"><?php esc_html_e( 'プライバシーポリシー', 'hidamari-care-asahikawa' ); ?></a><?php esc_html_e( '」をご覧ください。', 'hidamari-care-asahikawa' ); ?>
	</p>
</div>

<?php if ( $show_form ) : ?>
	<?php if ( $form_id > 0 && shortcode_exists( 'forminator_form' ) ) : ?>
		<?php echo do_shortcode( '[forminator_form id="' . $form_id . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php else : ?>
		<div class="form-panel">
			<h2><?php esc_html_e( 'フォームでのお問い合わせ', 'hidamari-care-asahikawa' ); ?></h2>
			<p class="form-note"><?php esc_html_e( 'お問い合わせフォームは現在準備中です。お急ぎの場合はお電話でご相談ください。', 'hidamari-care-asahikawa' ); ?></p>
			<div class="form-actions">
				<a class="button button--cta" href="<?php echo esc_url( hidamari_care_asahikawa_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'お問い合わせページへ', 'hidamari-care-asahikawa' ); ?></a>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>

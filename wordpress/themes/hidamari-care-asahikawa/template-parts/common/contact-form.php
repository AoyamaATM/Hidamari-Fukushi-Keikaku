<?php
/**
 * Shared Forminator contact form panel.
 *
 * @package Hidamari_Care_Asahikawa
 */

$form_id = (int) get_option( 'hidamari_forminator_form_id' );
?>
<div class="form-panel contact-form contact-form--forminator">
	<h2><?php esc_html_e( 'フォームでのお問い合わせ', 'hidamari-care-asahikawa' ); ?></h2>
	<p class="form-intro">
		<?php esc_html_e( 'お問い合わせフォームの入力・確認操作を', 'hidamari-care-asahikawa' ); ?><br>
		<?php esc_html_e( 'デモとしてお試しいただけます。', 'hidamari-care-asahikawa' ); ?><br><br>
		<?php esc_html_e( '入力内容のメール送信・保存は行いません。', 'hidamari-care-asahikawa' ); ?><br>
		<?php esc_html_e( '実在する個人情報は入力しないでください。', 'hidamari-care-asahikawa' ); ?>
	</p>
	<p class="form-note"><?php esc_html_e( '※こちらはデモフォームです。入力内容はメール送信・保存されません。実在する個人情報は入力しないでください。', 'hidamari-care-asahikawa' ); ?></p>

	<?php if ( $form_id > 0 && shortcode_exists( 'forminator_form' ) ) : ?>
		<div class="contact-form__embed">
			<?php echo do_shortcode( '[forminator_form id="' . $form_id . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php else : ?>
		<p class="form-note"><?php esc_html_e( 'お問い合わせフォームは現在準備中です。このデモサイトでは電話受付も行っていません。', 'hidamari-care-asahikawa' ); ?></p>
		<div class="form-actions">
			<a class="button button--cta" href="<?php echo esc_url( hidamari_care_asahikawa_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'お問い合わせページへ', 'hidamari-care-asahikawa' ); ?></a>
		</div>
	<?php endif; ?>
</div>

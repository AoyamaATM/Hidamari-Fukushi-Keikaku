<?php
/**
 * FAQ accordion item.
 *
 * @package Hidamari_Care_Asahikawa
 */

$faq_id       = isset( $args['id'] ) ? sanitize_html_class( $args['id'] ) : '';
$faq_question = isset( $args['question'] ) ? (string) $args['question'] : '';
$faq_answer   = isset( $args['answer'] ) ? (string) $args['answer'] : '';

if ( '' === $faq_id || '' === $faq_question ) {
	return;
}
?>
<div>
	<button class="faq-question" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $faq_id ); ?>">
		<span class="faq-question-text"><?php echo esc_html( $faq_question ); ?></span>
	</button>
	<div class="faq-answer" id="<?php echo esc_attr( $faq_id ); ?>" hidden>
		<div><?php echo wp_kses_post( wpautop( $faq_answer ) ); ?></div>
	</div>
</div>

<?php
/**
 * Not found template.
 *
 * @package Hidamari_Care_Asahikawa
 */

get_header();
?>
<main id="main-content" class="page-shell">
	<section class="error-404 not-found">
		<h1><?php esc_html_e( 'ページが見つかりませんでした', 'hidamari-care-asahikawa' ); ?></h1>
		<p><?php esc_html_e( 'URLをご確認いただくか、トップページへお戻りください。', 'hidamari-care-asahikawa' ); ?></p>
		<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'トップページへ戻る', 'hidamari-care-asahikawa' ); ?></a></p>
	</section>
</main>
<?php
get_footer();

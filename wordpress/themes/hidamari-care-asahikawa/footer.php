<?php
/**
 * Site footer.
 *
 * @package Hidamari_Care_Asahikawa
 */
?>
<footer class="footer">
	<div class="content-width grid-footer">
		<div>
			<?php get_template_part( 'template-parts/common/brand' ); ?>
			<p class="footer-address">
				<?php esc_html_e( '社会福祉法人 ひだまり福祉計画', 'hidamari-care-asahikawa' ); ?><br>
				<?php esc_html_e( 'ひだまりケア旭川', 'hidamari-care-asahikawa' ); ?><br>
				<?php esc_html_e( '北海道旭川市 旭町2条7丁目 12-77', 'hidamari-care-asahikawa' ); ?><br>
				<?php esc_html_e( 'Tel 0166-xx-yyyy', 'hidamari-care-asahikawa' ); ?>
			</p>
		</div>
		<nav class="footer-nav" aria-label="<?php esc_attr_e( 'フッターナビゲーション', 'hidamari-care-asahikawa' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'footer-nav__list',
					'fallback_cb'    => 'hidamari_care_asahikawa_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>
	</div>
	<p class="copyright">&copy; <?php esc_html_e( '社会福祉法人 ひだまり福祉計画', 'hidamari-care-asahikawa' ); ?> <?php echo esc_html( wp_date( 'Y' ) ); ?></p>
</footer>
<?php wp_footer(); ?>
</body>
</html>

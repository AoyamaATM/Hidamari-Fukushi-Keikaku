<?php
/**
 * Site footer.
 *
 * @package Hidamari_Care_Asahikawa
 */

$organization_name = hidamari_care_asahikawa_setting( 'organization_name', '社会福祉法人 ひだまり福祉計画' );
$facility_name     = hidamari_care_asahikawa_setting( 'facility_name', 'ひだまりケア旭川' );
$address           = hidamari_care_asahikawa_setting( 'address', '北海道旭川市（架空設定）' );
$phone_display     = hidamari_care_asahikawa_setting( 'facility_phone_display', '掲載なし（デモ）' );
?>
<footer class="footer">
	<div class="content-width grid-footer">
		<div>
			<?php get_template_part( 'template-parts/common/brand' ); ?>
			<p class="footer-address">
				<?php echo esc_html( $organization_name ); ?><br>
				<?php echo esc_html( $facility_name ); ?><br>
				<?php echo esc_html( $address ); ?><br>
				<?php echo esc_html( '電話番号　' . $phone_display ); ?>
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
	<p class="copyright">&copy; <?php echo esc_html( $organization_name ); ?> <?php echo esc_html( wp_date( 'Y' ) ); ?></p>
</footer>
<?php wp_footer(); ?>
</body>
</html>

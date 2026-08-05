<?php
/**
 * Site header.
 *
 * @package Hidamari_Care_Asahikawa
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-type="<?php echo esc_attr( hidamari_care_asahikawa_page_type() ); ?>">
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content"><?php esc_html_e( '本文へ移動', 'hidamari-care-asahikawa' ); ?></a>
<header class="site-header">
	<div class="header-inner">
		<?php get_template_part( 'template-parts/common/brand' ); ?>
		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
			<span class="nav-toggle-lines" aria-hidden="true"></span>
			<span class="visually-hidden"><?php esc_html_e( 'メニュー', 'hidamari-care-asahikawa' ); ?></span>
		</button>
		<nav class="site-nav" id="site-nav" aria-label="<?php esc_attr_e( 'グローバルナビゲーション', 'hidamari-care-asahikawa' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'site-nav__list',
					'fallback_cb'    => 'hidamari_care_asahikawa_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>
	</div>
</header>

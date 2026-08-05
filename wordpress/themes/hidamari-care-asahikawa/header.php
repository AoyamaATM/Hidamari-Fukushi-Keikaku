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
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content"><?php esc_html_e( '本文へ移動', 'hidamari-care-asahikawa' ); ?></a>
<header class="site-header">
	<div class="header-inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
		</a>
	</div>
</header>

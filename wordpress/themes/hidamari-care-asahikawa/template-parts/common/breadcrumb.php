<?php
/**
 * Fixed-page breadcrumb.
 *
 * @package Hidamari_Care_Asahikawa
 */

$breadcrumb_label = isset( $args['label'] ) ? (string) $args['label'] : get_the_title();
?>
<nav class="breadcrumb" aria-label="パンくず">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>">TOP</a>
	<span aria-hidden="true">&gt;</span>
	<span><?php echo esc_html( '“' . $breadcrumb_label . '”' ); ?></span>
</nav>

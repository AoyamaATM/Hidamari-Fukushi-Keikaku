<?php
/**
 * Compact post summary used on the TOP page.
 *
 * @package Hidamari_Care_Asahikawa
 */

$summary_post = isset( $args['post'] ) && $args['post'] instanceof WP_Post ? $args['post'] : null;

if ( ! $summary_post ) {
	return;
}

$category = hidamari_care_asahikawa_post_category( $summary_post->ID );
?>
<li class="archive-item">
	<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $summary_post ) ); ?>"><?php echo esc_html( get_the_date( 'Y/m/d', $summary_post ) ); ?></time>
	<span class="<?php echo esc_attr( $category['class'] ); ?>"><?php echo esc_html( $category['name'] ); ?></span>
	<a href="<?php echo esc_url( get_permalink( $summary_post ) ); ?>"><strong><?php echo esc_html( get_the_title( $summary_post ) ); ?></strong></a>
</li>

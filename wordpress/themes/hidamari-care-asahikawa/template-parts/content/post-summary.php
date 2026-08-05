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

$categories    = get_the_category( $summary_post->ID );
$category      = ! empty( $categories ) ? $categories[0] : null;
$category_key  = $category instanceof WP_Term ? $category->slug : 'news';
$category_name = $category instanceof WP_Term ? $category->name : __( 'ニュース', 'hidamari-care-asahikawa' );
$tag_class      = 'blog' === $category_key ? 'tag-blog' : 'tag-news';
?>
<li class="archive-item">
	<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $summary_post ) ); ?>"><?php echo esc_html( get_the_date( 'Y/m/d', $summary_post ) ); ?></time>
	<span class="<?php echo esc_attr( $tag_class ); ?>"><?php echo esc_html( $category_name ); ?></span>
	<a href="<?php echo esc_url( get_permalink( $summary_post ) ); ?>"><strong><?php echo esc_html( get_the_title( $summary_post ) ); ?></strong></a>
</li>

<?php
/**
 * Post summary content.
 *
 * @package Hidamari_Care_Asahikawa
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-summary' ); ?>>
	<header class="entry-header">
		<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
		<time class="entry-date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
	</header>
	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div>
</article>

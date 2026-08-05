<?php
/**
 * Single post content.
 *
 * @package Hidamari_Care_Asahikawa
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		<time class="entry-date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
	</header>
	<div class="entry-content">
		<?php the_content(); ?>
	</div>
</article>

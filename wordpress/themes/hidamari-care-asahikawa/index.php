<?php
/**
 * Fallback template.
 *
 * @package Hidamari_Care_Asahikawa
 */

get_header();
?>
<div class="skip-target" id="main-content" tabindex="-1"></div>
<main class="page-shell">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<?php get_template_part( 'template-parts/content/content', 'summary' ); ?>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
	<?php endif; ?>
</main>
<?php
get_footer();

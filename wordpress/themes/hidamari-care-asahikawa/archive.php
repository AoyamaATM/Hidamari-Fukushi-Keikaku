<?php
/**
 * Archive template.
 *
 * @package Hidamari_Care_Asahikawa
 */

get_header();
?>
<main id="main-content" class="page-shell">
	<header class="page-header">
		<?php the_archive_title( '<h1>', '</h1>' ); ?>
		<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
	</header>
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

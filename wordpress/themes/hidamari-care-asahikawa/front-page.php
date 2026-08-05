<?php
/**
 * Front page template.
 *
 * @package Hidamari_Care_Asahikawa
 */

get_header();
?>
<div class="skip-target" id="main-content" tabindex="-1"></div>
<main class="page-shell">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<?php get_template_part( 'template-parts/content/content', 'page' ); ?>
	<?php endwhile; ?>
</main>
<?php
get_footer();

<?php
/**
 * Posts page template.
 *
 * @package Hidamari_Care_Asahikawa
 */

get_header();
?>
<div class="skip-target" id="main-content" tabindex="-1"></div>
<main class="page-shell">
	<header class="page-header">
		<h1><?php esc_html_e( 'お知らせ', 'hidamari-care-asahikawa' ); ?></h1>
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

<?php
/**
 * Single post template.
 *
 * @package Hidamari_Care_Asahikawa
 */

get_header();
?>
<main class="page-shell">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<nav class="breadcrumb breadcrumb--plain" aria-label="パンくず">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'TOP', 'hidamari-care-asahikawa' ); ?></a>
			<span>&gt;</span>
			<a href="<?php echo esc_url( hidamari_care_asahikawa_posts_url() ); ?>"><?php esc_html_e( 'お知らせ', 'hidamari-care-asahikawa' ); ?></a>
			<span>&gt;</span>
			<span><?php echo esc_html( '“' . get_the_title() . '”' ); ?></span>
		</nav>

		<div class="skip-target" id="main-content" tabindex="-1"></div>

		<div class="content-width post-grid">
		<?php get_template_part( 'template-parts/content/content', 'single' ); ?>
			<?php get_template_part( 'template-parts/common/post', 'sidebar' ); ?>
		</div>
	<?php endwhile; ?>
</main>
<?php
get_footer();

<?php
/**
 * Privacy policy page template.
 *
 * @package Hidamari_Care_Asahikawa
 */

get_header();
?>
<main class="page-shell" id="main">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<?php
		get_template_part(
			'template-parts/common/breadcrumb',
			null,
			array(
				'label' => 'プライバシーポリシー',
				'plain' => true,
			)
		);
		?>

		<div class="skip-target" id="main-content" tabindex="-1"></div>

		<article class="content-width policy">
			<?php the_title( '<h1 class="section-heading__title">', '</h1>' ); ?>
			<?php the_content(); ?>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();

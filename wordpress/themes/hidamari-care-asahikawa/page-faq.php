<?php
/**
 * FAQ page template.
 *
 * @package Hidamari_Care_Asahikawa
 */

$page_id = get_queried_object_id();

$faq_groups = array(
	array(
		'section_id' => 'payment',
		'term_slug'  => 'payment',
		'heading'    => '費用・お支払いについて',
		'image_key'  => 'faq_payment_anchor',
		'image_alt'  => '費用・お支払い',
		'notes'      => array( '利用料金はどれくらい？', '支払方法は？' ),
	),
	array(
		'section_id' => 'day-care',
		'term_slug'  => 'day-care',
		'heading'    => '訪問介護・生活援助について',
		'image_key'  => 'faq_day_care_anchor',
		'image_alt'  => '訪問介護・生活援助',
		'notes'      => array( '具体的には何するの？', 'アレルギー食・刻み食', '同居してても大丈夫？' ),
	),
	array(
		'section_id' => 'consultation',
		'term_slug'  => 'consultation',
		'heading'    => 'ご相談・居宅介護支援について',
		'image_key'  => 'faq_consultation_anchor',
		'image_alt'  => 'ご相談・居宅介護支援',
		'notes'      => array( '見学相談だけでもいい？', '介護保険って？', 'ケアマネジャーって？' ),
	),
	array(
		'section_id' => 'facility-life',
		'term_slug'  => 'daily-life',
		'heading'    => '施設での生活について',
		'image_key'  => 'faq_facility_anchor',
		'image_alt'  => '施設での生活',
		'notes'      => array( 'どんな人が利用してる？', '送迎はある？', '急な体調不良の時は？' ),
	),
);

foreach ( $faq_groups as $group_index => $faq_group ) {
	$faq_groups[ $group_index ]['posts'] = get_posts(
		array(
			'post_type'      => 'hidamari_faq',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'tax_query'      => array(
				array(
					'taxonomy' => 'hidamari_faq_cat',
					'field'    => 'slug',
					'terms'    => $faq_group['term_slug'],
				),
			),
		)
	);
}

get_header();
?>
<main class="page-shell" id="main">
	<?php get_template_part( 'template-parts/common/subpage-hero', null, array( 'page_id' => $page_id, 'title' => 'よくあるご質問' ) ); ?>
	<?php get_template_part( 'template-parts/common/breadcrumb', null, array( 'label' => 'よくあるご質問' ) ); ?>

	<section class="section anchor-section">
		<div class="content-width">
			<div class="category-cards">
				<?php foreach ( $faq_groups as $faq_group ) : ?>
					<div>
						<a class="faq-category-card" href="#<?php echo esc_attr( $faq_group['section_id'] ); ?>">
							<?php
							$category_image = hidamari_care_asahikawa_page_image(
								$page_id,
								$faq_group['image_key'],
								'full',
								array(
									'loading'  => 'lazy',
									'decoding' => 'async',
								)
							);
							echo '' !== $category_image ? $category_image : esc_html( $faq_group['image_alt'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</a>
						<ul class="faq-category-card__notes">
							<?php foreach ( $faq_group['notes'] as $note ) : ?>
								<li><?php echo esc_html( $note ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="skip-target" id="main-content" tabindex="-1"></div>

			<?php foreach ( $faq_groups as $faq_group ) : ?>
				<section class="faq-group" id="<?php echo esc_attr( $faq_group['section_id'] ); ?>">
					<h2 class="section-heading"><?php echo esc_html( $faq_group['heading'] ); ?></h2>
					<div class="faq-stack">
						<?php if ( empty( $faq_group['posts'] ) ) : ?>
							<p><?php esc_html_e( '質問は準備中です。', 'hidamari-care-asahikawa' ); ?></p>
						<?php else : ?>
							<?php foreach ( $faq_group['posts'] as $faq_index => $faq_post ) : ?>
								<?php
								get_template_part(
									'template-parts/content/faq-item',
									null,
									array(
										'id'       => sprintf( 'faq-%s-%d', $faq_group['section_id'], $faq_index + 1 ),
										'question' => $faq_post->post_title,
										'answer'   => $faq_post->post_content,
									)
								);
								?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</section>
			<?php endforeach; ?>
		</div>
	</section>
</main>
<?php
get_footer();

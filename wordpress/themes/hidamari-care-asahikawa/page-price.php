<?php
/**
 * Price page template.
 *
 * @package Hidamari_Care_Asahikawa
 */

$page_id = get_queried_object_id();

$price_groups = array(
	'day-basic'        => array(
		'caption' => '基本料金',
		'headers' => array( '区分', '基本単位数', '1割負担（目安）', '2割負担（目安）' ),
	),
	'day-addition'     => array(
		'caption' => '主な加算料金',
		'classes' => array( 'table-price--content' ),
		'content' => true,
		'headers' => array( '区分', '単位数', '内容' ),
	),
	'day-outside'      => array(
		'caption' => '介護保険外料金',
		'classes' => array( 'table-price--content', 'table-price--fee' ),
		'content' => true,
		'headers' => array( '区分', '料金', '内容' ),
	),
	'visit-physical'   => array(
		'caption' => '身体介護',
		'note'    => '入浴・排泄・食事介助 など（要介護1〜5の方）',
		'headers' => array( '区分', '基本単位数', '1割負担（目安）', '2割負担（目安）' ),
	),
	'visit-housework'  => array(
		'caption' => '生活援助',
		'note'    => '掃除・洗濯・調理 など（要介護1〜5の方）',
		'headers' => array( '区分', '基本単位数', '1割負担（目安）', '2割負担（目安）' ),
	),
	'visit-prevention' => array(
		'caption' => '介護予防訪問サービス',
		'note'    => '要支援1・2の方',
		'classes' => array( 'table-price--frequency' ),
		'headers' => array( '区分', '回数', '1割負担（目安）', '2割負担（目安）' ),
	),
	'visit-addition'   => array(
		'caption' => '主な加算料金',
		'classes' => array( 'table-price--content' ),
		'content' => true,
		'headers' => array( '区分', '料金', '内容' ),
	),
	'visit-outside'    => array(
		'caption' => '介護保険外料金',
		'classes' => array( 'table-price--content', 'table-price--fee' ),
		'content' => true,
		'headers' => array( '区分', '回数', '内容' ),
	),
);

$price_rows_by_group = array_fill_keys( array_keys( $price_groups ), array() );
$price_rows          = get_posts(
	array(
		'post_type'      => 'hidamari_price',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
	)
);

foreach ( $price_rows as $price_row ) {
	$group_key = (string) get_post_meta( $price_row->ID, 'hidamari_price_group', true );
	if ( isset( $price_rows_by_group[ $group_key ] ) ) {
		$price_rows_by_group[ $group_key ][] = $price_row;
	}
}

get_header();
?>
<main class="page-shell" id="main">
	<?php get_template_part( 'template-parts/common/subpage-hero', null, array( 'page_id' => $page_id, 'title' => '料金表' ) ); ?>
	<?php get_template_part( 'template-parts/common/breadcrumb', null, array( 'label' => '料金表' ) ); ?>

	<section class="section anchor-section">
		<div class="content-width">
			<div class="price-links">
				<a class="asset-link price-link-card" href="#day-service">
					<?php
					$day_anchor = hidamari_care_asahikawa_page_image(
						$page_id,
						'price_day_anchor',
						'full',
						array(
							'loading'  => 'lazy',
							'decoding' => 'async',
						)
					);
					echo '' !== $day_anchor ? $day_anchor : esc_html__( 'デイサービス料金表', 'hidamari-care-asahikawa' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</a>
				<a class="asset-link price-link-card" href="#visit-service">
					<?php
					$visit_anchor = hidamari_care_asahikawa_page_image(
						$page_id,
						'price_visit_anchor',
						'full',
						array(
							'loading'  => 'lazy',
							'decoding' => 'async',
						)
					);
					echo '' !== $visit_anchor ? $visit_anchor : esc_html__( '訪問介護料金表', 'hidamari-care-asahikawa' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</a>
			</div>
		</div>
	</section>

	<div class="skip-target" id="main-content" tabindex="-1"></div>

	<section class="section" id="day-service">
		<div class="content-width table-stack">
			<div>
				<h2 class="section-heading center-heading">デイサービス 料金表</h2>
				<p>
					利用料金は、介護保険の利用者負担割合によって変動します。<br>
					その他、ご利用に応じて下記の利用料をお支払いいただきます。
				</p>
			</div>
			<?php foreach ( array( 'day-basic', 'day-addition', 'day-outside' ) as $group_key ) : ?>
				<?php get_template_part( 'template-parts/content/price-table', null, array( 'config' => $price_groups[ $group_key ], 'rows' => $price_rows_by_group[ $group_key ] ) ); ?>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="section" id="visit-service">
		<div class="content-width table-stack">
			<h2 class="section-heading center-heading">訪問介護 料金表</h2>
			<?php foreach ( array( 'visit-physical', 'visit-housework', 'visit-prevention', 'visit-addition', 'visit-outside' ) as $group_key ) : ?>
				<?php get_template_part( 'template-parts/content/price-table', null, array( 'config' => $price_groups[ $group_key ], 'rows' => $price_rows_by_group[ $group_key ] ) ); ?>
			<?php endforeach; ?>
		</div>
	</section>
</main>
<?php
get_footer();

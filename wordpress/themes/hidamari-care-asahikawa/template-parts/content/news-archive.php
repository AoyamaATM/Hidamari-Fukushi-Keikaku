<?php
/**
 * Posts, category, and monthly archive layout.
 *
 * @package Hidamari_Care_Asahikawa
 */

global $wpdb, $wp_query;

$posts_url        = hidamari_care_asahikawa_posts_url();
$post_counts      = wp_count_posts( 'post' );
$published_count  = isset( $post_counts->publish ) ? (int) $post_counts->publish : 0;
$current_category = is_category() ? get_queried_object() : null;
$current_year     = is_month() ? (int) get_query_var( 'year' ) : 0;
$current_month    = is_month() ? (int) get_query_var( 'monthnum' ) : 0;
$archive_label    = '';

if ( $current_category instanceof WP_Term ) {
	$archive_label = $current_category->name;
} elseif ( $current_year > 0 && $current_month > 0 ) {
	$archive_label = sprintf( '%1$d年%2$d月', $current_year, $current_month );
}

$categories = get_categories(
	array(
		'hide_empty' => true,
		'orderby'    => 'term_id',
		'order'      => 'ASC',
	)
);

$month_query = $wpdb->prepare(
	"SELECT YEAR(post_date) AS archive_year, MONTH(post_date) AS archive_month, COUNT(ID) AS post_count
	FROM {$wpdb->posts}
	WHERE post_type = %s AND post_status = %s
	GROUP BY YEAR(post_date), MONTH(post_date)
	ORDER BY post_date DESC",
	'post',
	'publish'
);
$months = $wpdb->get_results( $month_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

$found_posts  = (int) $wp_query->found_posts;
$per_page     = max( 1, (int) $wp_query->get( 'posts_per_page' ) );
$current_page = max( 1, (int) get_query_var( 'paged' ) );
$first_post    = $found_posts > 0 ? ( ( $current_page - 1 ) * $per_page ) + 1 : 0;
$last_post     = min( $current_page * $per_page, $found_posts );
?>
<main class="page-shell" id="main">
	<nav class="breadcrumb breadcrumb--plain" aria-label="パンくず">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'TOP', 'hidamari-care-asahikawa' ); ?></a>
		<span>&gt;</span>
		<?php if ( '' !== $archive_label ) : ?>
			<a href="<?php echo esc_url( $posts_url ); ?>"><?php esc_html_e( 'お知らせ一覧', 'hidamari-care-asahikawa' ); ?></a>
			<span>&gt;</span>
			<span><?php echo esc_html( $archive_label ); ?></span>
		<?php else : ?>
			<span><?php esc_html_e( '“お知らせ一覧”', 'hidamari-care-asahikawa' ); ?></span>
		<?php endif; ?>
	</nav>

	<div class="skip-target" id="main-content" tabindex="-1"></div>

	<section class="content-width archive-shell">
		<h1 class="section-heading center-heading">
			<?php echo esc_html( '' !== $archive_label ? $archive_label . 'の記事一覧' : __( 'お知らせ一覧', 'hidamari-care-asahikawa' ) ); ?>
		</h1>
		<p class="sample-content-note"><?php esc_html_e( '掲載しているお知らせ・ブログは、ポートフォリオ用に作成したサンプル記事です。', 'hidamari-care-asahikawa' ); ?></p>
		<div class="filter-bar" aria-label="お知らせの絞り込み">
			<label class="archive-select-field">
				<span><?php esc_html_e( 'カテゴリ：', 'hidamari-care-asahikawa' ); ?></span>
				<select data-archive-navigation>
					<option value="<?php echo esc_url( $posts_url ); ?>"><?php echo esc_html( sprintf( 'すべて（%d）', $published_count ) ); ?></option>
					<?php foreach ( $categories as $category ) : ?>
						<option value="<?php echo esc_url( get_category_link( $category ) ); ?>"<?php selected( $current_category instanceof WP_Term ? $current_category->term_id : 0, $category->term_id ); ?>><?php echo esc_html( sprintf( '%1$s（%2$d）', $category->name, $category->count ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="archive-select-field">
				<span><?php esc_html_e( '月別：', 'hidamari-care-asahikawa' ); ?></span>
				<select data-archive-navigation>
					<option value="<?php echo esc_url( $posts_url ); ?>"><?php echo esc_html( sprintf( '全期間（%d）', $published_count ) ); ?></option>
					<?php foreach ( $months as $month ) : ?>
						<?php $is_current_month = $current_year === (int) $month->archive_year && $current_month === (int) $month->archive_month; ?>
						<option value="<?php echo esc_url( get_month_link( (int) $month->archive_year, (int) $month->archive_month ) ); ?>"<?php selected( $is_current_month ); ?>><?php echo esc_html( sprintf( '%1$d年%2$d月（%3$d）', $month->archive_year, $month->archive_month, $month->post_count ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
		<p class="archive-result" aria-live="polite"><?php echo esc_html( sprintf( '%1$d件中%2$d〜%3$d件を表示しています', $found_posts, $first_post, $last_post ) ); ?></p>

		<?php if ( have_posts() ) : ?>
			<ul class="list-archive">
				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<?php get_template_part( 'template-parts/content/post', 'summary', array( 'post' => get_post() ) ); ?>
				<?php endwhile; ?>
			</ul>
		<?php else : ?>
			<p class="archive-empty"><?php esc_html_e( '該当するお知らせはありません。', 'hidamari-care-asahikawa' ); ?></p>
		<?php endif; ?>

		<?php
		$pagination = paginate_links(
			array(
				'total'     => (int) $wp_query->max_num_pages,
				'current'   => $current_page,
				'mid_size'  => 1,
				'prev_text' => '&lt;',
				'next_text' => '&gt;',
				'type'      => 'array',
			)
		);
		?>
		<?php if ( ! empty( $pagination ) ) : ?>
			<nav class="pagination" aria-label="ページネーション">
				<?php foreach ( $pagination as $page_link ) : ?>
					<?php echo wp_kses_post( $page_link ); ?>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</section>
</main>

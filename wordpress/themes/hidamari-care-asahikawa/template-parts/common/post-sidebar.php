<?php
/**
 * Sidebar for a single standard post.
 *
 * @package Hidamari_Care_Asahikawa
 */

$recent_posts = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
?>
<aside class="sidebar">
	<div class="sidebar-banners">
		<a class="sidebar-banner" href="<?php echo esc_url( hidamari_care_asahikawa_page_url( 'about-us' ) . '#flow__about' ); ?>">
			<img src="<?php echo esc_url( hidamari_care_asahikawa_asset_uri( 'img/sidebar02.webp' ) ); ?>" alt="<?php esc_attr_e( 'ご利用までの流れ', 'hidamari-care-asahikawa' ); ?>" width="400" height="225" loading="lazy" decoding="async">
		</a>
		<a class="sidebar-banner" href="<?php echo esc_url( hidamari_care_asahikawa_page_url( 'faq' ) ); ?>">
			<img src="<?php echo esc_url( hidamari_care_asahikawa_asset_uri( 'img/sidebar01.webp' ) ); ?>" alt="<?php esc_attr_e( 'よくあるご質問', 'hidamari-care-asahikawa' ); ?>" width="400" height="225" loading="lazy" decoding="async">
		</a>
	</div>
	<?php if ( $recent_posts->have_posts() ) : ?>
		<section>
			<h2 class="sidebar-title"><?php esc_html_e( '最新の投稿', 'hidamari-care-asahikawa' ); ?></h2>
			<ul class="list-compact">
				<?php while ( $recent_posts->have_posts() ) : ?>
					<?php
					$recent_posts->the_post();
					$category = hidamari_care_asahikawa_post_category( get_the_ID() );
					?>
					<li>
						<a href="<?php the_permalink(); ?>">
							<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'Y/m/d' ) ); ?></time>
							<span class="<?php echo esc_attr( $category['class'] ); ?>"><?php echo esc_html( $category['name'] ); ?></span>
							<strong><?php the_title(); ?></strong>
						</a>
					</li>
				<?php endwhile; ?>
			</ul>
		</section>
	<?php endif; ?>
</aside>
<?php wp_reset_postdata(); ?>

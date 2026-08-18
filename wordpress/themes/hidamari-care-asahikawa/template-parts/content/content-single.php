<?php
/**
 * Single post content.
 *
 * @package Hidamari_Care_Asahikawa
 */
$category = hidamari_care_asahikawa_post_category( get_the_ID() );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-article' ); ?>>
	<h1><?php the_title(); ?></h1>
	<div class="post-meta">
		<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'Y/m/d' ) ); ?></time>
		<span class="<?php echo esc_attr( $category['class'] ); ?>"><?php echo esc_html( $category['name'] ); ?></span>
	</div>
	<div class="post-body">
		<?php the_content(); ?>
	</div>
	<p class="post-back"><a href="<?php echo esc_url( hidamari_care_asahikawa_posts_url() ); ?>">&gt; <?php esc_html_e( 'お知らせ一覧', 'hidamari-care-asahikawa' ); ?></a></p>
</article>

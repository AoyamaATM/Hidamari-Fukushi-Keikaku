<?php
/**
 * Fixed-page hero with optional dedicated mobile image.
 *
 * @package Hidamari_Care_Asahikawa
 */

$hero_page_id   = isset( $args['page_id'] ) ? (int) $args['page_id'] : get_queried_object_id();
$hero_title     = isset( $args['title'] ) ? (string) $args['title'] : get_the_title( $hero_page_id );
$hero_desktop   = get_post_thumbnail_id( $hero_page_id );
$hero_mobile    = (int) get_post_meta( $hero_page_id, 'hidamari_hero_mobile_id', true );
$hero_mobile_src = $hero_mobile > 0 ? wp_get_attachment_image_src( $hero_mobile, 'full' ) : false;
?>
<section class="subpage-hero">
	<h1 class="visually-hidden"><?php echo esc_html( $hero_title ); ?></h1>
	<?php if ( $hero_desktop > 0 ) : ?>
		<picture>
			<?php if ( is_array( $hero_mobile_src ) ) : ?>
				<source media="(max-width: 768px)" srcset="<?php echo esc_url( $hero_mobile_src[0] ); ?>" width="<?php echo esc_attr( $hero_mobile_src[1] ); ?>" height="<?php echo esc_attr( $hero_mobile_src[2] ); ?>">
			<?php endif; ?>
			<?php
			echo wp_get_attachment_image(
				$hero_desktop,
				'hidamari-hero',
				false,
				array(
					'alt'           => '',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'decoding'      => 'async',
				)
			); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</picture>
	<?php endif; ?>
</section>

<?php
/**
 * Render one fixed-structure price table with managed rows.
 *
 * @package Hidamari_Care_Asahikawa
 */

$price_config = isset( $args['config'] ) && is_array( $args['config'] ) ? $args['config'] : array();
$price_rows   = isset( $args['rows'] ) && is_array( $args['rows'] ) ? $args['rows'] : array();
$caption      = isset( $price_config['caption'] ) ? (string) $price_config['caption'] : '';
$caption_note = isset( $price_config['note'] ) ? (string) $price_config['note'] : '';
$headers      = isset( $price_config['headers'] ) && is_array( $price_config['headers'] ) ? $price_config['headers'] : array();
$is_content   = ! empty( $price_config['content'] );
$table_classes = array( 'table-price' );

if ( isset( $price_config['classes'] ) && is_array( $price_config['classes'] ) ) {
	$table_classes = array_merge( $table_classes, $price_config['classes'] );
}
?>
<div class="table-wrap">
	<table class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $table_classes ) ) ); ?>">
		<caption>
			<span class="price-caption-title"><?php echo esc_html( $caption ); ?></span>
			<?php if ( '' !== $caption_note ) : ?>
				<span class="price-caption-note"><?php echo esc_html( $caption_note ); ?></span>
			<?php endif; ?>
		</caption>
		<colgroup>
			<col class="price-col-category">
			<col class="price-col-unit">
			<col class="price-col-share">
			<col class="price-col-share">
		</colgroup>
		<thead>
			<tr>
				<?php foreach ( $headers as $header_index => $header ) : ?>
					<th scope="col"<?php echo $is_content && 2 === $header_index ? ' colspan="2"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $header ); ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $price_rows ) ) : ?>
				<tr><td colspan="4"><?php esc_html_e( '料金情報は準備中です。', 'hidamari-care-asahikawa' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $price_rows as $price_row ) : ?>
					<?php
					$row_type = (string) get_post_meta( $price_row->ID, 'hidamari_price_row_type', true );
					$row_type = in_array( $row_type, array( 'normal', 'description', 'note' ), true ) ? $row_type : 'normal';
					$cells     = array();
					for ( $cell_number = 1; $cell_number <= 4; $cell_number++ ) {
						$cells[] = (string) get_post_meta( $price_row->ID, 'hidamari_price_cell_' . $cell_number, true );
					}
					?>
					<?php if ( 'normal' !== $row_type ) : ?>
						<tr class="price-row price-row--<?php echo esc_attr( $row_type ); ?>">
							<td colspan="4"><?php echo esc_html( implode( ' ', array_filter( $cells ) ) ); ?></td>
						</tr>
					<?php else : ?>
						<tr>
							<?php foreach ( array_slice( $cells, 0, $is_content ? 3 : 4 ) as $cell_index => $cell ) : ?>
								<td<?php echo $is_content && 2 === $cell_index ? ' colspan="2"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $cell ); ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>

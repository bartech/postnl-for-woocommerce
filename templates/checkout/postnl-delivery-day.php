<?php
/**
 * Template for delivery day file.
 *
 * @package PostNLWooCommerce\Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $fields ) ) {
	return;
}

foreach ( $fields as $field ) {
	?>
	<tr class="dhl-co-tr dhl-co-tr-fist">
		<td colspan="2">
		<?php
			woocommerce_form_field( $field['id'], $field, $field['value'] );
		?>
		</td>
	</tr>
	<?php
}
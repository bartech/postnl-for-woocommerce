/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';

export const options = [
	{
		label: __( 'Regular Address', 'shipping-workshop' ),
		value: 'normal',
	},
	{
		label: __( 'DHL Packstation', 'shipping-workshop' ),
		value: 'dhl_packstation',
	},
	{
		label: __( 'DHL Branch', 'shipping-workshop' ),
		value: 'dhl_branch',
	},
	/**
	 * [frontend-step-01]
	 * 📝 Add more options using the same format as above. Ensure one option has the key "other".
	 */
];

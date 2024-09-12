/**
 * External dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

const render = () => {};

registerPlugin( 'postnl-for-woocommerce', {
	render,
	scope: 'woocommerce-checkout',
} );

<?php
/**
 * Store_API_Extension class.
 *
 * A class to extend the store public API with Multiple Addresses shipping functionality.
 *
 * @package postnl-for-woocommerce
 */

namespace PostNLWooCommerce\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use PostNLWooCommerce\Frontend\Container;
use PostNLWooCommerce\Frontend\Delivery_Day;
use WC_Postnl_Tabs;

/**
 * Store API Extension.
 */
class Store_API_Extension {
	/**
	 * Global WC_Postnl_Tabs variable.
	 *
	 * @var WC_Postnl_Tabs
	 */
	private static WC_Postnl_Tabs $wcpt;

	/**
	 * Plugin Identifier, unique to each plugin.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'wc_postnl_tabs';

	/**
	 * Bootstraps the class and hooks required data.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		//self::$wcpt = $GLOBALS['wcpt'];
		self::extend_store();
	}

	/**
	 * Registers the data into each endpoint.
	 */
	public static function extend_store() {
		$logger              = wc_get_logger();
		$update_callback_reg = woocommerce_store_api_register_update_callback(
			array(
				'namespace' => self::IDENTIFIER,
				'callback'  => function( $data ) {
					self::update_shipping_notes( $data );
				},
			)
		);

		if ( is_wp_error( $update_callback_reg ) ) {
			$logger->error( $update_callback_reg->get_error_message() );
			return;
		}

		$endpoint_data_reg = woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => CartSchema::IDENTIFIER,
				'namespace'       => self::IDENTIFIER,
				'data_callback'   => array( static::class, 'data_callback' ),
				'schema_callback' => array( static::class, 'schema_callback' ),
				'schema_type'     => ARRAY_A,
			)
		);

		if ( is_wp_error( $endpoint_data_reg ) ) {
			$logger->error( $endpoint_data_reg->get_error_message() );
			return;
		}
	}

	/**
	 * Update multiple shipping notes an delivery dates.
	 *
	 * @param array $post_data Shipping notes data from POST.
	 */
	public static function update_shipping_notes( $post_data ) {
		if ( ! empty( $post_data['notes'] ) && is_array( $post_data['notes'] ) ) {
			wcms_session_set( 'wcms_package_notes', $post_data['notes'] );
		}

		if ( ! empty( $post_data['dates'] ) && is_array( $post_data['dates'] ) ) {
			wcms_session_set( 'wcms_delivery_dates', $post_data['dates'] );
		}

		if ( ! empty( $post_data['gifts'] ) && is_array( $post_data['gifts'] ) ) {
			wcms_session_set( 'wcms_package_gifts', $post_data['gifts'] );
		}
	}

	/**
	 * Store API extension data callback.
	 *
	 * @return array
	 */
	public static function data_callback() {
		$packages          = WC()->cart->get_shipping_packages();
		$shipping_packages = WC()->shipping->get_packages();

		foreach ( $shipping_packages as $index => $package ) {
			if ( ! isset( $packages[ $index ] ) ) {
				continue;
			}

			$packages[ $index ]['rates'] = $package['rates'];
		}

		// $data = ( self::$wcpt->cart->cart_is_eligible_for_multi_shipping() ) ? self::prepare_multi_shipping_data( $packages ) : array();

		return array(
			'postnl_tabs' 		  => self::prepare_postnl_tabs(),
		);
	}

	/**
	 * Store API extension schema callback.
	 *
	 * @return array Registered schema.
	 */
	public static function schema_callback() {
		return array(
			'postnl_tabs' => array(
				'description' => __( 'Postnl tabs', 'wc_shipping_multiple_address' ),
				'type'        => array( 'array' ),
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
		);
	}

	/**
	 * Prepare the postnl tabs data.
	 */
	public static function prepare_postnl_tabs() {
		$ms_settings = get_option( 'woocommerce_multiple_shipping_settings', array() );

		$delivery_day 	= new Delivery_Day();
		$container		= new Container();
		$fields 		= $delivery_day->get_fields();
		$field 			= array_shift( $fields );

		return array(
			// 'data'       	=> $data,
				'field'     	=> $field,
				'tabs'			=> $delivery_day->add_checkout_tab([],[]),
				'is_enabled' 	=> $delivery_day->is_enabled(),
				// 'data'       	=> '',
				// 'is_enabled' 	=> '',
				'static_var' 	=> $container->get_default_value([],[]),
		);
	}

	


}

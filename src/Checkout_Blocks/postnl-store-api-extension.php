<?php
/**
 * Store_API_Extension class.
 *
 * A class to extend the store public API with Multiple Addresses shipping functionality.
 *
 * @package WC_Postnl_Tabs
 */

namespace PostNLWooCommerce\Checkout_Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use PostNLWooCommerce\Utils;

use WC_Ship_Multiple;
use PostNLWooCommerce\Frontend\Delivery_Day;


/**
 * Postnl Store API Extension.
 */
class Postnl_Store_API_Extension {
	/**
	 * Stores Rest Extending instance.
	 *
	 * @var ExtendRestApi
	 */
	private static $extend;

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
		self::$extend = \Automattic\WooCommerce\StoreApi\StoreApi::container()->get( \Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema::class );
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
				'endpoint'        => CheckoutSchema::IDENTIFIER,
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

		$data = array();
		error_log('test'. 'test');

		$delivery_day = new Delivery_Day();
		return array(
			'wc_postnl_tabs' => array(
				// 'data'       	=> $data,
				// 'fields'     	=> $delivery_day->get_fields(),
				// 'is_enabled' 	=> $delivery_day->is_enabled(),
				// 'static_var' 	=> self::prepare_settings_static_variable(),
				'data'       	=> '',
				'fields'     	=> '',
				'is_enabled' 	=> '',
				'static_var' 	=> '',
			),
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
				'description' => __( 'MS info', 'postnl-for-woocommerce' ),
				'type'        => array( 'array' ),
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
		);
	}

	

	/**
	 * Formatting the shipping method label.
	 *
	 * @param object $method Shipping Method object.
	 */
	public static function get_formatted_shipping_method_label( $method ) {
		$label = esc_html( $method->label );

		if ( $method->cost <= 0 ) {
			return $label;
		}

		$shipping_tax = $method->get_shipping_tax();
		$label       .= ' &mdash; ';

		// Append price to label using the correct tax settings.
		if ( WC()->cart->display_totals_ex_tax || ! WC()->cart->prices_include_tax ) {
			$label .= wc_price( $method->cost );
			if ( $shipping_tax > 0 && WC()->cart->prices_include_tax ) {
				$label .= ' ' . WC()->countries->ex_tax_or_vat();
			}

			return $label;
		}

		$label .= wc_price( $method->cost + $shipping_tax );
		if ( $shipping_tax > 0 && ! WC()->cart->prices_include_tax ) {
			$label .= ' ' . WC()->countries->inc_tax_or_vat();
		}

		return $label;
	}

	/**
	 * Get shipping rates for the multiple address.
	 *
	 * @param array $package Current package.
	 * @param array $packages the whole cart packages.
	 *
	 * @return array
	 */
	public static function get_multi_addr_shipping_rates( $package, $packages ) {
		$shipping_rates = array();

		$sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );
		if ( isset( $sess_cart_addresses ) && ! empty( $sess_cart_addresses ) ) {
			// Always allow users to select shipping.
			foreach ( $package['rates'] as $rate ) {
				$rate->label                 = self::get_formatted_shipping_method_label( $rate );
				$shipping_rates[ $rate->id ] = $rate;
				$shipping_rates[ $rate->id ] = array(
					'option_label' => wp_strip_all_tags( $rate->label ),
					'option_value' => esc_attr( $rate->id ),
				);
			}
		} elseif ( self::$wcms->packages_have_different_origins( $packages ) || self::$wcms->packages_have_different_methods( $packages ) || self::$wcms->packages_contain_methods( $packages ) ) {

			$type = ( self::$wcms->packages_have_different_origins( $packages ) || self::$wcms->packages_have_different_methods( $packages ) ) ? 1 : 2;

			// Show shipping methods available to each package.
			foreach ( WC()->shipping->shipping_methods as $shipping_method ) {

				if ( isset( $package['method'] ) && ! in_array( $shipping_method->id, $package['method'], true ) ) {
					continue;
				}

				if ( ! $shipping_method->is_available( $package ) ) {
					continue;
				}

				// Reset Rates.
				$shipping_method->rates = array();

				// Calculate Shipping for package.
				$shipping_method->calculate_shipping( $package );

				// Place rates in package array.
				if ( empty( $shipping_method->rates ) || ! is_array( $shipping_method->rates ) ) {
					continue;
				}

				foreach ( $shipping_method->rates as $rate ) {
					$rate->label  = self::get_formatted_shipping_method_label( $rate );
					$option_label = ( 1 === $type ) ? wp_kses_post( wc_cart_totals_shipping_method_label( $rate ) ) : esc_html( $rate->label );
					$option_value = ( 1 === $type ) ? esc_attr( $rate->id ) . '||' . wp_strip_all_tags( $rate->label ) : esc_attr( $rate->id );

					$shipping_rates[ $rate->id ] = array(
						'option_label' => $option_label,
						'option_value' => $option_value,
					);
				}
			}
		}

		return $shipping_rates;
	}

	/**
	 * Prepare variables from the settings and static text or urls.
	 */
	public static function prepare_settings_static_variable() {
		$id                = wc_get_page_id( 'multiple_addresses' );
		$reset_url         = add_query_arg(
			array(
				'wcms_reset_address' => true,
				'nonce'              => wp_create_nonce( 'wcms_reset_address_security' ),
			),
			wc_get_checkout_url()
		);
		$modify_addr_link  = get_permalink( $id );
		$add_addr_link     = add_query_arg( 'cart', 1, get_permalink( $id ) );
		// $has_multi_address = ( self::$wcms->cart->cart_has_multi_shipping() && WC()->cart->needs_shipping() );
		// $note_limit        = ! empty( self::$wcms->gateway_settings['checkout_notes_limit'] ) ? absint( self::$wcms->gateway_settings['checkout_notes_limit'] ) : '';
		// $show_notes        = ( ! empty( self::$wcms->gateway_settings['checkout_notes'] ) && 'yes' === self::$wcms->gateway_settings['checkout_notes'] ) ? true : false;
		// $show_datepicker   = ( ! empty( self::$wcms->gateway_settings['checkout_datepicker'] ) && 'yes' === self::$wcms->gateway_settings['checkout_datepicker'] ) ? true : false;
		// $valid_dates       = ( ! empty( self::$wcms->gateway_settings['checkout_valid_days'] ) ) ? self::$wcms->gateway_settings['checkout_valid_days'] : array();
		// $excluded_dates    = ( ! empty( self::$wcms->gateway_settings['checkout_exclude_dates'] ) ) ? self::$wcms->gateway_settings['checkout_exclude_dates'] : array();
		$show_gifts        = \WC_MS_Gifts::is_enabled();
		$lang_notification = \WC_Ship_Multiple::$lang['notification'];
		$lang_button       = \WC_Ship_Multiple::$lang['btn_items'];

		return array(
			// 'is_eligible_wcms'  => self::$wcms->cart->cart_is_eligible_for_multi_shipping(),
			// 'has_multi_address' => $has_multi_address,
			'reset_url'         => $reset_url,
			'modify_addr_link'  => $modify_addr_link,
			'add_addr_link'     => $add_addr_link,
			'modify_addr_text'  => esc_html__( 'Modify/Add Address', 'wc_shipping_multiple_address' ),
			'reset_addr_text'   => esc_html__( 'Reset Address', 'wc_shipping_multiple_address' ),
			'lang_notification' => esc_html( $lang_notification ),
			'lang_button'       => esc_attr( $lang_button ),
			// 'show_notes'        => $show_notes,
			'note_label_text'   => esc_html( 'Note:', 'wc_shipping_multiple_address' ),
			'show_gifts'        => $show_gifts,
			'gifts_text'        => esc_html__( 'This is a gift', 'wc_shipping_multiple_address' ),
			// 'show_datepicker'   => $show_datepicker,
			'date_label_text'   => esc_html( 'Shipping date:', 'wc_shipping_multiple_address' ),
			// 'valid_dates'       => $valid_dates,
			// 'excluded_dates'    => $excluded_dates,
			'date_error_text'   => esc_html__( 'The item cannot be send on', 'wc_shipping_multiple_address' ),
			// 'note_limit'        => $note_limit,
			'no_method_text'    => esc_html__( 'No shipping method', 'wc_shipping_multiple_address' ),
		);
	}
}

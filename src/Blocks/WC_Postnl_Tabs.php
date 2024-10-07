<?php
/**
 * Class WC_Postnl_Tabs file.
 *
 * @package postnl-for-woocommerce
 */

namespace PostNLWooCommerce\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PostNLWooCommerce\Blocks\Blocks_Integration;
use PostNLWooCommerce\Blocks\Store_API_Extension;

/**
 * Class WC_Postnl_Tabs.
 */
class WC_Postnl_Tabs {

	/**
	 * Order meta key name.
	 *
	 * @var string
	 */
	public $meta_key_order = '_shipping_methods';

	/**
	 * Meta key settings ( Might not be used anymore ).
	 *
	 * @var string
	 */
	public $meta_key_settings = '_shipping_settings';

	/**
	 * Settings from shipping settings.
	 *
	 * @var array
	 */
	public $settings = null;

	/**
	 * Saved settings.
	 *
	 * @var array
	 */
	public $gateway_settings = null;

	/**
	 * Notification and button text.
	 *
	 * @var array
	 */
	public static $lang = array(
		'notification' => 'You may use multiple shipping addresses on this cart',
		'btn_items'    => 'Set Multiple Addresses',
	);

	/**
	 * Class constructor.
	 */
	public function __construct() {
		// Load the shipping options.
		$this->settings = get_option( $this->meta_key_settings, array() );

		// Override needs shipping method and totals.
		add_action( 'woocommerce_init', array( $this, 'wc_init' ) );

		// Register Blocks Integration.
		add_action( 'woocommerce_blocks_loaded', array( $this, 'register_blocks_integration' ) );
		add_action( 'woocommerce_blocks_loaded', array( $this, 'extend_store_api' ) );

		$settings               = get_option( 'woocommerce_multiple_shipping_settings', array() );
		$this->gateway_settings = $settings;


		if ( isset( $settings['lang_btn_items'] ) ) {
			self::$lang['btn_items'] = $settings['lang_btn_items'];
		}

	}

	/**
	 * Check if multiship is enabled.
	 *
	 * @return boolean.
	 */
	public function is_multiship_enabled() {
		$enabled = true;

		// Role-based shipping methods.
		if ( class_exists( 'WC_Role_Methods' ) ) {
			global $current_user;

			$enabled = false;

			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}

			$the_roles          = $wp_roles->roles;
			$current_user_roles = array();

			if ( is_user_logged_in() ) {
				$user = new WP_User( $current_user->ID );
				if ( ! empty( $user->roles ) && is_array( $user->roles ) ) {
					foreach ( $user->roles as $role ) {
						$current_user_roles[] = strtolower( $the_roles[ $role ]['name'] );
					}
				}
			} else {
				$current_user_roles[] = 'Guest';
			}

			$role_methods = WC_Role_Methods::get_instance();

			foreach ( $current_user_roles as $user_role ) {
				if ( $role_methods->check_rolea_methods( $user_role, 'multiple_shipping' ) ) {
					$enabled = true;
					break;
				}
			}
		}

		/**
		 * Filter to manipulate multiship activation value.
		 *
		 * @param boolean $enabled is the multiship activation value.
		 *
		 * @since 3.1
		 */
		return apply_filters( 'wc_ms_is_multiship_enabled', $enabled );
	}

	/**
	 * Display privacy notice.
	 */
	public function wc_init() {
	}

	/**
	 * Register blocks integration.
	 */
	public function register_blocks_integration() {
		add_action(
			'woocommerce_blocks_checkout_block_registration',
			function ( $integration_registry ) {
				$integration_registry->register( new Blocks_Integration() );
			}
		);
	}

	/**
	 * Extend the store API.
	 */
	public function extend_store_api() {
		Store_API_Extension::init();
	}
}

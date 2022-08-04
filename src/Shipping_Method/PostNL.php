<?php
/**
 * Class Shipping_Method/PostNL file.
 *
 * @package PostNLWooCommerce\Shipping_Method
 */

namespace PostNLWooCommerce\Shipping_Method;

use PostNLWooCommerce\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PostNL
 *
 * @package PostNLWooCommerce\Shipping_Method
 */
class PostNL extends \WC_Shipping_Method {
	/**
	 * Init and hook in the integration.
	 *
	 * @param int $instance_id Instance ID.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id           = POSTNL_SETTINGS_ID;
		$this->instance_id  = absint( $instance_id );
		$this->method_title = __( 'PostNL', 'postnl-for-woocommerce' );

		// translators: %1$s & %2$s is replaced with <a> tag.
		$this->method_description = sprintf( __( 'Below you will find all functions for controlling, preparing and processing your shipment with PostNL. Prerequisite is a valid PostNL business customer contract. If you are not yet a PostNL business customer, you can request a quote %1$shere%2$s.', 'postnl-for-woocommerce' ), '<a href="https://mijnpostnlzakelijk.postnl.nl/s/become-a-customer?language=nl_NL#/" target="_blank">', '</a>' );

		$this->init();
	}

	/**
	 * Shipping method initialization.
	 */
	public function init() {
		$this->init_form_fields();
		$this->init_settings();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style_script' ) );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Enqueue style and script for admin setting page.
	 */
	public function enqueue_style_script() {
		$screen = get_current_screen();

		if ( ! empty( $screen->id ) && 'woocommerce_page_wc-settings' === $screen->id && ! empty( $_GET['section'] ) && 'postnl' === $_GET['section'] ) {

			wp_enqueue_script(
				'postnl-admin-settings',
				POSTNL_WC_PLUGIN_DIR_URL . '/assets/js/admin-settings.js',
				array( 'jquery' ),
				POSTNL_WC_VERSION,
				true
			);

			wp_localize_script(
				'postnl-admin-settings',
				'postnl_admin',
				array(
					'store_address' => wc_get_base_location(),
				)
			);
		}
	}

	/**
	 * Initialize integration settings form fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$base_country = Utils::get_base_country();
		$settings     = Settings::get_instance();

		if ( 'NL' === $base_country ) {
			$form_fields = $settings->nl_setting_fields();
		} elseif ( 'BE' === $base_country ) {
			$form_fields = $settings->be_setting_fields();
		} else {
			$form_fields = $settings->filter_setting_fields( '' );
		}

		$this->form_fields = $form_fields;
	}
}

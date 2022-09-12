<?php
/**
 * Class Rest_API\Shipping\Client file.
 *
 * @package PostNLWooCommerce\Rest_API\Shipping
 */

namespace PostNLWooCommerce\Rest_API\Shipping;

use PostNLWooCommerce\Rest_API\Base;
use PostNLWooCommerce\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Client
 *
 * @package PostNLWooCommerce\Rest_API\Shipping
 */
class Client extends Base {
	/**
	 * API Endpoint.
	 *
	 * @var string
	 */
	public $endpoint = '/v1/shipment?confirm=true';

	/**
	 * Function for composing API request.
	 */
	public function compose_body_request() {
		return array(
			'Customer'  => array(
				'Address'            => array(
					'AddressType' => '02',
					'City'        => $this->item_info->shipper['city'],
					'CompanyName' => $this->item_info->shipper['company'],
					'Countrycode' => $this->item_info->shipper['country'],
					'HouseNr'     => $this->item_info->shipper['address_2'],
					'Street'      => $this->item_info->shipper['address_1'],
					'Zipcode'     => $this->item_info->shipper['postcode'],
				),
				/* Temporarily hardcoded in Settings::get_location_code(). */
				'CollectionLocation' => $this->item_info->customer['location_code'],
				'CustomerCode'       => $this->item_info->customer['customer_code'],
				'CustomerNumber'     => $this->item_info->customer['customer_num'],
				'ContactPerson'      => $this->item_info->customer['company'],
				'Email'              => $this->item_info->customer['email'],
				'Name'               => $this->item_info->customer['company'],
			),
			/** Hardcoded */
			'Message'   => array(
				'MessageID'        => '36209c3d-14d2-478f-85de-abccd84fa790',
				'MessageTimeStamp' => gmdate( 'd-m-Y H:i:s' ),
				'Printertype'      => 'GraphicFile|PDF',
			),
			'Shipments' => $this->get_shipments(),
		);
	}

	/**
	 * Get shipments data.
	 */
	public function get_shipments() {
		$shipments = array();

		$shipment = array(
			'Addresses'           => $this->get_shipment_addresses(),
			'Contacts'            => array(
				array(
					'ContactType' => '01',
					'Email'       => $this->item_info->shipment['email'],
					'SMSNr'       => $this->item_info->shipment['phone'],
				),
			),
			'Dimension'           => array(
				'Weight' => $this->item_info->shipment['total_weight'],
			),
			'ProductCodeDelivery' => $this->item_info->shipment['product_code'],
		);

		if ( $this->item_info->is_dropoff_points() && $this->item_info->pickup_points['insured_shipping'] ) {
			$shipment['Amounts'][] = array(
				'AmountType' => '02',
				'Value'      => '10',
			);
		}

		$shipments[] = $shipment;

		return $shipments;
	}

	/**
	 * Get shipment addresses data.
	 */
	public function get_shipment_addresses() {
		$addresses = array();

		$addresses[] = array(
			'AddressType' => '01',
			'City'        => $this->item_info->receiver['city'],
			'Countrycode' => $this->item_info->receiver['country'],
			'FirstName'   => $this->item_info->receiver['first_name'],
			'HouseNr'     => $this->item_info->receiver['address_2'],
			'HouseNrExt'  => '',
			'Name'        => $this->item_info->receiver['last_name'],
			'Street'      => $this->item_info->receiver['address_1'],
			'Zipcode'     => $this->item_info->receiver['postcode'],
		);

		if ( $this->item_info->is_dropoff_points() ) {
			$addresses[] = array(
				'AddressType' => '09',
				'CompanyName' => $this->item_info->pickup_points['company'],
				'City'        => $this->item_info->pickup_points['city'],
				'Countrycode' => $this->item_info->pickup_points['country'],
				'HouseNr'     => $this->item_info->pickup_points['address_2'],
				'HouseNrExt'  => '',
				'Street'      => $this->item_info->pickup_points['address_1'],
				'Zipcode'     => $this->item_info->pickup_points['postcode'],
			);
		}

		return $addresses;
	}
}

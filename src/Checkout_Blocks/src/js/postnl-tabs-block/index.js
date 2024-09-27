/**
 * Postnl Tabs Block
 *
 * This file is responsible for rendering selected shipping addresses and options from the Multiple Addresses Shipping plugin.
 *
 * @package WC_Postnl_Tabs
 */

const { registerPlugin }                                         = window.wp.plugins;
const { ExperimentalOrderShippingPackages, extensionCartUpdate } = window.wc.blocksCheckout;
const { CART_STORE_KEY }                                         = window.wc.wcBlocksData;
const { RawHTML, useState, useEffect }                           = window.wp.element;
const { dispatch }                                               = window.wp.data;
const { Button, Textarea, CheckboxControl, TextInput }           = window.wc.blocksComponents;
import { __ } from '@wordpress/i18n';

import './style.scss';

const onClickSetWCMSButton = ( data_url ) => {
	localStorage.removeItem( 'ms_note' );
	localStorage.removeItem( 'ms_date' );
	localStorage.removeItem( 'ms_gift' );

	location.href = data_url;
}

/**
 * Postnl Tabs shipping component.
 *
 * @param extensions
 * @returns {JSX.Element}
 * @constructor
 */
const PostnlTabs = ( { extensions } ) => {
	const namespace = 'wc_postnl_tabs';
	console.log( extensions );
	let has_errors  = false;
	// Skip if we don't have any relevant data.
	if ( ! extensions[ namespace ][ 'postnl_tabs' ] ) {
		return <div className="wc_postnl_tabs_block"></div>;
	}

	const staticVar = extensions[ namespace ][ 'postnl_tabs' ]['static_var'];
	const isLetterbox = extensions[ namespace ][ 'postnl_tabs' ]['letterbox'];
	const field = extensions[ namespace ][ 'postnl_tabs' ]['field'];
	const tabs = extensions[ namespace ][ 'postnl_tabs' ]['tabs'];
	const defaultVal = extensions[ namespace ][ 'postnl_tabs' ]['defaultVal'];
	
	return ( 
		<section>
			<div className="postnl_checkout_message">
				{ __('These items are eligible for letterbox delivery.', 'postnl-for-woocommerce') }
			</div>
			{isLetterbox && (
				<div className="postnl_checkout_message">
					{ __( 'These items are eligible for letterbox delivery.', 'postnl-for-woocommerce' ) }
				</div>
			)}
			<div id="postnl_checkout_option" className={`postnl_checkout_container ${isLetterbox ? 'is-hidden' : ''}`}>
				<div className="postnl_checkout_tab_container">
					<ul className="postnl_checkout_tab_list">
						{tabs.map((tab) => {
							const isChecked = selectedOption === tab.id;
							const activeClass = isChecked ? 'active' : '';

							return (
								<li key={tab.id} className={activeClass}>
									<label htmlFor={`${field.name}_${tab.id}`} className="postnl_checkout_tab">
										<span>{tab.name}</span>
										<input
											type="radio"
											name={field.name}
											id={`${field.name}_${tab.id}`}
											className="postnl_option"
											value={tab.id}
											checked={isChecked}
											onChange={handleOptionChange}
										/>
									</label>
								</li>
							);
						})}
					</ul>
				</div>

				<div className="postnl_checkout_content_container">
					{__('PostNL checkout content goes here', 'postnl-for-woocommerce')}
				</div>

				<div className="postnl_checkout_default">
					<input type="hidden" name="postnl_default" value={defaultVal.val} />
					<input type="hidden" name="postnl_default_date" value={defaultVal.date} />
					<input type="hidden" name="postnl_default_from" value={defaultVal.from} />
					<input type="hidden" name="postnl_default_to" value={defaultVal.to} />
					<input type="hidden" name="postnl_default_price" value={defaultVal.price} />
					<input type="hidden" name="postnl_default_type" value={defaultVal.type} />
				</div>
			</div>
		</section>
	);
}

const render = () => {
	return (
		<div className="postnl-co-tr postnl-co-tr-container">
			<PostnlTabs />
	 	</div>
	);
};

registerPlugin( 'postnl-tabs-checkout-block', {
	render,
	scope: 'woocommerce-checkout',
} );
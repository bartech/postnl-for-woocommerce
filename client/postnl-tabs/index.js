/**
 * Postnl Tabs Block
 *
 * This file is responsible for rendering duplicate cart button from the Multiple Addresses Shipping plugin.
 *
 * @package WC_Shipping_Multiple_Addresses
 */

import { __ } from '@wordpress/i18n';

const { registerPlugin }            = window.wp.plugins;
const { ExperimentalDiscountsMeta } = window.wc.blocksCheckout;
const { Button }                    = window.wc.blocksComponents;

import './style.scss';

/**
 * Postnl Tabs component.
 *
 * @param extensions
 * @returns {JSX.Element}
 * @constructor
 */
const PostnlTabs = ( { extensions } ) => {
	const namespace = 'wc_postnl_tabs';
	console.log(extensions[ namespace ][ 'postnl_tabs' ]['tabs']);
	// Skip if there are no text and url for this type.
	if ( ! extensions[ namespace ][ 'postnl_tabs' ]['field'] || ! extensions[ namespace ][ 'postnl_tabs' ]['tabs'] ) {
		return <div className="woocommerce-shipping-multiple-addresses-info"></div>;
	}

	const datas = extensions[ namespace ][ 'postnl_tabs' ];

	return ( 
		// <div className="wcms-postnl-tabs">
		// 	<Button
		// 		className="wcms-postnl-tabs-button"
		// 		onClick={ ( e ) => buttonOnClick( datas.url ) }
		// 		label={ datas.text }
		// 		showTooltip={ false }
		// 	>{ datas.text }</Button>
		// </div>
		<section>
			<div className="postnl_checkout_message">
				{ __('These items are eligible for letterbox delivery.', 'postnl-for-woocommerce') }
			</div>
			{ datas.isLetterbox && (
				<div className="postnl_checkout_message">
					{ __( 'These items are eligible for letterbox delivery.', 'postnl-for-woocommerce' ) }
				</div>
			)}
		<div id="postnl_checkout_option" className={`postnl_checkout_container ${datas.isLetterbox ? 'is-hidden' : ''}`}>
			<div className="postnl_checkout_tab_container">
				<ul className="postnl_checkout_tab_list">
					{datas.tabs.map((tab) => {
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
				<input type="hidden" name="postnl_default" value={datas.defaultVal.val} />
				<input type="hidden" name="postnl_default_date" value={datas.defaultVal.date} />
				<input type="hidden" name="postnl_default_from" value={datas.defaultVal.from} />
				<input type="hidden" name="postnl_default_to" value={datas.defaultVal.to} />
				<input type="hidden" name="postnl_default_price" value={datas.defaultVal.price} />
				<input type="hidden" name="postnl_default_type" value={datas.defaultVal.type} />
			</div>
		</div>
	</section>
	);
}

const buttonOnClick = ( data_url ) => {
	location.href = data_url;
};

const render = () => {
	return (
		<ExperimentalDiscountsMeta>
			<PostnlTabs />
		</ExperimentalDiscountsMeta>
	);
};

registerPlugin( 'wcpt-postnl-tabs', {
	render,
	scope: 'woocommerce-checkout',
} );
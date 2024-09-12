import { useBlockProps } from '@wordpress/block-editor';
import { useState } from 'react';
import { __ } from '@wordpress/i18n';

export const Block = (props) => {
    const blockProps = useBlockProps(); // Ensure proper block properties
    const { attributes = {} } = props;  // Default to an empty object if attributes is undefined
    const {
        letterbox = false,
        field = {},
        tabs = [],
        defaultVal = {},
        response = {},
        postData = {}
    } = attributes;

    const [selectedOption, setSelectedOption] = useState(tabs.length > 0 ? tabs[0].id : '');
    const [isLetterbox, setIsLetterbox] = useState(letterbox);

    const handleOptionChange = (event) => {
        setSelectedOption(event.target.value);
    };
console.error(tabs);
    return (
        <div {...blockProps} className="postnl-co-tr postnl-co-tr-container">
            {isLetterbox ? (
                <div className="postnl_checkout_message">
                    {__('These items are eligible for letterbox delivery.', 'postnl-for-woocommerce')}
                </div>
            ) : (
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
            )}
        </div>
    );
};

( function( $ ) {

	var postnl_settings = {
		// init Class
		init: function() {
			var environment_mode = jQuery( '#woocommerce_postnl_environment_mode' );


			this.display_api_key_field();
            this.display_printer_type_resolution_field();
			environment_mode
				.on( 'change', this.display_api_key_field );
		},

		display_api_key_field: function() {
			var value = jQuery( '#woocommerce_postnl_environment_mode' ).val();

			if ( 'production' === value ) {
				jQuery('#woocommerce_postnl_api_keys').closest('tr').show();
				jQuery('#woocommerce_postnl_api_keys_sandbox').closest('tr').hide();
			} else {
				jQuery('#woocommerce_postnl_api_keys').closest('tr').hide();
				jQuery('#woocommerce_postnl_api_keys_sandbox').closest('tr').show();
			}

		},

        display_printer_type_resolution_field: function () {
            var select = jQuery( '#woocommerce_postnl_printer_type' );
            var parent = this;
            this.checkValue( select )
            select.on('change', function() {
                parent.checkValue( select );
            });
        },

        checkValue: function ( select ) {
            if ( select[0].value == 'PDF' ) {
                jQuery('#woocommerce_postnl_printer_type_resolution').closest('tr').hide();
            } else {
                jQuery('#woocommerce_postnl_printer_type_resolution').closest('tr').show();
            }
        }
	};

	postnl_settings.init();

} )( jQuery );

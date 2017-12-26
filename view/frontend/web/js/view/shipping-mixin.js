/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @copyright 2017 La Poste
 * @license   Open Software License ("OSL") v. 3.0
 */
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'LaPoste_ColissimoFrontPage/js/model/widget',
        'Magento_Customer/js/model/address-list'
    ],
    function (
        ko,
        $,
        quote,
        widgetModel,
        addressList
    ) {
        'use strict';

        return function (target) {
            return target.extend({
                /**
                 * Override to change isFormInline on new address added.
                 */
                initialize: function () {
                    this._super();
                    addressList.subscribe(
                        function(newAddressList) {
                            this.isFormInline = newAddressList.length == 0;
                            if (this.isFormInline) {
                                $('.form-shipping-address').show();
                            } else {
                                $('.form-shipping-address').hide();
                            }
                        },
                        this
                    );
                },

                /**
                 * Override the validateShippingInformation method to check if a relay point is selected when
                 * colissimo shipping method has been chosen.
                 */
                validateShippingInformation: function () {
                    var shippingMethod = quote.shippingMethod();
                    var self = this;
                    if (
                        shippingMethod.carrier_code !== 'colissimofrontpage'
                        || widgetModel.validateRelayPoint(self)
                    ) {
                        return this._super();
                    } else {
                        return false;
                    }
                }
            });
        };
    }
);

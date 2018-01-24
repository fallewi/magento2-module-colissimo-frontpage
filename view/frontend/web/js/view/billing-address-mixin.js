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
        'Magento_Checkout/js/model/quote'
    ],
    function (
        ko,
        quote
    ) {
        'use strict';

        return function (target) {
            return target.extend({
                /**
                 * Override to show billing address form if relay point is chosen.
                 */
                initialize: function () {
                    this._super();
                    this.isAddressDetailsVisible.subscribe(
                        function (value) {
                            if (value) {
                                var billingAddress = quote.billingAddress();
                                if (typeof billingAddress.postcode == 'undefined') {
                                    this.isAddressDetailsVisible(false);
                                }
                            }
                        },
                        this
                    );
                }
            });
        };
    }
);

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
        'jquery',
        'Magento_Checkout/js/checkout-data',
        'LaPoste_ColissimoFrontPage/js/model/relay-address-manager'
    ],
    function ($, checkoutData, relayAddressManager) {
        'use strict';

        return function (target) {
            var targetResolveShippingAddress = target.resolveShippingAddress.bind(target);

            return $.extend(target, {
                /**
                 * Resolve shipping address. Used local storage
                 */
                resolveShippingAddress: function () {
                    var newRelayAddress = relayAddressManager.getNewRelayAddress();

                    if (newRelayAddress) {
                        relayAddressManager.createRelayAddress(newRelayAddress);
                    }

                    targetResolveShippingAddress();
                }
            });
        };
    }
);

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
        'uiComponent',
        'ko',
        'Magento_Checkout/js/model/quote',
        'LaPoste_ColissimoFrontPage/js/model/widget'
    ],
    function (Component, ko, quote, widgetModel) {
        'use strict';

        return Component.extend({
            isVisible: widgetModel.isVisible,
            relayAddress: widgetModel.relayAddress,

            /**
             * @return {exports} TODO Doc
             */
            initialize: function () {
                this._super();
                quote.shippingMethod.subscribe(this.checkIfFrontPageIsSelected, this);
                this.checkIfFrontPageIsSelected(quote.shippingMethod());
            },

            checkIfFrontPageIsSelected: function(shippingMethod) {
                if (
                    shippingMethod
                    && "method_code" in shippingMethod
                    && shippingMethod.method_code == 'colissimofrontpage'
                ) {
                    widgetModel.show();
                } else {
                    widgetModel.hide();
                }
            }
        });
    }
);

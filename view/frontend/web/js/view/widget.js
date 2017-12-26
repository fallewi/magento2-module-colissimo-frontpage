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
            previousValue: null,
            hasBeenInitialize: false,

            /**
             * Init widget view.
             * listen to change shipping method event.
             */
            initialize: function () {
                this._super();
            },

            /**
             * After the template is loaded, check if colissimo shipping method is selected
             */
            onTemplateInit: function () {
                quote.shippingMethod.subscribe(widgetModel.checkIfFrontPageIsSelected, widgetModel);
                if (!this.hasBeenInitialize) {
                    widgetModel.initRelayAddress();
                    widgetModel.checkIfFrontPageIsSelected(quote.shippingMethod());
                    this.hasBeenInitialize = true;
                }
            }
        });
    }
);

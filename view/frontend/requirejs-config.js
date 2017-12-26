/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @copyright 2017 La Poste
 * @license   Open Software License ("OSL") v. 3.0
 */
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'LaPoste_ColissimoFrontPage/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'LaPoste_ColissimoFrontPage/js/model/checkout-data-resolver-mixin': true
            },
            'Magento_Checkout/js/view/shipping-address/list': {
                'LaPoste_ColissimoFrontPage/js/view/shipping-address-list-mixin': true
            }
        }
    }
};

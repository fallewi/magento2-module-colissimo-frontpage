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
        'Magento_Customer/js/model/address-list'
    ],
    function (
        ko,
        addressList
    ) {
        'use strict';

        return function (target) {
            return target.extend({

                defaults: {
                    visible: ko.observable(addressList().length > 0)
                },

                /**
                 * Override to change visible attribute to observable.
                 */
                initialize: function () {
                    this._super();
                    addressList.subscribe(
                        function(newAddressList) {
                            this.visible(newAddressList.length > 0);
                        },
                        this
                    );
                }
            });
        };
    }
);

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
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/checkout-data',
        'Magento_Customer/js/customer-data',
        'mage/translate'
    ],
    function (
        ko,
        $,
        quote,
        selectShippingAddressAction,
        selectBillingAddressAction,
        addressConverter,
        addressList,
        checkoutData,
        storage,
        $t
    ) {
        'use strict';

        /**
         * Relay address manager.
         */
        return {

            addressElement: '.shipping-address-item',
            previousSelectedAddress: null,
            checkoutDataCacheKey: 'checkout-data',

            /**
             * Init relay address.
             */
            initRelayAddress: function() {
                addressList.valueHasMutated();
                var hasRelayAddress = false;
                addressList().some(
                    function(currentAddress, index)    {
                        if (currentAddress.getType() == 'new-relay-address') {
                            hasRelayAddress = true;
                        }
                    }
                );
                if (hasRelayAddress) {
                    this.addUpdateButton();
                    this.disableNonRelayAddress();
                }
            },

            /**
             * Select relay address in addresses list.
             *
             * @param address
             */
            selectRelayAddress: function (address) {
                if (quote.shippingAddress() && quote.shippingAddress().getType() != 'new-relay-address')
                {
                    this.previousSelectedAddress = quote.shippingAddress();
                    if (!quote.billingAddress()) {
                        selectBillingAddressAction(this.previousSelectedAddress);
                    }
                }
                selectShippingAddressAction(address);
                checkoutData.setSelectedShippingAddress(address.getKey());
                this.setNewRelayAddress(address);
                this.disableNonRelayAddress();
                this.addUpdateButton();
            },

            /**
             * Create a new relay address.
             *
             * @param addressData
             * @returns {Object}
             */
            createRelayAddress: function (addressData) {
                var relayAddress = addressConverter.formAddressDataToQuoteAddress(addressData);
                relayAddress.isEditable = function () { return false; };
                relayAddress.canUseForBilling = function () { return false; };
                relayAddress.getType = function () { return 'new-relay-address'; };
                relayAddress.getKey = function () { return 'new-relay-address'; };

                var isAddressUpdated = addressList().some(
                    function(currentAddress, index, addresses)    {
                        if (currentAddress.getKey() == relayAddress.getKey()) {
                            addresses[index] = relayAddress;
                            return true;
                        }
                        return false;
                    }
                );

                // Force show last element in case it is hidden (@see removeRelayAddress)
                $(this.addressElement).last().show();

                relayAddress.extension_attributes = ("extension_attributes" in addressData)
                    ? addressData.extension_attributes
                    : { colissimoRelayData: JSON.stringify(
                        { relayId : ("identifiant" in addressData) ? addressData.identifiant : null }
                        )
                    };

                isAddressUpdated
                    ? addressList.valueHasMutated()
                    : addressList.push(relayAddress);

                return relayAddress;
            },

            /**
             * Remove the relay address from addresses list.
             */
            removeRelayAddress: function () {
                if (this.previousSelectedAddress) {
                    selectShippingAddressAction(this.previousSelectedAddress);
                    checkoutData.setSelectedShippingAddress(this.previousSelectedAddress.getKey());
                } else {
                    if (addressList().length) {
                        selectShippingAddressAction(addressList()[0]);
                        checkoutData.setSelectedShippingAddress(addressList()[0].getKey());
                    } else {
                        checkoutData.setSelectedShippingAddress('new-customer-address');
                    }
                }

                var relayAddressIndex = false;
                addressList().some(
                    function(currentAddress, index)    {
                        if (currentAddress.getType() == 'new-relay-address') {
                            relayAddressIndex = index;
                        }
                    }
                );

                if (relayAddressIndex !== false) {
                    addressList().splice(relayAddressIndex, 1);
                    addressList.valueHasMutated();
                    // Address list component are not updated until a new address is added,
                    // so we remove it from the last and we hide the component
                    $(this.addressElement).last().hide();
                }
                this.removeNewRelayAddress();
                this.enableNonRelayAddress();
            },

            /**
             * Add update button on relay address.
             * Use a custom button instead of the native one to be able to cache the event on show the widget.
             */
            addUpdateButton: function() {
                // Use a timeout in order to wait the next ko render
                // and avoid to add the button to the wrong address component
                setTimeout(
                    $.proxy(
                        function() {
                            var addressComponent = $(this.addressElement).last();
                            if (!addressComponent.find('.edit-relay-address-link').length) {
                                addressComponent.append(
                                    $('<button>').addClass('edit-relay-address-link')
                                        .addClass('edit-address-link')
                                        .html('<span>' + $t('Edit') + '</span>')
                                );
                            }
                        },
                        this
                    ),
                    200
                );
            },

            /**
             * Save relay address in customer data (to keep it on page reload).
             * @param data
             */
            setNewRelayAddress: function (data) {
                var obj = storage.get(this.checkoutDataCacheKey)();
                obj.newRelayAddress = $.extend(true, {}, data);
                storage.set(this.checkoutDataCacheKey, obj);
            },

            /**
             * Save relay address in customer data (to keep it on page reload).
             */
            removeNewRelayAddress: function () {
                var obj = storage.get(this.checkoutDataCacheKey)();
                obj.newRelayAddress = null;
                storage.set(this.checkoutDataCacheKey, obj);
            },

            /**
             * Get relay addres from customer data
             * @returns {*}
             */
            getNewRelayAddress: function () {
                return storage.get(this.checkoutDataCacheKey)().newRelayAddress;
            },

            /**
             * Disable the button to prevent select customer address with relay point shipping method.
             */
            disableNonRelayAddress: function() {
                $('.action-select-shipping-item').hide();
                $('.action-show-popup').hide();
            },

            /**
             * Enable the button to allow select customer.
             */
            enableNonRelayAddress: function() {
                $('.action-select-shipping-item').show();
                $('.action-show-popup').show();
            }
        };
    }
);

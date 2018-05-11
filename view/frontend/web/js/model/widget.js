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
        'LaPoste_ColissimoFrontPage/js/model/relay-address-manager',
        'mage/translate',
        "jquery/ui"
    ],
    function (ko, $, quote, relayAddressManager, $t) {
        'use strict';

        /**
         * Script that control colissimo widget.
         */
        return {

            isInitialized: false,
            isVisible: ko.observable(false),
            widgetContainerId: '#colissimo-frontpage-widget-container',
            errorCodeElement: '#pudoWidgetErrorCode',
            errorMessageElement: '#pudoWidgetErrorMessage',
            relayNameElement: '#pudoWidgetCompanyName',
            addressElement: '.shipping-address-item',
            addressListElement: '.checkout-shipping-address',
            data: {},

            /**
             * Init Colissimo FrontPage Widget.
             */
            init: function (retryNumber) {
                retryNumber = typeof retryNumber == 'undefined' ? 0 : retryNumber;
                var widgetContainer = $(this.widgetContainerId);
                if (widgetContainer.length) {
                    this.data['callBackFrame'] = 'callBackFrame';
                    if (widgetContainer.frameColissimoOpen) {
                        widgetContainer.frameColissimoOpen(this.data);
                        this.isInitialized = true;
                    } else if (retryNumber < 5) {
                        setTimeout($.proxy(this.init, this, retryNumber + 1), 1000);
                    }
                }
                $(document).on('relaySelected', $.proxy(this.selectedRelayPoint, this));
                $(document).on('click', this.addressElement + ' .edit-relay-address-link', this.show.bind(this));
            },

            /**
             * Init Colissimo to FrontPage Widget.
             * @param data
             */
            setData: function (data) {
                this.data = data;
                quote.shippingAddress.subscribe(
                    function (newAddress) {
                        this.data['ceAddress'] = newAddress.street
                            ? newAddress.street.join(' ')
                            : this.data['ceAddress'];
                        this.data['ceZipCode'] = newAddress.street
                            ? newAddress.postcode
                            : this.data['ceZipCode'];
                        this.data['ceTown'] = newAddress.city
                            ? newAddress.city
                            : this.data['ceTown'];

                        // If widget is already initialize, reset it
                        var widgetContainer = $(this.widgetContainerId);
                        if (widgetContainer.length
                            && widgetContainer.frameColissimoClose
                            && widgetContainer.frameColissimoOpen
                            && this.isInitialized
                        ) {
                            widgetContainer.frameColissimoClose();
                            widgetContainer.frameColissimoOpen(this.data);
                        }
                    }.bind(this)
                );

                // Load script
                $.ajax({ async: true, dataType: "script", url: this.data.scriptUrl });
                // The widget is not initialized now because it need its div to be visible.
            },

            /**
             * Select a relay point.
             *
             * @param event
             */
            selectedRelayPoint: function (event) {
                var point = event.detail;
                if (typeof point === 'object') {
                    this.hide();
                    var relayShippingAddress = relayAddressManager.createRelayAddress(
                        {
                            "firstname"   : $t('Relay Point'),
                            // Set in lastname because most of magento renderer doesn't use company field
                            "lastname"    : ("nom" in point) ? point.nom : null,
                            "telephone"   : quote.shippingAddress().telephone,
                            "postcode"    : ("codePostal" in point) ? point.codePostal : null,
                            "city"        : ("localite" in point) ? point.localite : null,
                            "country_id"  : ("codePays" in point) ? point.codePays : null,
                            "street"      : [
                                ("adresse1" in point) ? point.adresse1 : ''
                                + ("adresse2" in point) ? point.adresse2 : ''
                                + ("adresse3" in point) ? point.adresse3 : ''
                            ],
                            "identifiant" : ("identifiant" in point) ? point.identifiant : null
                        }
                    );
                    relayAddressManager.selectRelayAddress(relayShippingAddress);
                }
            },

            /**
             * Validate a relay point has been selected.
             *
             * @param shippingStep
             * @returns {boolean}
             */
            validateRelayPoint: function (shippingStep) {
                var errorCode = $(this.errorCodeElement).val();

                if ((typeof errorCode != 'undefined' && errorCode != 0)
                    || quote.shippingAddress().getType() != 'new-relay-address'
                ) {
                    shippingStep.errorValidationMessage(
                        $(this.errorMessageElement).val()
                            ? $(this.errorMessageElement).val()
                            : $t('Colissimo: Please choose a relay point')
                    );
                    return false;
                }
                return true;
            },

            /**
             * Show colissimo frontpage widget.
             */
            show: function () {
                if (!this.isInitialized) {
                    this.init();
                }
                var previousValue = this.isVisible();
                this.isVisible(true);
                if (previousValue === false) {
                    $('html,body').animate({ scrollTop: $(this.widgetContainerId).offset().top }, 'fast');
                }
            },

            /**
             * Hide colissimo frontpage widget.
             */
            hide: function () {
                var previousValue = this.isVisible();
                this.isVisible(false);
                if (previousValue === true) {
                    $('html,body').animate({ scrollTop: $(this.addressListElement).offset().top }, 'fast');
                }
            },

            /**
             * Show the map if colissimo shipping method is selected.
             *
             * @param shippingMethod
             */
            checkIfFrontPageIsSelected: function (shippingMethod) {
                if (shippingMethod
                    && "method_code" in shippingMethod
                ) {
                    if (shippingMethod.method_code != this.previousValue) {
                        this.previousValue = shippingMethod.method_code;
                        if (shippingMethod.method_code == 'colissimofrontpage') {
                            this.show();
                        } else {
                            relayAddressManager.removeRelayAddress();
                            this.hide();
                        }
                    } else {
                        if (shippingMethod.method_code == 'colissimofrontpage'
                            && quote.shippingAddress().getType() != 'new-relay-address'
                        ) {
                            this.show();
                        } else {
                            this.hide();
                        }
                    }
                }
            },

            /**
             * Init relay address.
             */
            initRelayAddress: function () {
                relayAddressManager.initRelayAddress();
            }
        };
    }
);

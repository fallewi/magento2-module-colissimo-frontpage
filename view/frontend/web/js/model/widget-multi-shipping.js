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
        'mage/translate',
        "jquery/ui"
    ],
    function (ko, $, $t) {
        'use strict';

        /**
         * Script that control colissimo widget in multi-shipping tunnel.
         */
        return {

            isInitialized: false,
            isVisible: ko.observable(false),
            widgetContainerId: '#colissimo-frontpage-widget-container',
            errorCodeElement: '#pudoWidgetErrorCode',
            errorMessageElement: '#pudoWidgetErrorMessage',
            relayNameElement: '#pudoWidgetCompanyName',
            addressElement: '.shipping-address-item',
            addressListElement: '.multishipping-checkout-shipping',
            relayAddressLine: null,
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
                        this.updateCountrySelect();
                    } else if (retryNumber < 5) {
                        setTimeout($.proxy(this.init, this, retryNumber + 1), 1000);
                    }
                }
                $(document).on('relaySelected', $.proxy(this.selectedRelayPoint, this));
            },

            /**
             * Force country select in widget because of a weird and unresolved bug which
             * block country selection
             */
            updateCountrySelect: function() {
                $(this.widgetContainerId).on('DOMSubtreeModified', function() {
                    var widgetContainer = $(this.widgetContainerId),
                        countrySelect = widgetContainer.find('#listePays');
                    if (countrySelect[0]) {
                        countrySelect.find('option[value="' + countrySelect[0].getAttribute('value') + '"]').prop('selected', true);
                    }
                }.bind(this));
            },

            /**
             * Init Colissimo to FrontPage Widget.
             * @param data
             */
            setData: function (data) {
                this.data = data;

                // Load script
                $.ajax({ async: true, dataType: "script", url: this.data.scriptUrl });
                // The widget is not initialized now because it need its div to be visible.

                this.initBinding();
            },

            /**
             * Init Event Binding for Colissimo to FrontPage Widget.
             */
            initBinding: function() {
                // Bind show/hide on isVisible observable.
                var self = this;
                this.isVisible.subscribe(
                    function(newValue) {
                        newValue ? $(self.widgetContainerId).show() : $(self.widgetContainerId).hide();
                    }
                );

                // Find relay address line in page.
                this.relayAddressLine = $('#colissimo-relay-address').closest('.block-shipping');
                this.relayAddressLine.find('.box-shipping-address .action.edit').on(
                    'click',
                    function(event) {
                        event.preventDefault();
                        window.location=this.widgetContainerId;
                        this.show();
                    }.bind(this)
                );
                this.relayAddressLine.on(
                    'click',
                    '.items.methods-shipping .radio',
                    this.checkIfFrontPageIsSelected.bind(this)
                );
                $('.action.primary.continue').on(
                    'click',
                    function(event) {
                        if (!this.validateRelayPoint()) {
                            event.preventDefault();
                            alert($t('Colissimo: Please choose a relay point'));
                            this.show();
                        }
                    }.bind(this)
                )
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

                    // Replace colissimo address by loader.
                    var addressEl = this.relayAddressLine.find('address'),
                        oldAddress = addressEl.html();

                    addressEl.html(
                        '<img src="' + this.data.loaderUrl + '" alt="loader" />'
                    );

                    var relayShippingAddress = {
                        "firstname"   : $t('Relay Point'),
                        // Set in lastname because most of magento renderer doesn't use company field
                        "lastname"    : ("nom" in point) ? point.nom : null,
                        "postcode"    : ("codePostal" in point) ? point.codePostal : null,
                        "city"        : ("localite" in point) ? point.localite : null,
                        "country_id"  : ("codePays" in point) ? point.codePays : null,
                        "street"      : [
                            ("adresse1" in point) ? point.adresse1 : ''
                            + ("adresse2" in point) ? point.adresse2 : ''
                            + ("adresse3" in point) ? point.adresse3 : ''
                        ],
                        "identifiant" : ("identifiant" in point) ? point.identifiant : null
                    };

                    // Update address.
                    $.post(
                        this.data.updateAddressUrl,
                        relayShippingAddress,
                        function(data) {
                            if ('address_html' in data) {
                                addressEl.html(data['address_html']);
                            } else {
                                addressEl.html(oldAddress);
                            }
                        }.bind(this)
                    ).fail(
                        function() {
                            addressEl.html(oldAddress);
                        }.bind(this)
                    )
                }
            },

            /**
             * Validate a relay point has been selected.
             *
             * @returns {boolean}
             */
            validateRelayPoint: function () {
                return !this.relayAddressLine.find('#no_relay_selected').length;
            },

            /**
             * Show colissimo frontpage widget.
             */
            show: function () {
                var needTimeout = false;
                if (!this.isInitialized) {
                    this.init();
                    needTimeout = true;

                }
                var previousValue = this.isVisible();
                this.isVisible(true);
                if (previousValue === false) {
                    this.isVisible(true);
                    if (needTimeout) {
                        setTimeout(
                            function () {
                                $('html,body').animate({ scrollTop: $(this.widgetContainerId).offset().top }, 'fast');
                            }.bind(this),
                            2000
                        );
                    } else {
                        $('html,body').animate({ scrollTop: $(this.widgetContainerId).offset().top }, 'fast');
                    }
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
             */
            checkIfFrontPageIsSelected: function () {
                if (this.relayAddressLine.find('.radio:checked').val() == 'colissimofrontpage_colissimofrontpage') {
                    this.show();
                } else {
                    this.hide();
                }
            }
        };
    }
);

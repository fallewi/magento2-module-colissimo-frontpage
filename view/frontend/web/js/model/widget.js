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
        'jquery'
    ],
    function (ko, $) {
        'use strict';

        console.log('load script');

        $.ajax(
            {
                async: false,
                dataType: "script",
                url: 'https://soco-community-2.1.vm/pub/static/frontend/Magento/luma/fr_FR/LaPoste_ColissimoFrontPage/js//jquery.frameColiposte.js'
            }
        );

        /**
         * TODO Doc
         */
        return {

            isInitialized: false,
            isVisible: ko.observable(false),
            widgetContainerId: '#colissimo-frontpage-widget-container',
            widgetDataContainerId: '#colissimo-frontpage-widget-data-container',
            relayAddress: {
                id:         ko.observable(),
                name:       ko.observable(),
                street:     ko.observable(),
                postcode:   ko.observable(),
                city:       ko.observable(),
                country:    ko.observable()
            },
            data: {},

            init: function() {
                console.log('init widget');
                console.log(this.data);
                console.log($(this.widgetContainerId));

                var widgetContainer = $(this.widgetContainerId);

                if (widgetContainer.length) {
                    this.data['callBackFrame'] = this.selectRelayPointCallback;
                    widgetContainer.frameColiposteOpen(this.data);
                    this.isInitialized = true;
                }

                widgetContainer.on('click', 'a', function(event) { event.preventDefault(); });
                $(document).on('relaySelected', $.proxy(this.selectedRelayPoint, this));
            },

            setData: function(data) {
                console.log('set data widget');
                console.log(data);
                this.data = data;
            },

            // Todo explain two method
            selectRelayPointCallback: function callBackFrame(point) {
                jQuery(document).trigger('relaySelected', point);
            },

            selectedRelayPoint: function (event, point) {
                if (typeof point === 'object') {
                    this.relayAddress.id(("identifiant" in point) ? point.identifiant : null);
                    this.relayAddress.name(("nom" in point) ? point.nom : null);
                    this.relayAddress.postcode(("codePostal" in point) ? point.codePostal : null);
                    this.relayAddress.city(("localite" in point) ? point.localite : null);
                    this.relayAddress.country(("codePays" in point) ? point.codePays : null);
                    this.relayAddress.street(
                        (("adresse1" in point) ? point.adresse1 : '')
                        + (("adresse2" in point) ? point.adresse2 : '')
                        + (("adresse3" in point) ? point.adresse3 : '')
                    );
                }
            },

            show: function() {
                if (!this.isInitialized) {
                    this.init();
                }
                this.isVisible(true);
            },

            hide: function() {
                this.isVisible(false);
            }
        };
    }
);

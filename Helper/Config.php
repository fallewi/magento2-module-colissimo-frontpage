<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @copyright 2017 La Poste
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace LaPoste\ColissimoFrontPage\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Config helper.
 *
 * @author Smile (http://www.smile.fr)
 */
class Config extends AbstractHelper
{
    /**
     * Config paths.
     */
    const PATH_CARRIER_IS_ACTIVE                = 'carriers/colissimofrontpage/active';
    const PATH_CARRIER_TITLE                    = 'carriers/colissimofrontpage/title';
    const PATH_CARRIER_NAME                     = 'carriers/colissimofrontpage/name';
    const PATH_CARRIER_WIDGET_URL               = 'carriers/colissimofrontpage/widget_url';
    const PATH_CARRIER_AUTH_WS_URL              = 'carriers/colissimofrontpage/ws_auth_url';
    const PATH_CARRIER_AUTH_WS_LOGIN            = 'carriers/colissimofrontpage/ws_auth_login';
    const PATH_CARRIER_AUTH_WS_PASSWORD         = 'carriers/colissimofrontpage/ws_auth_pass';
    const PATH_CARRIER_AMOUNT                   = 'carriers/colissimofrontpage/amount';
    const PATH_CARRIER_MIN_QUOTE_PRICE_FOR_FREE = 'carriers/colissimofrontpage/min_quote_price_for_free';
    const PATH_CARRIER_MIN_ORDER_TOTAL          = 'carriers/colissimofrontpage/min_order_total';
    const PATH_CARRIER_MAX_ORDER_TOTAL          = 'carriers/colissimofrontpage/max_order_total';
    const PATH_CARRIER_MAX_WEIGHT               = 'carriers/colissimofrontpage/max_weight';
    const PATH_CARRIER_ALLOW_SPECIFIC_COUNTRIES = 'carriers/colissimofrontpage/sallowspecific';
    const PATH_CARRIER_SPECIFIC_COUNTRIES       = 'carriers/colissimofrontpage/specificcountry';
    const PATH_CARRIER_ORDER_PREPARE_DELAY      = 'carriers/colissimofrontpage/order_prepare_delay';
    const PATH_CARRIER_DEFAULT_COUNTRY          = 'carriers/colissimofrontpage/default_country';
    const PATH_CARRIER_DEFAULT_ADDRESS          = 'carriers/colissimofrontpage/default_address';
    const PATH_CARRIER_DEFAULT_POSTCODE         = 'carriers/colissimofrontpage/default_postcode';
    const PATH_CARRIER_DEFAULT_CITY             = 'carriers/colissimofrontpage/default_city';
    const PATH_RESOURCE_URL                     = 'carriers/colissimofrontpage/resource_url';
    const PATH_CARRIER_SORT_ORDER               = 'carriers/colissimofrontpage/sort_order';
    const GENERAL_AUTHORIZED_COUTRNIES          = 'general/country/allow';
    /**#@-*/

    /**
     * Colissimo relay address data field (on order entity).
     */
    const FIELD_COLISSIMO_RELAY_ADDRESS_DATA = 'colissimo_relay_address_data';

    /**
     * Country list supported by colissimo widget.
     */
    const SUPPORTED_COUNTRIES = [
        'FR',
        'BE',
        'DE',
        'NL',
        'LU',
        'ES',
        'GB',
        'PT',
        'AT',
        'EE',
        'LV',
        'LT',
        'CZ',
        'HU',
        'SK',
        'SI',
        'DK',
        'FI',
        'IE',
        'PL',
        'SE'
    ];

    /**
     * Colissimo relay address data field (on order entity).
     */
    const RELAY_ADDRESS_FAKE_ID = 'colissimo_frontpage_relay';

    /**
     * Colissimo relay address type.
     */
    const RELAY_ADDRESS_TYPE_MULTISHIPPING = 'lpc_fp';

    /**
     * Check if the carrier is enabled.
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->scopeConfig->getValue(self::PATH_CARRIER_IS_ACTIVE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the carrier title.
     *
     * @return string
     */
    public function getCarrierTitle()
    {
        return $this->scopeConfig->getValue(self::PATH_CARRIER_TITLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the carrier name.
     *
     * @return string
     */
    public function getCarrierName()
    {
        return $this->scopeConfig->getValue(self::PATH_CARRIER_NAME, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the widget url.
     *
     * @return string
     */
    public function getWidgetUrl()
    {
        return $this->scopeConfig->getValue(self::PATH_CARRIER_WIDGET_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the authentication webservice url.
     *
     * @return string
     */
    public function getAuthenticationWebserviceUrl()
    {
        return $this->scopeConfig->getValue(self::PATH_CARRIER_AUTH_WS_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the authentication webservice login.
     *
     * @return string
     */
    public function getAuthenticationWebserviceLogin()
    {
        return $this->scopeConfig->getValue(self::PATH_CARRIER_AUTH_WS_LOGIN, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the authentication webservice password.
     *
     * @return string
     */
    public function getAuthenticationWebservicePassword()
    {
        return $this->scopeConfig->getValue(self::PATH_CARRIER_AUTH_WS_PASSWORD, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the carrier shipping amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return (float) $this->scopeConfig->getValue(self::PATH_CARRIER_AMOUNT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the min quote price for free shipping.
     *
     * @return float
     */
    public function getMinQuotePriceForFree()
    {
        return (float) $this->scopeConfig
            ->getValue(self::PATH_CARRIER_MIN_QUOTE_PRICE_FOR_FREE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the max order total for this carrier.
     *
     * @return float
     */
    public function getMaxOrderTotal()
    {
        return (float) $this->scopeConfig->getValue(self::PATH_CARRIER_MAX_ORDER_TOTAL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the min order total for this carrier.
     *
     * @return float
     */
    public function getMinOrderTotal()
    {
        return (float) $this->scopeConfig->getValue(self::PATH_CARRIER_MIN_ORDER_TOTAL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the maximum weight this carrier.
     *
     * @return float
     */
    public function getMaxWeight()
    {
        return (float) $this->scopeConfig->getValue(self::PATH_CARRIER_MAX_WEIGHT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get available country list.
     *
     * @return array
     */
    public function getAvailableCountryList()
    {
        $allowSpecificCountry = (bool) $this->scopeConfig->getValue(
            self::PATH_CARRIER_ALLOW_SPECIFIC_COUNTRIES,
            ScopeInterface::SCOPE_STORE
        );

        $countryList = explode(
            ',',
            $allowSpecificCountry
                ? $this->scopeConfig->getValue(self::PATH_CARRIER_SPECIFIC_COUNTRIES, ScopeInterface::SCOPE_STORE)
                : $this->scopeConfig->getValue(self::GENERAL_AUTHORIZED_COUTRNIES, ScopeInterface::SCOPE_STORE)
        );

        return implode(',', array_intersect($countryList, self::SUPPORTED_COUNTRIES));
    }

    /**
     * Get the order prepare delay (in days).
     *
     * @return int
     */
    public function getOrderPrepareDelay()
    {
        return (int) $this->scopeConfig->getValue(self::PATH_CARRIER_ORDER_PREPARE_DELAY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the default country.
     *
     * @return string
     */
    public function getDefaultCountry()
    {
        return $this->scopeConfig->getValue(self::PATH_CARRIER_DEFAULT_COUNTRY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the default address.
     *
     * @return string
     */
    public function getDefaultAddress()
    {
        return $this->scopeConfig->getValue(self::PATH_CARRIER_DEFAULT_ADDRESS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the default postcode.
     *
     * @return string
     */
    public function getDefaultPostcode()
    {
        return $this->scopeConfig->getValue(self::PATH_CARRIER_DEFAULT_POSTCODE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get the default city.
     *
     * @return string
     */
    public function getDefaultCity()
    {
        return $this->scopeConfig->getValue(self::PATH_CARRIER_DEFAULT_CITY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get colissimo resource url.
     *
     * @return string
     */
    public function getColissimoResourceUrl()
    {
        return $this->scopeConfig->getValue(self::PATH_RESOURCE_URL, ScopeInterface::SCOPE_STORE);
    }
}

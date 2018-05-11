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
namespace LaPoste\ColissimoFrontPage\Block;

use LaPoste\ColissimoFrontPage\Helper\Authentication as AuthenticationHelper;
use LaPoste\ColissimoFrontPage\Helper\Config as ConfigHelper;
use Magento\Framework\Locale\Resolver as LocalResolver;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote\Address;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Colissimo FrontPage Widget Block.
 *
 * @author Smile (http://www.smile.fr)
 */
class FrontPage extends Template
{
    /** @var ConfigHelper */
    protected $configHelper;

    /** @var AuthenticationHelper */
    protected $authenticationHelper;

    /** @var LocalResolver */
    protected $localResolver;

    /** @var CheckoutSession */
    protected $checkoutSession;

    /**
     * @param Context $context
     * @param ConfigHelper $configHelper
     * @param AuthenticationHelper $authenticationHelper
     * @param LocalResolver $localResolver
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        AuthenticationHelper $authenticationHelper,
        LocalResolver $localResolver,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->authenticationHelper = $authenticationHelper;
        $this->localResolver = $localResolver;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get widget script url.
     *
     * @return string
     */
    public function getWidgetScriptUrl()
    {
        return $this->configHelper->getWidgetUrl();
    }

    /**
     * Get available country list.
     *
     * @return string
     */
    public function getCountryList()
    {
        return $this->configHelper->getAvailableCountryList();
    }

    /**
     * Get default country.
     *
     * @return string
     */
    public function getCountry()
    {
        $shippingAddress = $this->getDefaultShippingAddress();
        return $shippingAddress->getCountryId() ?: $this->configHelper->getDefaultCountry();
    }

    /**
     * Get default address.
     *
     * @return string
     */
    public function getAddress()
    {
        $shippingAddress = $this->getDefaultShippingAddress();
        return $shippingAddress->getStreet()
            ? implode(' ', $shippingAddress->getStreet())
            : $this->configHelper->getDefaultAddress();
    }

    /**
     * Get default postcode.
     *
     * @return string
     */
    public function getPostcode()
    {
        $shippingAddress = $this->getDefaultShippingAddress();
        return $shippingAddress->getPostcode() ?: $this->configHelper->getDefaultPostcode();
    }

    /**
     * Get default city.
     *
     * @return string
     */
    public function getCity()
    {
        $shippingAddress = $this->getDefaultShippingAddress();
        return $shippingAddress->getCity() ?: $this->configHelper->getDefaultCity();
    }

    /**
     * Get default shipping address.
     *
     * @return Address
     */
    protected function getDefaultShippingAddress()
    {
        if (!$this->hasData('default_shipping_address')) {
            $shippingAddress = $this->checkoutSession->getQuote()->getShippingAddress();
            if (!$shippingAddress->getCountryId()) {
                foreach ($this->checkoutSession->getQuote()->getCustomer()->getAddresses() as $address) {
                    if ($address->isDefaultShipping()) {
                        $shippingAddress = $address;
                        break;
                    }
                }
            }
            $this->setData('default_shipping_address', $shippingAddress);
        }
        return $this->getData('default_shipping_address');
    }

    /**
     * Get the order prepare delay (in days).
     *
     * @return string
     */
    public function getOrderPrepareDelay()
    {
        return $this->configHelper->getOrderPrepareDelay();
    }

    /**
     * Get weight of the current cart (in g).
     *
     * @return string
     */
    public function getPackageWeight()
    {
        $cartWeight = 0;
        foreach ($this->checkoutSession->getQuote()->getAllVisibleItems() as $item) {
            $cartWeight += ($item->getWeight() * 1000);
        }
        return $cartWeight;
    }

    /**
     * Get authetication token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->authenticationHelper->getAuthenticationToken();
    }

    /**
     * Get store language.
     *
     * @return string
     */
    public function getStoreLanguage()
    {
        $locale = $this->localResolver->getLocale();
        return $locale ? substr($locale, 0, 2) : '';
    }

    /**
     * Get colissimo resource url.
     *
     * @return string
     */
    public function getColissimoResourceUrl()
    {
        return $this->configHelper->getColissimoResourceUrl();
    }
}

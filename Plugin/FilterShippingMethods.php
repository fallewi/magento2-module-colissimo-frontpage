<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @rewrite to use Colissimoshipping address when it is possible
 *
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace LaPoste\ColissimoFrontPage\Plugin;

use LaPoste\ColissimoFrontPage\Helper\Config;
use LaPoste\ColissimoFrontPage\Model\Carrier;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Multishipping\Block\Checkout\Shipping;

/**
 * Plugin for filtering shipping method according to shipping address.
 *
 * @author Smile (http://www.smile.fr)
 */
class FilterShippingMethods
{
    /** @var Config */
    protected $helper;

    /**
     * @param Config $helper
     */
    public function __construct(
        Config $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Filter shipping method according to shipping address.
     *
     * @param Shipping $subject
     * @param callable $proceed
     * @param Address $address
     * @return array
     */
    public function aroundGetShippingRates(
        Shipping $subject,
        callable $proceed,
        Address $address
    ) {
        $methods = $proceed($address);
        if ($this->helper->isActive() && $subject->getRequest()->getModuleName() == 'multishipping') {
            $filteredMethods = [];
            foreach ($methods as $methodCode => $method) {
                if (
                    (
                        $address->getAddressType() == Config::RELAY_ADDRESS_TYPE_MULTISHIPPING
                        && $methodCode == Carrier::CODE
                    )
                    || (
                        $address->getAddressType() != Config::RELAY_ADDRESS_TYPE_MULTISHIPPING
                        && $methodCode != Carrier::CODE
                    )
                ) {
                    $filteredMethods[$methodCode] = $method;
                }
            }
            $methods = $filteredMethods;
        }
        return $methods;
    }
}

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
use Magento\Quote\Model\Quote\Address;
use Magento\Multishipping\Block\Checkout\Overview;

/**
 * Plugin to change edit address link for colissimo address.
 *
 * @author Smile (http://www.smile.fr)
 */
class EditAddressLink
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
     * Change edit address link for colissimo address.
     *
     * @param Overview $subject
     * @param callable $proceed
     * @param Address $address
     * @return string
     */
    public function aroundGetEditShippingAddressUrl(
        Overview $subject,
        callable $proceed,
        $address
    ) {
        $editUrl = $proceed($address);
        if ($this->helper->isActive() && $address->getAddressType() == Config::RELAY_ADDRESS_TYPE_MULTISHIPPING) {
            $editUrl = $subject->getEditShippingUrl();
        }
        return $editUrl;
    }
}

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
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

/**
 * Plugin add relay address in shipping address list.
 *
 * @author Smile (http://www.smile.fr)
 */
class AddRelayAddressAsShipping
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
     * Add relay address in shipping address list.
     *
     * @param Quote $subject
     * @param array $addresses
     * @return array
     */
    public function afterGetAllShippingAddresses(
        Quote $subject,
        $addresses
    ) {
        if ($this->helper->isActive()) {
            foreach ($subject->getAddressesCollection() as $address) {
                if ($address->getAddressType() == Config::RELAY_ADDRESS_TYPE_MULTISHIPPING && !$address->isDeleted()) {
                    $addresses[] = $address;
                }
            }
        }
        return $addresses;
    }
}

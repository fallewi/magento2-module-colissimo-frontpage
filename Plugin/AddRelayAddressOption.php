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
use Magento\Multishipping\Block\Checkout\Addresses;

/**
 * Plugin the addresses list in multi-shipping context.
 *
 * @author Smile (http://www.smile.fr)
 */
class AddRelayAddressOption
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
     * Add relay address option in multi-shipping address list.
     *
     * @param Addresses $subject
     * @param array $addresses
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAddressOptions(
        Addresses $subject,
        $addresses
    ) {
        if ($this->helper->isActive()) {
            $addresses[Config::RELAY_ADDRESS_FAKE_ID] = __('Relay point (chosen at next step)');
        }
        return $addresses;
    }
}

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
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Item;

/**
 * Plugin relay relay addresses.
 *
 * @author Smile (http://www.smile.fr)
 */
class QuoteAddress
{
    /** @var Config */
    protected $helper;

    /** @var Session */
    protected $checkoutSession;

    /**
     * @param Config $helper
     * @param Session $checkoutSession
     */
    public function __construct(
        Config $helper,
        Session $checkoutSession
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Relay relay addresses.
     *
     * @param Address $subject
     * @param callable $proceed
     * @param string $type
     * @return string
     */
    public function aroundFormat(
        Address $subject,
        callable $proceed,
        $type
    ) {
        $addressStr = $proceed($type);
        if ($this->helper->isActive() && $subject->getAddressType() == Config::RELAY_ADDRESS_TYPE_MULTISHIPPING) {
            // Check if a relay point is already set
            $colissimoRelayData = json_decode(
                $this->checkoutSession->getData('colissimofrontpage_shipping_data'),
                true
            );
            if (!is_array($colissimoRelayData)
                || !array_key_exists('relayId', $colissimoRelayData)
                || empty($colissimoRelayData['relayId'])
            ) {
                $addressStr = __(
                    'Colissimo: Please choose %1a relay point%2',
                    '<a id="no_relay_selected" class="action edit" href="#colissimo-frontpage-widget-container">',
                    '</a>'
                );
            }

            $addressStr .= '<span id="colissimo-relay-address"/>';
        }
        return $addressStr;
    }

    /**
     * Prevent magento to add all item in quote shipping address if they already are in relay address.
     * @see \Magento\Quote\Model\Quote\Address:566
     *
     * @param Address $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllItems(
        Address $subject,
        $result
    ) {
        if ($this->helper->isActive() && $subject->getAddressType() == 'shipping') {
            if (
                $subject->getItemsCollection()->count() == 0
                && $subject->getQuote()->getIsMultiShipping()
            ) {
                $result = [];
            }
        }

        return $result;
    }
}

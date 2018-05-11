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
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\AddressFactory;

/**
 * Plugin the addresses list in multi-shipping context.
 *
 * @author Smile (http://www.smile.fr)
 */
class AddShippingItem
{
    /** @var Config */
    protected $helper;

    /** @var AddressFactory */
    protected $addressFactory;

    /** @var EventManager */
    protected $eventManager;

    /**
     * @param Config $helper
     * @param AddressFactory $addressFactory
     * @param EventManager $eventManager
     */
    public function __construct(
        Config $helper,
        AddressFactory $addressFactory,
        EventManager $eventManager
    ) {
        $this->helper = $helper;
        $this->addressFactory = $addressFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * Add relay address as multi-shipping address.
     *
     * @param Multishipping $subject
     * @param callable $proceed
     * @param array $info
     * @return Multishipping
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetShippingItemsInformation(
        Multishipping $subject,
        callable $proceed,
        $info
    ) {
        $proceed($info);
        if ($this->helper->isActive()) {
            $needSave = false;
            foreach ($info as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    if ($data['address'] == Config::RELAY_ADDRESS_FAKE_ID) {
                        $this->addShippingItem($quoteItemId, $data, $subject);
                        $needSave = true;
                    }
                }
            }
            if ($needSave) {
                $subject->save();
            }
        }
        return $subject;
    }

    /**
     * Add relay shipping item.
     *
     * @param $quoteItemId
     * @param $data
     * @param Multishipping $multishipping
     * @throws LocalizedException
     */
    protected function addShippingItem($quoteItemId, $data, $multishipping)
    {
        $quote = $multishipping->getQuote();
        $qty = isset($data['qty']) ? (int)$data['qty'] : 1;
        $quoteItem = $quote->getItemById($quoteItemId);
        $defaultShippingAddressId = $quote->getCustomer()->getDefaultShipping();

        if ($quoteItem) {
            if ($qty === 0) {
                return;
            }
            $quoteItem->setMultishippingQty((int)$quoteItem->getMultishippingQty() + $qty);
            $quoteItem->setQty($quoteItem->getMultishippingQty());

            if (!$quoteAddress = $this->getColissimoAddress($quote)) {
                $defaultShippingAddress = $this->getCustomerAddressById(
                    $quote->getCustomer(),
                    $defaultShippingAddressId
                );
                $quoteAddress = $this->addressFactory->create()->importCustomerAddressData($defaultShippingAddress);
                $quoteAddress->setCustomerAddressId(null);
                $quote->addShippingAddress($quoteAddress);
                $quoteAddress->setAddressType(Config::RELAY_ADDRESS_TYPE_MULTISHIPPING);
            }

            $quoteAddressItem = $quoteAddress->getItemByQuoteItemId($quoteItemId);
            $quoteItem = $quote->getItemById($quoteItemId);
            if ($quoteAddressItem) {
                $quoteAddressItem->setQty((int)($quoteAddressItem->getQty() + $qty));
                // This address has been added after fist magento collect totals,
                // so magento compensated qty for item without address
                // we should cancel this action to get a correct item qty
                $quoteItem->setQty((int)($quoteItem->getQty() - $quoteAddressItem->getQty()));
            } else {
                $quoteAddress->addItem($quoteItem, $qty);
                // This address has been added after fist magento collect totals,
                // so magento compensated qty for item without address
                // we should cancel this action to get a correct item qty
                $quoteItem->setQty((int)($quoteItem->getQty() - $qty));
            }

            $quoteAddress->setCollectShippingRates(true);
            $quoteAddress->collectShippingRates();
        }
    }

    /**
     * Get customer address by id.
     *
     * @param Customer $customer
     * @param int $addressId
     * @return Address
     */
    protected function getCustomerAddressById($customer, $addressId)
    {
        /** @var \Magento\Customer\Model\Data\Address $address */
        foreach ($customer->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                return $address;
            }
        }
        return null;
    }

    /**
     *Get quote colissimo address.
     *
     * @param Quote $quote
     * @return Address
     */
    protected function getColissimoAddress($quote)
    {
        /** @var \Magento\Quote\Model\Quote\Address $address */
        foreach ($quote->getAddressesCollection() as $address) {
            if ($address->getAddressType() == Config::RELAY_ADDRESS_TYPE_MULTISHIPPING) {
                return $address;
            }
        }
        return null;
    }
}

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
namespace LaPoste\ColissimoFrontPage\Observer;

use LaPoste\ColissimoFrontPage\Helper\Config;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Update order with Colissimo Relay Address data.
 *
 * @author Smile (http://www.smile.fr)
 */
class AddColissimoDataToOrder implements ObserverInterface
{
    /** @var Session */
    protected $checkoutSession;

    /**
     * @param Session $checkoutSession
     */
    public function __construct(Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->getShippingMethod() === 'colissimofrontpage_colissimofrontpage') {
            $colissimoRelayData = json_decode(
                $this->checkoutSession->getData('colissimofrontpage_shipping_data'),
                true
            );
            
            if (is_array($colissimoRelayData)
                && array_key_exists('relayId', $colissimoRelayData)
                && $colissimoRelayData['relayId']
            ) {
                $colissimoDataJson = json_encode($colissimoRelayData, JSON_FORCE_OBJECT);
                $order->setData(Config::FIELD_COLISSIMO_RELAY_ADDRESS_DATA, $colissimoDataJson);
            }
        }
    }
}

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

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Plugin for the payment information management model.
 *
 * @author Smile (http://www.smile.fr)
 */
class ShippingInformationManagementPlugin
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
     * Display the exception message instead of a generic message when the exception was thrown by Colissimo.
     *
     * @param ShippingInformationManagement $shippingInformationManagement
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return array
     * @throws CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $shippingInformationManagement,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {

        if ($addressInformation->getShippingCarrierCode() == 'colissimofrontpage') {
            $extensionAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes();
            if ($extensionAttributes
                && ($colissimoRelayData = json_decode($extensionAttributes->getColissimoRelayData(), true))
                && is_array($colissimoRelayData)
                && array_key_exists('relayId', $colissimoRelayData)
                && $colissimoRelayData['relayId']
            ) {
                $this->checkoutSession->setData('colissimofrontpage_shipping_data', json_encode($colissimoRelayData));
            } else {
                throw new CouldNotSaveException(__('Colissimo: Please choose a relay point'));
            }
        } else {
            $this->checkoutSession->setData('colissimofrontpage_shipping_data', null);
        }

        return [$cartId, $addressInformation];
    }
}

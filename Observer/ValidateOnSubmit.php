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

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Exception\LocalizedException;

/**
 * Observer that validates the session data on the payment page.
 *
 * @author Smile (http://www.smile.fr)
 * @SuppressWarnings(PHPMD.ValidateOnSubmit)
 */
class ValidateOnSubmit implements ObserverInterface
{
    /** @var Session */
    protected $checkoutSession;

    /** @var ManagerInterface */
    protected $messageManager;

    /** @var bool */
    protected $errorOccurred = false;

    /** @var Phrase|null */
    protected $errorMessage;

    /**
     * @param Session $checkoutSession
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Session $checkoutSession,
        ManagerInterface $messageManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer = null)
    {
        $shippingAddress = $this->checkoutSession->getQuote()->getShippingAddress();
        if ($shippingAddress->getShippingMethod() !== 'colissimofrontpage_colissimofrontpage') {
            return;
        }

        // Copy phone number from billing address for relay address
        if ($billingAddress = $this->checkoutSession->getQuote()->getBillingAddress()) {
            $shippingAddress->setTelephone($billingAddress->getTelephone());
        }

        $colissimoRelayData = json_decode($this->checkoutSession->getData('colissimofrontpage_shipping_data'), true);

        if (!is_array($colissimoRelayData)
            || !array_key_exists('relayId', $colissimoRelayData)
            || empty($colissimoRelayData['relayId'])
        ) {
            $this->throwError(__('Colissimo: Please choose a relay point'));
        }
    }

    /**
     * Check whether the observer threw an exception.
     *
     * @param Phrase $message
     * @throws LocalizedException
     */
    protected function throwError(Phrase $message)
    {
        $this->errorOccurred = true;
        $this->errorMessage = $message;
        throw new LocalizedException($message);
    }

    /**
     * Check whether the observer threw an exception.
     *
     * @return bool
     */
    public function hasErrorOccurred()
    {
        return $this->errorOccurred;
    }

    /**
     * Get the exception message that was thrown.
     *
     * @return Phrase
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}

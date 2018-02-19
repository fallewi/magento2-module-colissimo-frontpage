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

/**
 * Clear colissimo shipping data in checkout session.
 *
 * @author Smile (http://www.smile.fr)
 */
class ClearCheckoutSession implements ObserverInterface
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer = null)
    {
        if ($this->checkoutSession) {
            $this->checkoutSession->unsetData('colissimofrontpage_shipping_data');
        }
    }
}

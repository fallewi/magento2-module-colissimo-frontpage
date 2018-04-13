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
use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Multishipping\Model\Cart\Controller\CartPlugin;

/**
 * Plugin reset relay id when back to cart.
 *
 * @author Smile (http://www.smile.fr)
 */
class ResetRelayId
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
     * Reset relay id when back to cart.
     *
     * @param CartPlugin $subject
     * @param Cart $cart
     * @param RequestInterface $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeBeforeDispatch(
        CartPlugin $subject,
        $cart,
        $request
    ) {
        if ($this->helper->isActive()) {
            $this->checkoutSession->setData('colissimofrontpage_shipping_data', null);
        }
        return [$cart, $request];
    }
}

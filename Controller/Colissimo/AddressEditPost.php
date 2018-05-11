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
namespace LaPoste\ColissimoFrontPage\Controller\Colissimo;

use LaPoste\ColissimoFrontPage\Helper\Config;
use Magento\Checkout\Controller\Action;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteRepository;

/**
 * Colissimo FrontPage Edit Colissimo Address Controller.
 *
 * @author Smile (http://www.smile.fr)
 */
class AddressEditPost extends Action
{
    /** @var CheckoutSession */
    protected $checkoutSession;
    
    /** @var QuoteRepository */
    protected $quoteRepository;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        CheckoutSession $checkoutSession,
        QuoteRepository $quoteRepository
    ) {
        parent::__construct($context, $customerSession, $customerRepository, $accountManagement);
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Multishipping checkout process posted addresses
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        $data = $this->_request->getParams();
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $relayPointCode = array_key_exists('identifiant', $data) ? $data['identifiant'] : '';
        unset($data['identifiant']);

        /** @var Address $address */
        foreach ($quote->getAddressesCollection() as $address) {
            if ($address->getAddressType() == Config::RELAY_ADDRESS_TYPE_MULTISHIPPING) {
                foreach ($data as $key => $value) {
                    $address->setData($key, $value);
                }
                $address->setData('middlename', '');
                $address->setData('company', '');
                $address->setData('region', '');
                $address->setData('region_id', '');
                $this->checkoutSession->setData(
                    'colissimofrontpage_shipping_data',
                    json_encode(['relayId' => $relayPointCode])
                );
            }
        }

        $this->quoteRepository->save($quote);
        $result->setData(['address_html' => $address->format('html')]);

        return $result;
    }
}

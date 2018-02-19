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
namespace LaPoste\ColissimoFrontPage\Model;

use LaPoste\ColissimoFrontPage\Helper\Config as ConfigHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Psr\Log\LoggerInterface;

/**
 * Shipping method.
 *
 * @author Smile (http://www.smile.fr)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Carrier extends AbstractCarrier implements CarrierInterface
{
    /**
     * Identifier code for current shipping method.
     */
    const CODE = 'colissimofrontpage';

    /** @var string */
    protected $_code = self::CODE;

    /** @var State */
    protected $appState;

    /** @var ResultFactory */
    protected $rateResultFactory;

    /** @var MethodFactory */
    protected $rateMethodFactory;

    /** @var StatusFactory */
    protected $trackResultFactory;

    /** @var Session */
    protected $checkoutSession;

    /** @var ConfigHelper */
    protected $configHelper;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param State $appState
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param StatusFactory $trackResultFactory
     * @param Session $checkoutSession
     * @param ConfigHelper $configHelper
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        State $appState,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        StatusFactory $trackResultFactory,
        Session $checkoutSession,
        ConfigHelper $configHelper,
        array $data = []
    ) {
        $this->appState = $appState;
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->trackResultFactory = $trackResultFactory;
        $this->checkoutSession = $checkoutSession;
        $this->configHelper = $configHelper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!$this->checkPriceRange($request)) {
            return false;
        }

        if (!$this->checkWeightLimit($request)) {
            return false;
        }

        if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML) {
            // Admin area: can't open js widget
            return false;
        }

        /** @var Result $result */
        $result = $this->rateResultFactory->create();

        /** @var Method $method */
        $method = $this->rateMethodFactory->create();
        $method->setData('carrier', $this->_code);
        $method->setData('carrier_title', $this->getConfigData('title'));
        $method->setData('method', $this->_code);
        $method->setData('method_title', $this->getConfigData('name'));

        $shippingPrice = $this->isFreeShipping($request)
            ? 0 :
            $this->getConfigData('amount');
        $method->setPrice($shippingPrice);
        $method->setData('cost', $shippingPrice);
        $result->append($method);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * {@inheritdoc}
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Get the tracking info (track number, carrier title).
     *
     * @param string $trackNumber
     * @return \Magento\Shipping\Model\Tracking\Result\Status
     */
    public function getTrackingInfo($trackNumber)
    {
        return $this->trackResultFactory->create()
            ->setCarrierTitle($this->getConfigData('title'))
            ->setTracking($trackNumber)
            ->setUrl('https://www.laposte.fr/particulier/outils/suivre-vos-envois?code=' . $trackNumber);
    }

    /**
     * Check if price range is valid.
     *
     * @param RateRequest $request
     * @return bool
     */
    protected function checkPriceRange(RateRequest $request)
    {
        $isPriceRangeValid = true;

        if (is_numeric($this->getConfigData('min_order_total'))
            && $request->getPackageValueWithDiscount() < $this->getConfigData('min_order_total')
        ) {
            $isPriceRangeValid = false;
        }

        if (is_numeric($this->getConfigData('max_order_total'))
            && $request->getPackageValueWithDiscount() > $this->getConfigData('max_order_total')
        ) {
            $isPriceRangeValid = false;
        }

        return $isPriceRangeValid;
    }

    /**
     * Check if weight limit is valid.
     *
     * @param RateRequest $request
     * @return bool
     */
    protected function checkWeightLimit(RateRequest $request)
    {
        $isWeightLimitValid = true;

        if (is_numeric($this->getConfigData('max_weight'))
            && $request->getPackageWeight() > $this->getConfigData('max_weight')
        ) {
            $isWeightLimitValid = false;
        }

        return $isWeightLimitValid;
    }

    /**
     * Check whether to apply free shipping.
     *
     * @param RateRequest $request
     * @return bool
     */
    protected function isFreeShipping(RateRequest $request)
    {
        $minQuotePriceForFreeShipping = $this->getConfigData('min_quote_price_for_free');
        $quotePriceWithDiscount = $request->getPackageValueWithDiscount();

        return $request->getFreeShipping() === true
            || $request->getPackageQty() == $this->getFreeBoxesCount($request)
            || ($minQuotePriceForFreeShipping > 0 && $quotePriceWithDiscount >= $minQuotePriceForFreeShipping);
    }

    /**
     * Get the number of items with free shipping.
     *
     * @param RateRequest $request
     * @return int
     */
    protected function getFreeBoxesCount(RateRequest $request)
    {
        $freeBoxes = 0;
        $items = $request->getAllItems();

        if ($items) {
            foreach ($items as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    $freeBoxes += $this->getFreeBoxesCountFromChildren($item);
                } elseif ($item->getFreeShipping()) {
                    $freeBoxes += $item->getQty();
                }
            }
        }

        return $freeBoxes;
    }

    /**
     * Get the number of children items with free shipping.
     *
     * @param mixed $item
     * @return int
     */
    protected function getFreeBoxesCountFromChildren($item)
    {
        $freeBoxes = 0;

        foreach ($item->getChildren() as $child) {
            if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                $freeBoxes += $item->getQty() * $child->getQty();
            }
        }

        return $freeBoxes;
    }
}

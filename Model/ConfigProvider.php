<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model;

use MageRocket\GoCuotas\Helper\Data as GoCuotasHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'gocuotas';

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var GoCuotasHelper $goCuotasHelper
     */
    protected GoCuotasHelper $goCuotasHelper;

    /**
     * @param GoCuotasHelper $goCuotasHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GoCuotasHelper $goCuotasHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->goCuotasHelper = $goCuotasHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $paymentMode = $this->goCuotasHelper->getPaymentMode($storeId);
        $paymentModeString = $paymentMode ? 'modal' : 'redirect';
        return [
            'payment' => [
                self::CODE => [
                    'payment_mode' => $paymentMode,
                    'payment_logo' => $this->goCuotasHelper->getLogo(),
                    'payment_active' => $this->goCuotasHelper->isActive($storeId),
                    'payment_banner' => $this->goCuotasHelper->getBanner($storeId),
                    'payment_url' => $this->goCuotasHelper->getCreatePreferenceURL(),
                    'payment_title' => $this->goCuotasHelper->getPaymentTitle($storeId),
                    'payment_icon' => $this->goCuotasHelper->getPaymentIcon($paymentModeString),
                    'payment_showBanner' => $this->goCuotasHelper->getShowCheckoutBanner($storeId),
                    'payment_description' => $this->goCuotasHelper->getPaymentDescription($storeId),
                    'payment_instructions' => __(
                        $this->goCuotasHelper->getPaymentInstructions($storeId, $paymentModeString)
                    ),
                    'payment_checkout_success' => $this->goCuotasHelper->getSuccessURL(),
                    'payment_checkout_failure' => $this->goCuotasHelper->getFailureURL()
                ]
            ],
        ];
    }
}

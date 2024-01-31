<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\ViewModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use MageRocket\GoCuotas\Helper\Data;

class AbstractWidget
{
    /**
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * @var PriceHelper $priceHelper
     */
    protected PriceHelper $priceHelper;

    /**
     * @param Data $helper
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        Data $helper,
        PriceHelper $priceHelper
    ) {
        $this->helper = $helper;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Get InstallmentsQty
     *
     * @param int|null $storeId
     * @return int
     */
    public function getInstallmentsQty($storeId = null)
    {
        return $this->helper->getWidgetInstallmentsQty($storeId);
    }

    /**
     * Get InstallmentsLabel
     *
     * @param string $path
     * @param int|null $storeId
     * @return string
     */
    public function getInstallmentsLabel(string $path = 'pdp_installments_message', $storeId = null)
    {
        return $this->helper->getInstallmentsLabel($path, $storeId);
    }

    /**
     * Get Go Cuotas Logo
     *
     * @param int|null $storeId
     * @return string
     */
    public function getGoCuotasLogo($storeId = null)
    {
        return $this->helper->getLogo();
    }

    /**
     * Get InstallmentsAmount
     *
     * @param string|float $amount
     * @param int|null $storeId
     * @return float|string
     */
    public function getInstallmentsAmount($amount, $storeId = null)
    {
        $maxInstallments = $this->getInstallmentsQty($storeId);
        $installmentsAmount = ($amount / $maxInstallments);
        return $this->priceHelper->currency($installmentsAmount, true, false);
    }
}

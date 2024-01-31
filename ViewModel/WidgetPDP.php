<?php

/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\ViewModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class WidgetPDP extends AbstractWidget implements ArgumentInterface
{
    /**
     * Get PDPWidget
     *
     * @param ProductInterface $product
     * @param int|null $storeId
     * @return Phrase
     */
    public function getPDPWidget($product, $storeId = null)
    {
        $productPrice = $product->getFinalPrice();
        $installmentsAmount = $this->getInstallmentsAmount($productPrice, $storeId);
        $installmentsLabel = $this->getInstallmentsLabel('pdp_installments_message', $storeId);
        $maxInstallments = $this->getInstallmentsQty($storeId);
        return __($installmentsLabel, $maxInstallments, $installmentsAmount);
    }
}

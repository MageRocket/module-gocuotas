<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CheckoutModeOption implements OptionSourceInterface
{

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['label' => __('Modal'), 'value' => 1],
            ['label' => __('Redirect'), 'value' => 0],
        ];
    }
}

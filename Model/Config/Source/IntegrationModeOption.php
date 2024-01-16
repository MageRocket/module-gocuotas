<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IntegrationModeOption implements OptionSourceInterface
{

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['label' => __('Production'), 'value' => 1],
            ['label' => __('Sandbox'), 'value' => 0],
        ];
    }
}

<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2026 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Api;

use Magento\Framework\Webapi\Exception;

interface WebhookInterface
{
    /**
     * Webhook updateStatus
     *
     * @param string $token
     * @param string $status
     * @param string|null $order_id
     * @param string $order_reference_id
     * @param string|null $number_of_installments
     * @return array
     * @throws Exception
     */
    public function updateStatus(
        string $token,
        string $status,
        ?string $order_id = null,
        string $order_reference_id = '',
        ?string $number_of_installments = null
    ): array;
}

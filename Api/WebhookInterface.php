<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
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
     * @param string $order_id
     * @param string $order_reference_id
     * @param string $number_of_installments
     * @return array
     * @throws Exception
     */
    public function updateStatus(
        string $token,
        string $status,
        string $order_id,
        string $order_reference_id,
        string $number_of_installments
    ): array;
}

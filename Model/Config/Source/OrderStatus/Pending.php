<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model\Config\Source\OrderStatus;

use Magento\Sales\Model\Config\Source\Order\Status;
use Magento\Sales\Model\Order;

class Pending extends Status
{
    /**
     * Return Status Pending Payment
     * @var $_stateStatuses
     */
    protected $_stateStatuses = [
        Order::STATE_PENDING_PAYMENT,
    ];
}

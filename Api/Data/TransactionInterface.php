<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Api\Data;

interface TransactionInterface
{
    public const ENTITY_ID = 'entity_id';
    public const ORDER_ID = 'order_id';
    public const INCREMENT_ID = 'increment_id';
    public const STATUS = 'status';
    public const CREATED_AT = 'created_at';
    public const EXPIRED_AT = 'expired_at';
    public const TRANSACTION_ID = 'transaction_id';

    /**
     * Get transaction_id
     *
     * @return string|null
     */
    public function getTransactionId();

    /**
     * Set transaction_id
     *
     * @param string $transactionId
     * @return TransactionInterface
     */
    public function setTransactionId($transactionId);

    /**
     * Get OrderId
     *
     * @return int|string|null
     */
    public function getOrderId();

    /**
     * Set OrderId
     *
     * @param int $orderId
     * @return mixed
     */
    public function setOrderId($orderId);

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param string $status
     * @return mixed
     */
    public function setStatus($status);

    /**
     * Get CreatedAt
     *
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return mixed
     */
    public function setCreatedAt($createdAt);

    /**
     * Get ExpiredAt
     *
     * @return mixed
     */
    public function getExpiredAt();

    /**
     * Set ExpiredAt
     *
     * @param string $expiredAt
     * @return mixed
     */
    public function setExpiredAt($expiredAt);

    /**
     * Get IncrementId
     *
     * @return mixed
     */
    public function getIncrementId();

    /**
     * Set IncrementId
     *
     * @param string|int $incrementId
     * @return mixed
     */
    public function setIncrementId($incrementId);
}

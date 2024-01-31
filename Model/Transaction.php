<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model;

use MageRocket\GoCuotas\Api\Data\TransactionInterface;
use Magento\Framework\Model\AbstractModel;

class Transaction extends AbstractModel implements TransactionInterface
{

    /**
     * Init Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Transaction::class);
    }

    /**
     * Get TransactionId
     *
     * @return int|string|null
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * Set TransactionId
     *
     * @param int|string $transactionId
     * @return TransactionInterface|Transaction
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * Get OrderId
     *
     * @return array|int|mixed|string|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set OrderId
     *
     * @param int|string $orderId
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get Status
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get CreatedAt
     *
     * @return array|mixed|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return Transaction|mixed
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get ExpiredAt
     *
     * @return array|mixed|null
     */
    public function getExpiredAt()
    {
        return $this->getData(self::EXPIRED_AT);
    }

    /**
     * Set ExpiredAt
     *
     * @param string $expiredAt
     * @return Transaction|mixed
     */
    public function setExpiredAt($expiredAt)
    {
        return $this->setData(self::EXPIRED_AT, $expiredAt);
    }

    /**
     * Get incrementId
     *
     * @return mixed
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * Set IncrementId
     *
     * @param int|string $incrementId
     * @return mixed
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }
}

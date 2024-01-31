<?php

/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Cron;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MageRocket\GoCuotas\Api\TransactionRepositoryInterface;
use MageRocket\GoCuotas\Helper\Data;
use MageRocket\GoCuotas\Model\GoCuotas;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\Order;

class CancelOrder
{
    protected const PENDING = ['pending'];
    protected const PAYMENT_METHOD = ['gocuotas'];
    protected const TRANSACTION_CANCELED = 'canceled';

    /**
     * @var SearchCriteriaBuilder $searchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var TransactionRepositoryInterface $transactionRepository
     */
    protected TransactionRepositoryInterface $transactionRepository;

    /**
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var GoCuotas $goCuotas
     */
    protected GoCuotas $goCuotas;

    /**
     * @var TimezoneInterface $timezone
     */
    protected TimezoneInterface $timezone;

    /**
     * @var DateTime $dateTime
     */
    protected DateTime $dateTime;

    /**
     * @var OrderCollection $orderCollection
     */
    protected OrderCollection $orderCollection;

    /**
     * Init Construct
     *
     * @param Data $helper
     * @param GoCuotas $goCuotas
     * @param OrderCollection $orderCollection
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        Data $helper,
        GoCuotas $goCuotas,
        OrderCollection $orderCollection,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->helper = $helper;
        $this->goCuotas = $goCuotas;
        $this->orderCollection = $orderCollection;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * CancelPending
     *
     * @return void
     */
    public function cancelPending()
    {
        $collection = $this->getOrderCollection(self::PENDING, $this->getCreatedAt());
        foreach ($collection as $order) {
            if ($order->getState() !== Order::STATE_NEW) {
                continue;
            }
            $hasTransaction = $this->getTransaction($order->getId());
            $cancelMessage = __(
                'Go Cuotas: Order canceled. Payment expiration time (%1 minutes)',
                Data::GOCUOTAS_PAYMENT_EXPIRATION
            );
            $this->goCuotas->cancelOrder($order, $hasTransaction, $cancelMessage);
        }
    }

    /**
     * Get Transaction
     *
     * @param int|string $orderId
     * @return bool
     */
    private function getTransaction($orderId)
    {
        try {
            $transaction = $this->transactionRepository->getByOrderId($orderId);
            if ($transaction) {
                return $transaction->getOrderId();
            }
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * Get OrderCollection
     *
     * @param array $status
     * @param string $createdAt
     * @return OrderCollection
     */
    private function getOrderCollection($status, string $createdAt): OrderCollection
    {
        $this->orderCollection->getSelect()
            ->joinLeft(
                ["sop" => "sales_order_payment"],
                'main_table.entity_id = sop.parent_id',
                ['method']
            )->where('sop.last_trans_id IS NULL');
        $this->orderCollection->addFieldToFilter('sop.method', ['in' => self::PAYMENT_METHOD])
            ->addFieldToFilter('main_table.status', ['in' => $status])
            ->addFieldToFilter('main_table.created_at', ['lteq' => $createdAt])
            ->setOrder('main_table.created_at', 'ASC');
        return $this->orderCollection;
    }

    /**
     * Retrieve formatted locale date
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        $timeInterval = Data::GOCUOTAS_PAYMENT_EXPIRATION;
        $prevDate = date_create(date('Y-m-d H:i:s', strtotime("-{$timeInterval} min")));
        return $prevDate->format('Y-m-d H:i:s');
    }
}

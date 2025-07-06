<?php

/**
 * @author MageRocket
 * @copyright Copyright (c) 2025 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Cron;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MageRocket\GoCuotas\Api\Data\TransactionInterface;
use MageRocket\GoCuotas\Api\TransactionRepositoryInterface;
use MageRocket\GoCuotas\Helper\Data;
use MageRocket\GoCuotas\Model\GoCuotas;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\Order;

class CancelOrder
{
    protected const PENDING = ['pending'];
    protected const PAYMENT_METHOD = ['gocuotas'];
    protected const TRANSACTION_CANCELED = 'canceled';

    /**
     * @var TransactionRepositoryInterface $transactionRepository
     */
    protected TransactionRepositoryInterface $transactionRepository;

    /**
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * @var GoCuotas $goCuotas
     */
    protected GoCuotas $goCuotas;

    /**
     * @var TimezoneInterface $timezone
     */
    protected TimezoneInterface $timezone;

    /**
     * @var OrderCollection $orderCollection
     */
    protected OrderCollection $orderCollection;

    /**
     * @var $cronCancellationTimeout int|string
     */
    protected $cronCancellationTimeout;

    /**
     * Init Construct
     *
     * @param Data $helper
     * @param GoCuotas $goCuotas
     * @param TimezoneInterface $timezone
     * @param OrderCollection $orderCollection
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        Data $helper,
        GoCuotas $goCuotas,
        TimezoneInterface $timezone,
        OrderCollection $orderCollection,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->helper = $helper;
        $this->goCuotas = $goCuotas;
        $this->timezone = $timezone;
        $this->orderCollection = $orderCollection;
        $this->transactionRepository = $transactionRepository;
        $this->cronCancellationTimeout = $this->helper->getCronOrderTimeout() ?? Data::GOCUOTAS_PAYMENT_EXPIRATION;
    }

    /**
     * CancelPending
     *
     * @return void
     * @throws \Exception
     */
    public function cancelPending()
    {
        try {
            $collection = $this->getOrderCollection(self::PENDING, $this->getCreatedAt());
            if(count($collection) > 0){
                foreach ($collection as $order) {
                    if ($order->getState() !== Order::STATE_NEW ||
                        strtotime($order->getCreatedAt()) > strtotime('-' . $this->cronCancellationTimeout . ' minutes')
                    ) {
                        continue;
                    }

                    // Get Transaction Data
                    $orderTransaction = $this->getTransaction($order->getId());

                    /**
                     * If the transactionId is null, I proceed to query the GoCuotas API to check if there is any payment that was not reported to Magento
                     */
                    if ($orderTransaction->getTransactionId() === null) {
                        // We retrieve all payments generated so far based on the current time minus the cancellation minutes.
                        $searchTransactionDateStart = $this->timezone->date()->modify('-' . ($this->cronCancellationTimeout + 10) . ' minute')->format('Y-m-d H:i');
                        $searchTransactionDateEnd = $this->timezone->date()->format('Y-m-d H:i');

                        // Request GoCuotas API
                        $this->helper->logDebug("GoCuotas Cron: Search Payment - Order External ID: #{$order->getIncrementId()} - Date From: $searchTransactionDateStart - To: $searchTransactionDateEnd");
                        $searchGoCuotasTransactions = $this->goCuotas->searchOrders(
                            $order->getStoreId(),
                            $searchTransactionDateStart,
                            $searchTransactionDateEnd
                        );

                        $orderTransaction['transaction_id'] = false;
                        if (count($searchGoCuotasTransactions) > 0) {
                            // Search Transaction by Order Reference ID
                            $searchOrder = array_filter($searchGoCuotasTransactions, function ($transaction) use ($order) {
                                return $transaction['order_reference_id'] === $order->getIncrementId();
                            });

                            // Order Payment Found?
                            if(count($searchOrder) > 0) {
                                $orderGoCuotasTransaction = current($searchOrder);
                                $orderTransaction['transaction_id'] = $orderGoCuotasTransaction['id'];
                            } else {
                                $this->helper->logDebug("GoCuotas Cron: No Payment found. Order Reference ID: #{$order->getIncrementId()}");
                            }
                        } else {
                            $this->helper->logDebug("GoCuotas Cron: No Transactions found. Order Reference ID: #{$order->getIncrementId()}");
                        }
                    }

                    // Get GoCuotas Transaction Data
                    $goCuotasTransaction = $this->goCuotas->getGoCuotasTransaction($order, $orderTransaction['transaction_id']);
                    if (isset($goCuotasTransaction['status']) && $goCuotasTransaction['status'] !== 'approved') {
                        $cancelData = [
                            'status' => 'Cancel',
                            'external_reference' => $order->getIncrementId(),
                            'reason' => __('Payment expiration time (%1 minutes)', $this->cronCancellationTimeout),
                        ];
                        $this->goCuotas->cancelOrder($order, $orderTransaction->getIncrementId(), $cancelData);
                    } else {
                        // Approved Transaction
                        $additionalData = [
                            'id' => $goCuotasTransaction['id'],
                            'status' => $goCuotasTransaction['status'],
                            'installments' => $goCuotasTransaction['number_of_installments'],
                            'external_reference' => $goCuotasTransaction['order_reference_id'],
                            'method' => $this->helper->getPaymentMode($order->getStoreId()) ? 'Modal' : 'Redirect',
                        ];

                        // Get Transaction Payment Card Data
                        if ($goCuotasTransaction['payment'] !== null) {
                            $cardData = $goCuotasTransaction['payment']['card'];
                            $additionalData['card_number'] = $cardData['number'] ?: 'N/A';
                            $additionalData['card_name'] = $cardData['name'] ?: 'N/A';
                        }

                        // Invoice Order
                        $this->goCuotas->invoiceOrder($order, $goCuotasTransaction['id'], $additionalData);
                    }
                }
            } else {
                $this->helper->logDebug('GoCuotas Cron: NO PENDING ORDERS');
            }
        } catch (\Exception $e) {
            $this->helper->log('GOCUOTAS CRON ERROR: ' . $e->getMessage());
        }
    }

    /**
     * Get Transaction
     *
     * @param $orderId
     * @return TransactionInterface|bool
     */
    private function getTransaction($orderId)
    {
        try {
            $transaction = $this->transactionRepository->getByOrderId($orderId);
            if ($transaction) {
                return $transaction;
            }
        } catch (LocalizedException $e) {
            $this->helper->log("Cron ERROR - OrderID: $orderId: " . $e->getMessage());
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
    private function getOrderCollection(array $status, string $createdAt): OrderCollection
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
        $timeInterval = $this->cronCancellationTimeout;
        $prevDate = date_create(date('Y-m-d H:i:s', strtotime("-{$timeInterval} min")));
        return $prevDate->format('Y-m-d H:i:s');
    }
}

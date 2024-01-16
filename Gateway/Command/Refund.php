<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Gateway\Command;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderRepository;
use MageRocket\GoCuotas\Helper\Data;
use MageRocket\GoCuotas\Model\TransactionRepository;
use MageRocket\GoCuotas\Model\GoCuotas;
use Magento\Framework\Serialize\SerializerInterface as Json;

class Refund implements CommandInterface
{

    /**
     * @var OrderRepository $orderRepository
     */
    protected OrderRepository $orderRepository;

    /**
     * @var TransactionRepository $transactionRepository
     */
    protected TransactionRepository $transactionRepository;

    /**
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * @var GoCuotas $goCuotas
     */
    protected GoCuotas $goCuotas;

    /**
     * @var MessageManagerInterface $messageManager
     */
    protected MessageManagerInterface $messageManager;

    /**
     * @var Json $jsonSerializer
     */
    protected Json $jsonSerializer;

    /**
     * @param Data $helper
     * @param GoCuotas $goCuotas
     * @param Json $jsonSerializer
     * @param OrderRepository $orderRepository
     * @param MessageManagerInterface $messageManager
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(
        Data $helper,
        GoCuotas $goCuotas,
        Json $jsonSerializer,
        OrderRepository $orderRepository,
        MessageManagerInterface $messageManager,
        TransactionRepository $transactionRepository
    ) {
        $this->helper = $helper;
        $this->goCuotas = $goCuotas;
        $this->jsonSerializer = $jsonSerializer;
        $this->messageManager = $messageManager;
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Execute
     *
     * @param array $commandSubject
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function execute(array $commandSubject)
    {
        /** @var Payment $payment */
        $payment = $commandSubject['payment'];
        $amount = $commandSubject['amount'];
        $orderId = $payment->getPayment()->getParentId();

        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);

        // Refund enabled?
        if (!$this->helper->isRefundEnabled($order->getStoreId())) {
            $this->messageManager->addWarningMessage(__('Go Cuotas: Refund disabled'));
            $order->addCommentToStatusHistory(__('Go Cuotas: Refund disabled'));
            $this->orderRepository->save($order);
            return;
        }

        // Validate Order Days
        $maxDaysToRefund = $this->helper->getRefundMaximumDays($order->getStoreId());
        if (strtotime($order->getCreatedAt()) < strtotime("-$maxDaysToRefund days")) {
            $this->messageManager->addWarningMessage(
                __('Go Cuotas: Maximum time to make refund exceeded %1 days', $maxDaysToRefund)
            );
            $order->addCommentToStatusHistory(
                __('Go Cuotas: Maximum time to make refund exceeded %1 days', $maxDaysToRefund)
            );
            $this->orderRepository->save($order);
            return;
        }

        // Validate Previous Refund
        $creditMemoCollectionSize = $order->getCreditmemosCollection()->getSize();
        if ($creditMemoCollectionSize >= 1) {
            $this->messageManager->addWarningMessage(__('Go Cuotas: Payment can be refunded only once'));
            $order->addCommentToStatusHistory(__('Go Cuotas: Payment can be refunded only once'));
            $this->orderRepository->save($order);
            return;
        }

        try {
            $transaction = $this->transactionRepository->getByOrderId($order->getId());
            $transactionId = $transaction->getTransactionId();

            if ($transaction->getStatus() === 'approved' && isset($transactionId)) {
                // The amount must be reported only when making a partial refund
                $amountRefund = (round($order->getGrandTotal(), 2) == $amount) ? null : $amount;
                // Create Refund
                $refundResult = $this->goCuotas->createRefund($order, $transactionId, $amountRefund);
                $orderHistory = $this->getRefundAdditionalInformation($refundResult);
                $order->addCommentToStatusHistory($orderHistory);
                $this->orderRepository->save($order);
            }
        } catch (\Exception $e) {
            $this->helper->log('Refund ERROR: ' . $e->getMessage());
            $order->addCommentToStatusHistory("Go Cuotas Refund ERROR: " . $e->getMessage());
            $this->orderRepository->save($order);
            return;
        }
    }

    /**
     * Get RefundAdditionalInformation
     *
     * @param $refundData
     * @return string
     */
    private function getRefundAdditionalInformation($refundData)
    {
        $historyFormatted = __("<b>Go Cuotas Payment Refund Information</b>") . "<br>";
        if (is_array($refundData) && count($refundData) > 0) {
            // Refund ID
            $historyFormatted .= __(
                "Refund ID: <b>%1</b>",
                $refundData[0]["id"] ?? 'N/A'
            ) . "<br>";
            // Refund Status
            $historyFormatted .= __(
                "Refund Status: <b>%1</b>",
                ucfirst($refundData[0]["status"]) ?? 'N/A'
            ) . "<br>";
        } else {
            $historyFormatted .= __('An error occurred while processing your refund information');
        }
        return $historyFormatted;
    }
}

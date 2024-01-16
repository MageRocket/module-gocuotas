<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Plugin\Order;

use Magento\Framework\Exception\LocalizedException;
use MageRocket\GoCuotas\Helper\Data;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use MageRocket\GoCuotas\Model\TransactionRepository;

class EmailOrderSender
{
    /**
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * @var TransactionRepository $transactionRepository
     */
    protected TransactionRepository $transactionRepository;

    /**
     * @param Data $helper
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(
        Data $helper,
        TransactionRepository $transactionRepository
    ) {
        $this->helper = $helper;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Around Send
     *
     * @param OrderSender $subject
     * @param callable $proceed
     * @param Order $order
     * @param bool $forceSyncMode
     * @return bool
     */
    public function aroundSend(OrderSender $subject, callable $proceed, Order $order, bool $forceSyncMode = false): bool
    {
        $paymentMethod = $order->getPayment()->getMethod();
        if (!$forceSyncMode) {
            try {
                $goCuotasPaymentMethods = array_keys(Data::GOCUOTAS_PAYMENT_METHODS);
                if (in_array($paymentMethod, $goCuotasPaymentMethods)) {
                    $transaction = $this->transactionRepository->getByOrderIncrementId($order->getIncrementId());
                    $status = $transaction->getStatus();
                    if ($status != 'approved') {
                        return false;
                    }
                }
            } catch (LocalizedException $e) {
                /**
                 * Possibly it is the purchase confirmation email.
                 * Since the transaction does not exist, we do not trigger it.
                 */
                return false;
            }
        }
        return $proceed($order, $forceSyncMode);
    }
}

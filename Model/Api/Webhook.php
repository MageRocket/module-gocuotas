<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model\Api;

use MageRocket\GoCuotas\Api\WebhookInterface;
use MageRocket\GoCuotas\Helper\Data;
use MageRocket\GoCuotas\Model\GoCuotas;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;

class Webhook implements WebhookInterface
{
    protected const ORDER_STATUS_PENDING = 'pending';

    /**
     * @var GoCuotas $goCuotas
     */
    protected GoCuotas $goCuotas;

    /**
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * @var Request $request
     */
    protected Request $request;

    /**
     * @param Data $helper
     * @param GoCuotas $goCuotas
     * @param Request $request
     */
    public function __construct(
        Data $helper,
        GoCuotas $goCuotas,
        Request $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->goCuotas = $goCuotas;
    }

    /**
     * UpdateStatus
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
    ): array {
        // Get Transaction
        $transaction = $this->goCuotas->getTransactionByExternalReference($order_reference_id);
        if ($transaction === null) {
            throw new \Magento\Framework\Webapi\Exception(__('The requested transaction does not exist'));
        }

        // Get Order Data
        $order = $this->goCuotas->getOrder($transaction->getOrderId());
        if ($order === null) {
            throw new \Magento\Framework\Webapi\Exception(__('The requested order does not exist'));
        }

        // Validate Token
        $generatedToken = $this->helper->generateToken($order);
        if ($generatedToken !== $token) {
            $this->helper->log('Webhook Invalid Token: Order #' . $order->getId() .
                ' - Webhook Token: ' . $token . ' - Generated Token: ' . $generatedToken);
            throw new \Magento\Framework\Webapi\Exception(__('Webhook invalid token'));
        }

        // Check Order Payment Method
        $goCuotasPaymentMethods = array_keys(Data::GOCUOTAS_PAYMENT_METHODS);
        if (!in_array($order->getPayment()->getMethod(), $goCuotasPaymentMethods)) {
            $this->helper->log('The order was not paid with Go Cuotas');
            throw new \Magento\Framework\Webapi\Exception(__('The order was not paid with Go Cuotas'));
        }

        // Check Order status
        if ($order->getStatus() !== self::ORDER_STATUS_PENDING) {
            $this->helper->log("Webhook NOT Processed: Order #{$order->getId()} - Status: {$order->getStatus()}");
            throw new \Magento\Framework\Webapi\Exception(
                __('The status of the order does not allow it to be updated')
            );
        }

        // Invoice Order
        $goCuotasTransactionID = $order_id;
        $additionalData = [
            'id' => $order_id,
            'status' => $status,
            'installments' => $number_of_installments,
            'external_reference' => $order_reference_id,
            'method' => $this->helper->getPaymentMode($order->getStoreId()) ? 'Modal' : 'Redirect'
        ];

        // Get Transaction Payment Card Data
        $goCuotasTransaction = $this->goCuotas->getGoCuotasTransaction($order, $goCuotasTransactionID);
        if ($goCuotasTransaction['payment'] !== null) {
            $cardData = $goCuotasTransaction['payment']['card'];
            $additionalData['card_number'] = $cardData['number'] ?: 'N/A';
            $additionalData['card_name'] = $cardData['name'] ?: 'N/A';
        }

        // Process Payment
        switch (strtolower($status)) {
            case 'approved':
                if (!$this->goCuotas->invoiceOrder($order, $goCuotasTransactionID, $additionalData)) {
                    throw new \Magento\Framework\Webapi\Exception(__('Order could not be invoiced.'));
                }
                $response = ['error' => false, 'status' => 'Order Approved'];
                break;
            case 'denied':
                if (!$this->goCuotas->cancelOrder($order, $goCuotasTransactionID, $additionalData)) {
                    throw new \Magento\Framework\Webapi\Exception(__('Order could not be canceled.'));
                }
                $response = ['error' => false, 'status' => 'Order Canceled'];
                break;
            default:
                // Payment Pending
                $response = ['error' => false, 'status' => 'Pending Payment'];
                break;
        }

        return [$response];
    }
}

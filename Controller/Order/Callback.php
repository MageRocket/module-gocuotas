<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Controller\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use MageRocket\GoCuotas\Helper\Data;
use MageRocket\GoCuotas\Model\GoCuotas;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Webapi\Exception;
use MageRocket\GoCuotas\Model\TransactionRepository;

class Callback implements ActionInterface
{
    /**
     * @var RequestInterface $request
     */
    protected RequestInterface $request;

    /**
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * @var RedirectFactory $redirectFactory
     */
    protected RedirectFactory $redirectFactory;

    /**
     * @var Session $session
     */
    protected Session $session;

    /**
     * @var GoCuotas $goCuotas
     */
    protected GoCuotas $goCuotas;

    /**
     * @var TransactionRepository $transactionRepository
     */
    protected TransactionRepository $transactionRepository;

    /**
     * Init Construct
     *
     * @param Data $helper
     * @param Session $session
     * @param GoCuotas $goCuotas
     * @param RequestInterface $request
     * @param RedirectFactory $redirectFactory
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(
        Data $helper,
        Session $session,
        GoCuotas $goCuotas,
        RequestInterface $request,
        RedirectFactory $redirectFactory,
        TransactionRepository $transactionRepository
    ) {
        $this->helper = $helper;
        $this->session = $session;
        $this->request = $request;
        $this->goCuotas = $goCuotas;
        $this->redirectFactory = $redirectFactory;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Execute
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->redirectFactory->create();
        $order = $this->session->getLastRealOrder();
        $path = 'checkout/onepage/failure';
        try {
            // Check Payment Method
            $goCuotasPaymentMethods = array_keys(Data::GOCUOTAS_PAYMENT_METHODS);
            if (!in_array($order->getPayment()->getMethod(), $goCuotasPaymentMethods)) {
                throw new Exception(__("The order #{$order->getIncrementId()} was not paid with Go Cuotas"));
            }

            // Get Transaction
            $transaction = $this->transactionRepository->getByOrderId($order->getId());

            // Check Transaction Status (Update by Webhooks)
            if ($transaction->getStatus() == "approved") {
                // Go to Success Page
                $path = 'checkout/onepage/success';
            } elseif ($transaction->getStatus() == "denied") {
                // Denied from Webhook
                $this->session->setErrorMessage(__('An error occurred while processing your payment.'));
            } else {
                // Check Status from Callback
                if ($order->getStatus() !== 'pending') {
                    throw new Exception(
                        __("The status of the order does not allow it to be updated." .
                            " Order #{$order->getId()} - Status: {$order->getStatus()}")
                    );
                }

                // Get Token
                $callbackToken = $this->request->getParam('token');
                if ($callbackToken === null) {
                    throw new Exception(__("The order #{$order->getIncrementId()} missing token callback"));
                }

                // Prepare Additional Data
                $additionalData = [
                    'method' => 'redirect',
                    'external_reference' => $order->getIncrementId(),
                ];

                // Compare Token
                $tokenSuccess = $this->helper->generateToken($order, 'approved');
                $tokenFailure = $this->helper->generateToken($order, 'denied');
                $tokenCanceled = $this->helper->generateToken($order, 'cancel');
                if ($callbackToken === $tokenSuccess) {
                    $additionalData['status'] = 'approved';
                    $path = 'checkout/onepage/success';
                    if (!$this->goCuotas->invoiceOrder($order, $order->getId(), $additionalData)) {
                        throw new Exception(__('Order could not be invoiced.'));
                    }
                } elseif ($callbackToken === $tokenFailure) {
                    $additionalData['status'] = 'denied';
                    if (!$this->goCuotas->cancelOrder($order, $order->getId(), $additionalData)) {
                        throw new Exception(__('Order could not be canceled.'));
                    }
                    $this->session->setErrorMessage(__('An error occurred while processing your payment.'));
                } elseif ($callbackToken === $tokenCanceled) {
                    $additionalData['status'] = 'cancel';
                    $additionalData['method'] = 'modal';
                    if (!$this->goCuotas->cancelOrder($order, $order->getId(), $additionalData)) {
                        throw new Exception(__('Order could not be canceled.'));
                    }
                    // Timeout cancel?
                    if ($this->request->getParam('timeout') !== null) {
                        $this->session->setErrorMessage(__('Waiting time exceeded. Please try again.'));
                    } else {
                        $this->session->setErrorMessage(__('Go Cuotas Payment canceled by Customer'));
                    }
                } else {
                    // Unknown
                    $this->session->setErrorMessage(__('We are waiting for the response from Go Cuotas.'));
                }
            }
        } catch (Exception $e) {
            $this->helper->log($e->getMessage());
        } finally {
            $resultRedirect->setPath($path);
            return $resultRedirect;
        }
    }
}

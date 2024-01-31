<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model;

use Magento\Framework\Serialize\SerializerInterface as Json;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use MageRocket\GoCuotas\Api\Data\TokenInterface;
use MageRocket\GoCuotas\Api\Data\TransactionInterface;
use MageRocket\GoCuotas\Api\TokenRepositoryInterface;
use MageRocket\GoCuotas\Api\TransactionRepositoryInterface;
use MageRocket\GoCuotas\Helper\Data;
use MageRocket\GoCuotas\Model\GoCuotas\Endpoints;
use MageRocket\GoCuotas\Model\ResourceModel\Token\Collection as TokenCollection;
use MageRocket\GoCuotas\Model\Rest\Webservice;
use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface as PaymentTransactionRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Payment as PaymentResourceModel;
use Magento\Sales\Model\Order\Email\Sender\OrderSenderFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSenderFactory;
use Magento\Framework\FlagManager;

class GoCuotas
{
    protected const GOCUOTAS_PAYMENT_APPROVED = 'approved';

    protected const GOCUOTAS_PAYMENT_CANCELED = 'denied';

    protected const GOCUOTAS_PAYMENT_PENDING = 'undefined';

    /**
     * @var Webservice $webservice
     */
    protected Webservice $webservice;

    /**
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * @var TransactionRepositoryInterface $transactionRepository
     */
    protected TransactionRepositoryInterface $transactionRepository;

    /**
     * @var PaymentTransactionRepository $paymentTransactionRepository
     */
    protected PaymentTransactionRepository $paymentTransactionRepository;

    /**
     * @var TransactionFactory $transactionFactory
     */
    protected TransactionFactory $transactionFactory;

    /**
     * @var TimezoneInterface $timezone
     */
    protected TimezoneInterface $timezone;

    /**
     * @var TokenRepositoryInterface $tokenRepositoryInterface
     */
    protected TokenRepositoryInterface $tokenRepositoryInterface;

    /**
     * @var TokenFactory $tokenFactory
     */
    protected TokenFactory $tokenFactory;

    /**
     * @var TokenCollection $tokenCollection
     */
    protected TokenCollection $tokenCollection;

    /**
     * @var DateTime $dateTime
     */
    protected DateTime $dateTime;

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var ResourceConnection $resourceConnection
     */
    protected ResourceConnection $resourceConnection;

    /**
     * @var InvoiceManagementInterface $invoiceManagement
     */
    protected InvoiceManagementInterface $invoiceManagement;

    /**
     * @var OrderPaymentRepositoryInterface $paymentRepository
     */
    protected OrderPaymentRepositoryInterface $paymentRepository;

    /**
     * @var InvoiceRepositoryInterface $invoiceRepository
     */
    protected InvoiceRepositoryInterface $invoiceRepository;

    /**
     * @var PaymentResourceModel $paymentResourceModel
     */
    protected PaymentResourceModel $paymentResourceModel;

    /**
     * @var Json $jsonSerializer
     */
    protected Json $jsonSerializer;

    /**
     * @var OrderSenderFactory $orderSenderFactory
     */
    protected OrderSenderFactory $orderSenderFactory;

    /**
     * @var InvoiceSenderFactory $invoiceSenderFactory
     */
    protected InvoiceSenderFactory $invoiceSenderFactory;

    /**
     * @var FlagManager $flagManager
     */
    protected FlagManager $flagManager;

    /**
     * @param Data $helper
     * @param DateTime $dateTime
     * @param Json $jsonSerializer
     * @param Webservice $webservice
     * @param FlagManager $flagManager
     * @param TokenFactory $tokenFactory
     * @param TimezoneInterface $timezone
     * @param TokenCollection $tokenCollection
     * @param ResourceConnection $resourceConnection
     * @param TransactionFactory $transactionFactory
     * @param OrderSenderFactory $orderSenderFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceSenderFactory $invoiceSenderFactory
     * @param PaymentResourceModel $paymentResourceModel
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param InvoiceManagementInterface $invoiceManagement
     * @param TokenRepositoryInterface $tokenRepositoryInterface
     * @param OrderPaymentRepositoryInterface $paymentRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param PaymentTransactionRepository $paymentTransactionRepository
     */
    public function __construct(
        Data $helper,
        DateTime $dateTime,
        Json $jsonSerializer,
        Webservice $webservice,
        FlagManager $flagManager,
        TokenFactory $tokenFactory,
        TimezoneInterface $timezone,
        TokenCollection $tokenCollection,
        ResourceConnection $resourceConnection,
        TransactionFactory $transactionFactory,
        OrderSenderFactory $orderSenderFactory,
        OrderRepositoryInterface $orderRepository,
        InvoiceSenderFactory $invoiceSenderFactory,
        PaymentResourceModel $paymentResourceModel,
        InvoiceRepositoryInterface $invoiceRepository,
        InvoiceManagementInterface $invoiceManagement,
        TokenRepositoryInterface $tokenRepositoryInterface,
        OrderPaymentRepositoryInterface $paymentRepository,
        TransactionRepositoryInterface $transactionRepository,
        PaymentTransactionRepository $paymentTransactionRepository
    ) {
        $this->helper = $helper;
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
        $this->webservice = $webservice;
        $this->flagManager = $flagManager;
        $this->tokenFactory = $tokenFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->orderRepository = $orderRepository;
        $this->tokenCollection = $tokenCollection;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceManagement = $invoiceManagement;
        $this->paymentRepository = $paymentRepository;
        $this->resourceConnection = $resourceConnection;
        $this->transactionFactory = $transactionFactory;
        $this->orderSenderFactory = $orderSenderFactory;
        $this->invoiceSenderFactory = $invoiceSenderFactory;
        $this->paymentResourceModel = $paymentResourceModel;
        $this->transactionRepository = $transactionRepository;
        $this->tokenRepositoryInterface = $tokenRepositoryInterface;
        $this->paymentTransactionRepository = $paymentTransactionRepository;
    }

    /**
     * Create Transaction
     *
     * @param Order $order
     * @return false|mixed|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createTransaction($order)
    {
        $storeId = $order->getStoreId();
        $accessToken = $this->getAccessToken($storeId);
        if ($accessToken === null) {
            throw new Exception(__("An error occurred while validating/generating token"));
        }
        $paymentData = $this->prepareOrderData($order);
        $requestData = [];
        $requestData['headers'] = [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer $accessToken",
        ];
        $requestData['body'] = $this->serializeData($paymentData);
        $createPaymentEndpoint = $this->helper->buildRequestURL(Endpoints::CREATE_PAYMENT, $storeId);
        $goCuotasPreference = $this->webservice->doRequest($createPaymentEndpoint, $requestData, "POST");
        $responseBody = $this->unserializeData($goCuotasPreference->getBody()->getContents());
        if ($goCuotasPreference->getStatusCode() > 201) {
            $this->helper->log("ERROR: Checkouts Create Request: " . $this->serializeData($requestData));
            $this->helper->log("Response: " . $this->serializeData($responseBody));
            throw new Exception(__("An error occurred while creating Payment"), $goCuotasPreference->getStatusCode());
        }
        // Log Debug
        $this->helper->logDebug('Create Transaction Payload: ' . $this->serializeData($requestData));
        $this->helper->logDebug('Create Transaction Response: ' . $this->serializeData($responseBody ?? []));
        return $responseBody;
    }

    /**
     * CreateRefund
     *
     * @param Order $order
     * @param string $paymentId
     * @param string $amount
     * @return bool
     * @throws Exception
     */
    public function createRefund(Order $order, $paymentId, $amount)
    {
        $storeId = $order->getStoreId();
        $accessToken = $this->getAccessToken($storeId);
        if ($accessToken === null) {
            throw new Exception(__("An error occurred while validating/generating token"));
        }

        $requestData = [];
        $requestData['headers'] = [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer $accessToken",
        ];

        // Refund Data
        $refundData = [];
        if ($amount !== null) {
            // Convert amount in cents
            $refundData['amount_in_cents'] = round(($amount * 100));
        }
        $requestData['body'] = $this->serializeData($refundData);
        $endpoint = sprintf(Endpoints::CREATE_REFUND, $paymentId);
        $createPaymentEndpoint = $this->helper->buildRequestURL($endpoint, $storeId);
        $goCuotasPreference = $this->webservice->doRequest($createPaymentEndpoint, $requestData, "DELETE");
        $responseBody = $this->unserializeData($goCuotasPreference->getBody()->getContents());
        if ($goCuotasPreference->getStatusCode() > 201) {
            $this->helper->log("Create Refund Payload: " . $this->serializeData($requestData));
            $this->helper->log("Create Refund Response: " . $this->serializeData($responseBody));
            $extendMsg = $goCuotasPreference->getStatusCode() == '404' ? 'Payment not exist Go Cuotas' : 'Check logs';
            throw new Exception(
                __("An error occurred while creating Refund $extendMsg"),
                $goCuotasPreference->getStatusCode()
            );
        }

        // Log Debug
        $this->helper->logDebug('Create Refund Payload: ' . $this->serializeData($requestData));
        $this->helper->logDebug('Create Refund Response: ' . $this->serializeData($responseBody ?? []));
        return $responseBody;
    }

    /**
     * Save Transaction
     *
     * @param Order $order
     * @return void
     */
    public function saveTransaction($order)
    {
        try {
            if (!$this->transactionRepository->getByOrderId($order->getId())) {
                $paymentCreatedAt = $this->timezone->date()->format('Y-m-d H:i:s');
                $paymentExpiredAt = $this->getExpirationTime();
                $transaction = $this->transactionFactory->create();
                $transaction->setOrderId($order->getId());
                $transaction->setStatus(Data::GOCUOTAS_PAYMENT_PENDING_STATUS);
                $transaction->setCreatedAt($paymentCreatedAt);
                $transaction->setExpiredAt($paymentExpiredAt);
                $transaction->setIncrementId($order->getIncrementId());
                $this->transactionRepository->save($transaction);
            }
        } catch (\Exception $e) {
            $this->helper->log("Save Transaction ERROR: " . $e->getMessage());
        }
    }

    /**
     * Get TransactionByExternalReference
     *
     * @param string $externalReference
     * @return TransactionInterface|false|null
     */
    public function getTransactionByExternalReference(string $externalReference)
    {
        try {
            $transaction = $this->transactionRepository->getByOrderIncrementId($externalReference);
            if ($transaction === null) {
                return null;
            }
            return $transaction;
        } catch (Exception $e) {
            $this->helper->log("Webhook Get Order by IncrementID " . $e->getMessage());
        }
    }

    /**
     * Get Order
     *
     * @param int|string $orderId
     * @return OrderInterface|null
     */
    public function getOrder($orderId)
    {
        $orderData = $this->orderRepository->get($orderId);
        if ($orderData === null) {
            return null;
        }
        return $orderData;
    }

    /**
     * Invoice Order
     *
     * @param Order $order
     * @param string $transactionId
     * @param array $additionalData
     * @return bool
     */
    public function invoiceOrder(Order $order, string $transactionId, array $additionalData = [])
    {
        // Can Invoice?
        if (!$order->canInvoice() || $order->hasInvoices()) {
            return false;
        }
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        try {
            $invoice = $this->invoiceManagement->prepareInvoice($order);
            $invoice->register();
            $this->orderRepository->save($order);
            $invoice->setTransactionId($transactionId);
            $payment = $order->getPayment();
            $this->paymentRepository->save($payment);

            // Create Transaction
            $transaction = $this->generateTransaction($payment, $invoice, $transactionId);
            $transaction->setAdditionalInformation('amount', round($order->getGrandTotal(), 2));
            $transaction->setAdditionalInformation('currency', 'ARS');
            $this->paymentTransactionRepository->save($transaction);

            // Update Transaction
            $this->updateGoCuotasTransaction($order->getId(), $transactionId, self::GOCUOTAS_PAYMENT_APPROVED);

            // Create Invoice
            $invoice->pay();
            $invoice->getOrder()->setIsInProcess(true);
            $payment->addTransactionCommentsToOrder($transaction, __('Go Cuotas'));
            $this->invoiceRepository->save($invoice);

            // Add Status History
            $historyMessage = $this->getAdditionalInformationFormatted($additionalData);
            $statusApproved = $this->helper->getOrderStatusApproved($order->getStoreId());
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus($statusApproved);
            $order->addCommentToStatusHistory($historyMessage, $statusApproved);

            // Send email
            if (!$order->getEmailSent()) {
                if ($this->helper->orderSendEmailEnabled($order->getStoreId())) {
                    // Confirmation Email
                    $this->orderSenderFactory->create()->send($order);
                    $order->setIsCustomerNotified(true);
                } else {
                    // Invoice Email
                    $this->invoiceSenderFactory->create()->send($invoice);
                    $invoice->setIsCustomerNotified(true);
                }
            }

            $this->orderRepository->save($order);
            $this->setPaymentInformation($order, $additionalData);
            $connection->commit();
            return true;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->helper->log("Invoice creating for order {$order->getIncrementId()} failed: \n {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Update GoCuotas Transaction
     *
     * @param int|string $orderId
     * @param string|null $transactionId
     * @param string $status
     * @return void
     */
    public function updateGoCuotasTransaction($orderId, $transactionId, string $status = 'denied')
    {
        try {
            $goCuotasTransaction = $this->transactionRepository->getByOrderId($orderId);
            if ($goCuotasTransaction->getTransactionId() === null && $transactionId !== null) {
                $goCuotasTransaction->setTransactionId($transactionId);
            }
            $goCuotasTransaction->setStatus($status);
            $this->transactionRepository->save($goCuotasTransaction);
        } catch (Exception $e) {
            $this->helper->log("Go Cuotas Transaction Update: " . $e->getMessage());
        }
    }

    /**
     * Cancel Order
     *
     * @param Order $order
     * @param string|null $transactionId
     * @param array|string $additionalData
     * @return bool
     */
    public function cancelOrder(Order $order, ?string $transactionId, $additionalData)
    {
        try {
            if ($order->canCancel()) {
                $additionalData = is_array($additionalData) ?
                    $this->getAdditionalInformationFormatted($additionalData) : $additionalData;
                $order->cancel();
                $order->setState(Order::STATE_CANCELED);
                $statusRejected = $this->helper->getOrderStatusRejected($order->getStoreId());
                $order->setStatus($statusRejected);
                $order->addCommentToStatusHistory($additionalData, $statusRejected);
                $this->orderRepository->save($order);
                // Update Transaction
                if ($transactionId) {
                    $this->updateGoCuotasTransaction($order->getId(), $transactionId, self::GOCUOTAS_PAYMENT_CANCELED);
                }
                return true;
            }
        } catch (\Exception $e) {
            $this->helper->log("Go Cuotas Cancel Order: " . $e->getMessage());
            return false;
        }
        return false;
    }

    /**
     * Delete All Tokens
     *
     * @param int|null $storeId
     * @return void
     */
    public function deleteAllTokens($storeId = null)
    {
        $tokenCollection = $this->tokenCollection;
        if ($storeId !== null) {
            $tokenCollection->addFieldToFilter(TokenInterface::STORE_ID, $storeId);
        }
        // Delete tokens
        foreach ($tokenCollection as $token) {
            try {
                $this->tokenRepositoryInterface->delete($token);
                // Invalidate Credential Validation by Store
                if ($storeId !== null) {
                    $this->credentialValidation($storeId);
                }
            } catch (\Exception $e) {
                $this->helper->log("Go Cuotas Delete Token - ERROR:" . $e->getMessage());
            }
        }

        // Invalidate All credentials validation
        if ($storeId === null) {
            $this->flagManager->saveFlag(Data::GOCUOTAS_CREDENTIALS_FLAG, $this->serializeData([]));
        }
    }

    /**
     * Generate Transaction
     *
     * @param OrderPaymentInterface $payment
     * @param InvoiceInterface $invoice
     * @param string $transactionId
     * @return mixed
     */
    private function generateTransaction($payment, $invoice, string $transactionId)
    {
        $payment->setTransactionId($transactionId);
        return $payment->addTransaction(\Magento\Sales\Api\Data\TransactionInterface::TYPE_PAYMENT, $invoice, true);
    }

    /**
     * Set Payment Information
     *
     * @param Order $order
     * @param array $additionalInformation
     * @return void
     * @throws LocalizedException
     */
    private function setPaymentInformation(Order $order, array $additionalInformation)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();
        if ($additionalInformation) {
            $payment->setAdditionalInformation($additionalInformation);
        }
        $this->paymentResourceModel->save($payment);
    }

    /**
     * Get Additional Information Formatted
     *
     * @param array|null $additionalInformation
     * @return string
     */
    private function getAdditionalInformationFormatted($additionalInformation): string
    {
        $historyFormatted = __("<b>Go Cuotas Payment Information</b>") . "<br>";
        if ($additionalInformation !== null) {
            $additionalInformation['method'] = $additionalInformation['method'] ?? $this->helper->getPaymentMode();
            if ($additionalInformation['method'] !== 'redirect' &&
                $additionalInformation["status"] !== 'cancel') {
                /**
                 * Modal / Webhook
                 */
                $historyFormatted .= __(
                    "Payment ID: <b>%1</b><br>",
                    $additionalInformation["id"] ?? 'N/A'
                );
                $historyFormatted .= __(
                    "Installments: <b>%1</b><br>",
                    $additionalInformation["installments"] ?? 'N/A'
                );
                // Set Modal Method
                $additionalInformation["method"] = "Modal";
            }
            // Log Cancel by User
            if ($additionalInformation["status"] === 'cancel') {
                $historyFormatted .= __(
                    "<b>Payment Canceled by Customer</b><br>"
                );
            }
            $historyFormatted .= __(
                "Payment External Reference: <b>#%1</b><br>",
                $additionalInformation["external_reference"] ?? 'N/A'
            );
            $historyFormatted .= __(
                "Payment Method: <b>%1</b><br>",
                ucfirst($additionalInformation["method"]) ?? 'N/A'
            );
            $historyFormatted .= __(
                "Payment Status: <b>%1</b><br>",
                ucfirst($additionalInformation["status"]) ?? 'N/A'
            );
        } else {
            $historyFormatted .= __("Unknown Payment Information");
        }
        return $historyFormatted;
    }

    /**
     * Get Access Token
     *
     * @param int|null $storeId
     * @return void|null
     */
    private function getAccessToken($storeId = null)
    {
        $tokenCollection = $this->tokenCollection;
        if ($storeId !== null) {
            $tokenCollection->addFieldToFilter(TokenInterface::STORE_ID, $storeId);
        }
        $tokenData = $tokenCollection->getFirstItem();
        if ($tokenData->getId() === null || !$this->validateToken($tokenData)) {
            if ($tokenData->getId()) {
                // Invalidate Credential
                $this->credentialValidation($storeId);
                try {
                    $this->tokenRepositoryInterface->delete($tokenData);
                    $this->helper->log("Go Cuotas Expired Token:" . $tokenData->getToken());
                } catch (\Exception $e) {
                    $this->helper->log("Go Cuotas Expired Token - ERROR:" . $e->getMessage());
                }
            }
            $tokenData = $this->generateAccessToken($storeId);
        }
        // Update Credential Flag
        $validation = false;
        if ($tokenData !== null) {
            $validation = true;
        }
        $this->credentialValidation($storeId, $validation);
        return $tokenData->getToken();
    }

    /**
     * Generate Access Token
     *
     * @param int|null $storeId
     * @return Token|null
     */
    private function generateAccessToken($storeId = null)
    {
        $email = $this->helper->getEmail($storeId);
        $password  = $this->helper->getPassword($storeId);
        if (empty($email) || empty($password)) {
            $this->helper->log("Missing Email / Password Credentials");
            return null;
        }
        $requestData = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'form_params' => [
                'email' => $email,
                'password' => $password,
            ],
        ];
        $goCuotasAuthenticationEndpoint = $this->helper->buildRequestURL(Endpoints::AUTHENTICATION, $storeId);
        $goCuotasAuthenticationRequest = $this->webservice->doRequest(
            $goCuotasAuthenticationEndpoint,
            $requestData,
            "POST"
        );
        $responseBody = $this->unserializeData($goCuotasAuthenticationRequest->getBody()->getContents());
        if ($goCuotasAuthenticationRequest->getStatusCode() != 200) {
            $this->helper->log("Get AccessToken Request: " . $this->serializeData($requestData));
            $this->helper->log("Get AccessToken Response: " . $this->serializeData($responseBody));
            return null;
        }

        try {
            // Generate Expiration Date
            $storeCurrentDateTime = $this->timezone->date()->format('Y-m-d H:i:s');
            // Add Token Expiration Days to Current Date
            $tokenExpiration = $this->dateTime->date(
                'Y-m-d H:i:s',
                strtotime($storeCurrentDateTime . " +" . Data::GOCUOTAS_TOKEN_EXPIRATION_DAYS . " days")
            );
            $tokenModel = $this->tokenFactory->create();
            $tokenModel->setStoreId($storeId ?? 1)
                ->setExpirationAt($tokenExpiration)
                ->setToken($responseBody['token']);
            $this->tokenRepositoryInterface->save($tokenModel);
            // Update Flag
            $this->credentialValidation($storeId, true);
            // Log Debug
            $this->helper->logDebug('Create Token Payload: ' . $this->serializeData($requestData));
            $this->helper->logDebug('Create Token Response: ' . $this->serializeData($responseBody ?? []));
            return $tokenModel;
        } catch (\Exception $e) {
            $this->helper->log("GoCuotas: Save Token " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Expiration Time
     *
     * @param int $expirationTime
     * @return string
     */
    private function getExpirationTime(int $expirationTime = Data::GOCUOTAS_PAYMENT_EXPIRATION)
    {
        $storeCurrentDateTime = $this->timezone->date()->format('Y-m-d H:i:s');
        return $this->dateTime->date(
            'Y-m-d H:i:s',
            strtotime($storeCurrentDateTime . " +" . $expirationTime . " minutes")
        );
    }

    /**
     * Prepare Order Data
     *
     * @param Order $order
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function prepareOrderData(Order $order)
    {
        $paymentMode = $this->helper->getPaymentMode($order->getStoreId());
        if ($paymentMode) {
            // Modal
            $successURL = Data::GOCUOTAS_MODAL_ENDPOINT . Endpoints::MODAL_SUCCESS;
            $failureURL = Data::GOCUOTAS_MODAL_ENDPOINT . Endpoints::MODAL_FAILURE;
        } else {
            // Redirect
            $tokenSuccess = $this->helper->generateToken($order, 'approved');
            $tokenFailure = $this->helper->generateToken($order, 'denied');
            $successURL = $this->helper->getCallBackUrl(['token' => $tokenSuccess]);
            $failureURL = $this->helper->getCallBackUrl(['token' => $tokenFailure]);
        }
        $webhookToken = $this->helper->generateToken($order);
        return [
            'order_reference_id' => $order->getIncrementId(),
            'email' => $order->getCustomerEmail(),
            'phone_number' => $order->getBillingAddress()->getTelephone() ?: '',
            'amount_in_cents' => round(($order->getGrandTotal() * 100), 2),
            'url_success' => $successURL,
            'url_failure' => $failureURL,
            'webhook_url' => $this->helper->getWebhookUrl($webhookToken),
        ];
    }

    /**
     * Validate Token
     *
     * @param TokenInterface $token
     * @return bool
     */
    private function validateToken(TokenInterface $token): bool
    {
        $tokenExpirationDate = $token->getExpiredAt();
        $currentDateTime = $this->timezone->date();
        // Validate Expiration
        if (strtotime($currentDateTime->format('Y-m-d H:i:s')) >= strtotime($tokenExpirationDate)) {
            return false;
        }
        // Test Token
        $requestData['headers'] = [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer {$token->getToken()}",
        ];
        $filterDate = $currentDateTime->format('Y-m-d H:i');
        $endpoint = sprintf(Endpoints::GET_ORDERS, $filterDate, $filterDate);
        $testToken = $this->helper->buildRequestURL($endpoint, $token->getStoreId());
        $goCuotasValidateToken = $this->webservice->doRequest($testToken, $requestData, "GET");
        if ($goCuotasValidateToken->getStatusCode() > 201) {
            return false;
        }
        return true;
    }

    /**
     * Save credential Validation
     *
     * @param int|null $storeId
     * @param bool $valid
     */
    private function credentialValidation($storeId, bool $valid = false)
    {
        // Get Current Config
        $storeId = $storeId ?: 0;
        $currentData = $this->flagManager->getFlagData(Data::GOCUOTAS_CREDENTIALS_FLAG) ?: [];
        if (!is_array($currentData)) {
            $currentData = $this->unserializeData($currentData);
        }
        // Update
        $currentData["store_$storeId"] = $valid;
        // Save
        $this->flagManager->saveFlag(Data::GOCUOTAS_CREDENTIALS_FLAG, $this->serializeData($currentData));
    }

    /**
     * Serialize Data
     *
     * @param array|string $data
     * @return bool|string
     */
    private function serializeData($data)
    {
        return $this->jsonSerializer->serialize($data);
    }

    /**
     * Unserialize Data
     *
     * @param array|string $data
     * @return bool|string
     */
    private function unserializeData($data)
    {
        return $this->jsonSerializer->unserialize($data);
    }
}

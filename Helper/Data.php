<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2025 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use MageRocket\GoCuotas\Logger\Logger;
use MageRocket\GoCuotas\Model\GoCuotas\Endpoints;

class Data
{
    protected const GOCUOTAS_PRODUCTION_ENDPOINT = 'https://api-magento.gocuotas.com';
    protected const GOCUOTAS_SANDBOX_ENDPOINT = 'https://sandbox.gocuotas.com';
    protected const GOCUOTAS_PAYMENT_XML_PATH = 'payment/gocuotas/%s';
    protected const GOCUOTAS_WEBHOOK_ENDPOINT = 'rest/V1/gocuotas/webhook';
    public const GOCUOTAS_MODAL_ENDPOINT = 'https://api-magento.gocuotas.com';
    public const GOCUOTAS_PAYMENT_PENDING_STATUS = 'undefined';
    public const GOCUOTAS_PAYMENT_EXPIRATION = 30;
    public const GOCUOTAS_TOKEN_EXPIRATION_DAYS = 1;
    public const GOCUOTAS_PAYMENT_METHODS = ['gocuotas' => 'gocuotas_redirect'];
    public const GOCUOTAS_CREDENTIALS_FLAG = 'magerocket_gocuotas_credentials';

    /**
     * @var Logger $logger
     */
    protected Logger $logger;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var Repository $assetRepository
     */
    protected Repository $assetRepository;

    /**
     * Init Construct
     *
     * @param Logger $logger
     * @param Repository $assetRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Logger $logger,
        Repository $assetRepository,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->assetRepository = $assetRepository;
    }

    /**
     * Get isActive
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null): bool
    {
        // Check credentials
        if ($this->getConfig('active', $storeId)) {
            return $this->isValidCredentials($storeId);
        }
        return false;
    }

    /**
     * Get isValidCredentials
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isValidCredentials($storeId = null): bool
    {
        if (empty($this->getEmail($storeId)) || empty($this->getPassword($storeId))) {
            return false;
        }
        return true;
    }

    /**
     * Get isDebugEnabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isDebugEnabled($storeId = null): bool
    {
        return $this->getConfig('debug', $storeId) ?: false;
    }

    /**
     * Get Secret
     *
     * @param int|null $storeId
     * @return string
     */
    public function getSecret($storeId = null): string
    {
        return $this->getConfig('secret', $storeId) ?: 'ag-GoCuotas';
    }

    /**
     * Get IntegrationMode
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getIntegrationMode($storeId = null): bool
    {
        return $this->getConfig('mode', $storeId) ?: false;
    }

    /**
     * Get Email
     *
     * @param int|null $storeId
     * @return string
     */
    public function getEmail($storeId = null): string
    {
        $integrationMode = $this->getIntegrationMode($storeId) ? 'production' : 'sandbox';
        return $this->getConfig($integrationMode . '/email', $storeId) ?: '';
    }

    /**
     * Get Password
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPassword($storeId = null): string
    {
        $integrationMode = $this->getIntegrationMode($storeId) ? 'production' : 'sandbox';
        return $this->getConfig($integrationMode . '/password', $storeId) ?: '';
    }

    /**
     * Get PaymentTitle
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPaymentTitle($storeId = null): string
    {
        return $this->getConfig('title', $storeId);
    }

    /**
     * Get PaymentDescription
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPaymentDescription($storeId = null): string
    {
        return $this->getConfig('description', $storeId);
    }

    /**
     * Get PaymentInstructions
     *
     * @param int|null $storeId
     * @param string $method
     * @return string
     */
    public function getPaymentInstructions($storeId = null, string $method = 'redirect'): string
    {
        return $this->getConfig("checkout/{$method}_message", $storeId);
    }

    /**
     * Get PaymentMode
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getPaymentMode($storeId = null): bool
    {
        return $this->getConfig('payment_mode', $storeId) ?: false;
    }

    /**
     * Get ShowCheckoutBanner
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getShowCheckoutBanner($storeId = null): bool
    {
        return $this->getConfig('checkout/show_payment_banner', $storeId) ?: false;
    }

    /**
     * Get Banner
     *
     * Return Banner URL
     *
     * @param int|null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBanner($storeId = null): string
    {
        $bannerGoCuotas = $this->getConfig('checkout/payment_banner', $storeId);
        if ($bannerGoCuotas === null) {
            return $this->assetRepository->getUrl("MageRocket_GoCuotas::images/banner/bannerGoCuotas.png");
        }
        $path = "gocuotas/banner/$bannerGoCuotas";
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $path;
    }

    /**
     * Get Logo
     *
     * Return Logo URL
     *
     * @return string
     */
    public function getLogo(): string
    {
        return $this->assetRepository->getUrl("MageRocket_GoCuotas::images/logoGoCuotas.svg");
    }

    /**
     * Get PaymentIcon
     *
     * Return Payment Icon URL
     *
     * @param string $method
     * @return string
     */
    public function getPaymentIcon(string $method = 'redirect'): string
    {
        return $this->assetRepository->getUrl("MageRocket_GoCuotas::images/{$method}GoCuotas.png");
    }

    /**
     * Get showInstallmentsWidget
     *
     * @param int|null $storeId
     * @return bool
     */
    public function showInstallmentsWidget($storeId = null): bool
    {
        return $this->getConfig('widget/pdp_installments_widget', $storeId) ?: false;
    }

    /**
     * Get WidgetInstallmentsQty
     *
     * @param int|null $storeId
     * @return int
     */
    public function getWidgetInstallmentsQty($storeId = null): int
    {
        return $this->getConfig('widget/installments', $storeId) ?: 1;
    }

    /**
     * Get InstallmentsLabel
     *
     * @param string $path
     * @param int|null $storeId
     * @return string
     */
    public function getInstallmentsLabel(string $path = 'pdp_installments_message', $storeId = null): string
    {
        return $this->getConfig("widget/$path", $storeId) ??
            __('Up to <b>%1</b> interest-free installments of <b>%2</b>');
    }

    /**
     * Get isRefundEnabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isRefundEnabled($storeId = null): bool
    {
        return $this->getConfig('refund/enable', $storeId) ?: false;
    }

    /**
     * Get RefundMaximumDays
     *
     * @param int|null $storeId
     * @return int
     */
    public function getRefundMaximumDays($storeId = null): int
    {
        return $this->getConfig('refund/maximum_days', $storeId) ?? 1;
    }

    /**
     * Get RefundMaximumPartialRefunds
     *
     * @param int|null $storeId
     * @return int
     */
    public function getRefundMaximumPartialRefunds($storeId = null): int
    {
        return $this->getConfig('refund/maximum_partial_refunds', $storeId) ?? 1;
    }

    /**
     * Get orderSendEmailEnabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function orderSendEmailEnabled($storeId = null):bool
    {
        return $this->scopeConfig->isSetFlag('sales_email/order/enabled', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Get CreatePreferenceURL
     *
     * Return Create Preference URL
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCreatePreferenceURL(): string
    {
        return $this->getUrl('gocuotas/order/create');
    }

    /**
     * Get WebhookURL
     *
     * Return Webhook URL
     *
     * @param string $token
     * @return string
     * @throws NoSuchEntityException
     */
    public function getWebhookURL(string $token): string
    {
        return $this->getUrl(self::GOCUOTAS_WEBHOOK_ENDPOINT . "/$token");
    }

    /**
     * Get SuccessURL
     *
     * Return Success Page URL
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSuccessURL(): string
    {
        return $this->getUrl('checkout/onepage/success');
    }

    /**
     * Get FailureURL
     *
     * Return Failure Page URL
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getFailureURL(): string
    {
        return $this->getUrl('checkout/onepage/failure');
    }

    /**
     * Get CallBackUrl
     *
     * Return Callback URL
     *
     * @param array $path
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCallBackUrl($path = []): string
    {
        return $this->getUrl('gocuotas/order/callback', $path);
    }

    /**
     * Get OrderStatusApproved
     *
     * @param int|null $storeId
     * @return string
     */
    public function getOrderStatusApproved($storeId = null): string
    {
        return $this->getConfig('order/status_approved', $storeId);
    }

    /**
     * Get OrderStatusRejected
     *
     * @param int|null $storeId
     * @return string
     */
    public function getOrderStatusRejected($storeId = null): string
    {
        return $this->getConfig('order/status_rejected', $storeId);
    }

    /**
     * Get CronOrderTimeout
     *
     * @param int|null $storeId
     * @return string
     */
    public function getCronOrderTimeout($storeId = null): string
    {
        return $this->getConfig('cron/order_timeout', $storeId) ?? 30;
    }

    /**
     * Generate Token
     *
     * @param Order $order
     * @param string|null $status
     * @return string
     */
    public function generateToken(Order $order, $status = null): string
    {
        return hash(
            'sha256',
            $this->getSecret($order->getStoreId()) . $order->getIncrementId() . $order->getCreatedAt() . $status
        );
    }

    /**
     * maskSensitiveData
     *
     * @param $data
     * @param int $visibleStart
     * @param int $visibleEnd
     * @param string $maskChar
     * @return string
     */
    function maskSensitiveData($data, int $visibleStart = 3, int $visibleEnd = 2, string $maskChar = '*'): string
    {
        $length = strlen($data);
        if ($length <= ($visibleStart + $visibleEnd)) {
            return $data;
        }
        $start = substr($data, 0, $visibleStart);
        $end = substr($data, -$visibleEnd);
        $masked = str_repeat($maskChar, $length - ($visibleStart + $visibleEnd));
        return $start . $masked . $end;
    }

    /**
     * Build RequestURL
     *
     * Return GoCuotas API URL
     *
     * @param string $endpoint
     * @param int|null $storeId
     * @return string
     */
    public function buildRequestURL(string $endpoint, $storeId = null): string
    {
        $integrationMode = $this->getIntegrationMode($storeId);
        $basePath = $integrationMode ? self::GOCUOTAS_PRODUCTION_ENDPOINT : self::GOCUOTAS_SANDBOX_ENDPOINT;
        // Add Path, Version & Endpoint
        $basePath .= Endpoints::API_PATH . Endpoints::API_VERSION . "%s";
        return vsprintf($basePath, [$endpoint]);
    }

    /**
     * Log
     *
     * This method will log CRITICAL errors even if Debug Mode is not active
     *
     * @param string $message
     * @return void
     */
    public function log(string $message): void
    {
        $this->logger->critical($message);
    }

    /**
     * LogDebug
     *
     * This method will log errors ONLY when Debug Mode is active
     *
     * @param string $message
     * @return void
     */
    public function logDebug(string $message)
    {
        if ($this->isDebugEnabled()) {
            $this->logger->debug($message);
        }
    }

    /**
     * Get Config
     *
     * Return GoCuotas Config values
     *
     * @param string $path
     * @param int|null $storeId
     * @return mixed
     */
    private function getConfig(string $path, $storeId = null)
    {
        $path = vsprintf(self::GOCUOTAS_PAYMENT_XML_PATH, [$path]);
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Get Url
     *
     * Return URL With params
     *
     * @param string $path
     * @param array $params
     * @return string
     * @throws NoSuchEntityException
     */
    private function getUrl(string $path, array $params = [])
    {
        return $this->storeManager->getStore()->getUrl($path, $params);
    }
}

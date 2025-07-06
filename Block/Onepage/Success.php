<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2025 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Block\Onepage;

use MageRocket\GoCuotas\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class Success extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * @var Session $checkoutSession
     */
    protected Session $checkoutSession;

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * Init Construct
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param OrderRepositoryInterface $orderRepository
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        OrderRepositoryInterface $orderRepository,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
    }

    /**
     * Get Order
     *
     * @return false|OrderInterface
     */
    public function getOrder()
    {
        $orderId = $this->checkoutSession->getLastRealOrder()->getId();
        if ($orderId) {
            return $this->orderRepository->get($orderId);
        } else {
            return false;
        }
    }

    /**
     * Get isPaymentMethodGoCuotas
     *
     * @return bool
     */
    public function isPaymentMethodGoCuotas(): bool
    {
        $order = $this->getOrder();
        if (!$order) {
            return false;
        }
        return in_array($order->getPayment()->getMethod(), array_keys(Data::GOCUOTAS_PAYMENT_METHODS));
    }

    /**
     * Get Logo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->helper->getLogo();
    }

    /**
     * GetPaymentMethodInfoBlock
     *
     * @return string
     * @throws LocalizedException
     */
    public function getPaymentMethodInfoBlock(): string
    {
        $order = $this->getOrder();
        $payment = $order->getPayment();
        $block = $this->getLayout()->getBlock(Data::GOCUOTAS_PAYMENT_METHODS[$payment->getMethod()]);
        return $block ? $block->toHtml() : '';
    }
}

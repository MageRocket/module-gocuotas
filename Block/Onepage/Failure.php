<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2025 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Block\Onepage;

use Magento\Framework\Exception\NoSuchEntityException;
use MageRocket\GoCuotas\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Failure extends \Magento\Checkout\Block\Onepage\Failure
{
    /**
     * @var Session $checkoutSession
     */
    protected Session $checkoutSession;

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * Init Construct
     *
     * @param Data $helper
     * @param Context $context
     * @param Session $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Context $context,
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->helper = $helper;
        parent::__construct($context, $checkoutSession, $data);
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
}

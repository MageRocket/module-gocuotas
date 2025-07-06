<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2025 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Controller\Order;

use MageRocket\GoCuotas\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\OrderRepository;

class Retry implements HttpGetActionInterface
{
    /**
     * @var OrderRepository
     */
    protected OrderRepository $orderRepository;

    /**
     * @var QuoteRepository
     */
    protected QuoteRepository $quoteRepository;

    /**
     * @var Session
     */
    protected Session $checkoutSession;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * @var RedirectFactory
     */
    protected RedirectFactory $resultRedirectFactory;

    /**
     * @var EventManagerInterface
     */
    protected EventManagerInterface $eventManager;

    /**
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * @param Session $checkoutSession
     * @param OrderRepository $orderRepository
     * @param QuoteRepository $quoteRepository
     * @param Data $helper
     * @param Context $context
     */
    public function __construct(
        Session         $checkoutSession,
        OrderRepository $orderRepository,
        QuoteRepository $quoteRepository,
        Data            $helper,
        Context         $context
    ) {
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->request = $context->getRequest();
        $this->messageManager = $context->getMessageManager();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->eventManager = $context->getEventManager();
    }

    /**
     * Execute
     *
     * @return Redirect
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function execute(): Redirect
    {
        $orderId = $this->request->getParam('order_id');
        if (!$orderId) {
            return $this->redirect('checkout/cart');
        }
        $order = $this->orderRepository->get($orderId);
        if ($order->getId()) {
            try {
                $quote = $this->quoteRepository->get($order->getQuoteId());
                $quote->setIsActive(1)->setReservedOrderId(null);
                $this->quoteRepository->save($quote);
                $this->checkoutSession->replaceQuote($quote)->unsLastRealOrderId();
                $this->eventManager->dispatch('restore_quote', ['order' => $order, 'quote' => $quote]);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('Quote cannot be restored.'));
                $this->helper->log($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Quote cannot be restored. Order not found.'));
        }
        return $this->redirect('checkout/cart');
    }

    /**
     * Create Redirect
     *
     * @param string $path
     * @return Redirect
     */
    private function redirect(string $path): Redirect
    {
        $result = $this->resultRedirectFactory->create();
        $result->setPath($path);
        return $result;
    }
}

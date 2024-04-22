<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Controller\Adminhtml\Secret;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;

class Regenerate extends Action
{
    // Secret XML Path
    protected const GOCUOTAS_SECRET_XML_PATH = 'payment/gocuotas/secret';

    // Secret Prefix. DO NOT CHANGE!
    private const GOCUTAS_SECRET_PREFIX = 'ag';

    /**
     * @var WriterInterface $configWriter
     */
    protected WriterInterface $configWriter;

    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var EncryptorInterface $encryptor
     */
    protected EncryptorInterface $encryptor;

    /**
     * @var ResultFactory $resultFactory
     */
    protected $resultFactory;

    /**
     * @var Random $random
     */
    protected Random $random;

    /**
     * Construct
     *
     * @param Random $random
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param WriterInterface $configWriter
     * @param ResultFactory $resultFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Random $random,
        Context $context,
        EncryptorInterface $encryptor,
        WriterInterface $configWriter,
        ResultFactory $resultFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->random = $random;
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->resultFactory = $resultFactory;
        $this->configWriter = $configWriter;
        parent::__construct($context);
    }

    /**
     * Execute
     */
    public function execute()
    {
        try {
            $secret = $this->random->getRandomString(10);
            $this->configWriter->save(
                self::GOCUOTAS_SECRET_XML_PATH,
                $this->encryptor->encrypt(self::GOCUTAS_SECRET_PREFIX . $secret)
            );
            $this->messageManager->addSuccessMessage(__('Go Cuotas: Secret has been regenerated.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('Go Cuotas: Error regenerating the secret. Please try again.'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}

<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2025 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use MageRocket\GoCuotas\Helper\Data as Helper;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\FlagManager;
use Magento\Framework\Serialize\SerializerInterface as Json;

class CredentialsStatus extends Field
{

    /**
     * @var Helper $helper
     */
    protected Helper $helper;

    /**
     * @var FlagManager $flagManager
     */
    protected FlagManager $flagManager;

    /**
     * @var Json $jsonSerializer
     */
    protected Json $jsonSerializer;

    /**
     * @param Helper $helper
     * @param Context $context
     * @param Json $jsonSerializer
     * @param FlagManager $flagManager
     * @param array $data
     */
    public function __construct(
        Helper $helper,
        Context $context,
        Json $jsonSerializer,
        FlagManager $flagManager,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->flagManager = $flagManager;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context, $data);
    }

    /**
     * Get ElementHtml
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $scopeId = $this->getForm()->getScopeId() ?: 0;
        if ($this->helper->isValidCredentials($scopeId)) {
            // Get Validation from Flags
            $credentialStatus = $this->getCredentialStatus($scopeId);
            if ($credentialStatus) {
                // Valid Credential
                $status = 'success';
                $label = __('Valid credentials');
            } elseif ($credentialStatus === false) {
                // Invalid Credential
                $status = 'error';
                $label = __('Invalid credentials');
            } else {
                // Not exist validation in Flag Table
                if ($this->helper->getEmail($scopeId) === null || $this->helper->getPassword($scopeId) === null) {
                    // Empty Credentials
                    $status = 'error';
                    $label = __('Credentials section is incomplete');
                } else {
                    // Pending Validation
                    $status = 'warning';
                    $label = __('Pending validation');
                }
            }
        } else {
            $status = 'info';
            $label = __('Not configured in the selected scope');
        }
        return sprintf('<div class="control-value"><span class="%s">%s</span></div>', $status, $label);
    }

    /**
     * Decorate Row HTML
     *
     * @param AbstractElement $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml(AbstractElement $element, $html)
    {
        return "<tr id='row_{$element->getHtmlId()}'>$html</tr>";
    }

    /**
     * Get Credential Status
     *
     * @param int|null $storeId
     * @return bool|null
     */
    private function getCredentialStatus($storeId)
    {
        $storeId = $storeId ?: 0;
        $credentialsData = $this->flagManager->getFlagData(Helper::GOCUOTAS_CREDENTIALS_FLAG) ?: [];
        if (!is_array($credentialsData)) {
            $credentialsData = $this->jsonSerializer->unserialize($credentialsData);
            if (in_array("store_$storeId", array_keys($credentialsData))) {
                return $credentialsData["store_$storeId"];
            }
        }
        return null;
    }
}

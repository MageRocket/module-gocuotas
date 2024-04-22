<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Block\Adminhtml\System\Config\Secret;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Button extends Field
{
    private const TEMPLATE = 'MageRocket_GoCuotas::system/config/button.phtml';

    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var Config $configResource
     */
    protected Config $configResource;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param Config $configResource
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context              $context,
        Config               $configResource,
        array                $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
        $this->configResource = $configResource;
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout(): Button
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(self::TEMPLATE);
        }
        return $this;
    }

    /**
     * Remove scope label and rendering the elements
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Generate button html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id'      => $element->getHtmlId(),
            ]
        );
        return $this->_toHtml();
    }

    /**
     * Get Regenerate Url
     *
     * @return string
     */
    public function getRegenerateUrl()
    {
        return $this->getUrl('gocuotas/secret/regenerate');
    }
}

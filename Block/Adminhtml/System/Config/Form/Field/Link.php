<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2025 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Block\Adminhtml\System\Config\Form\Field;

class Link extends \MageRocket\Core\Block\Adminhtml\System\Config\Form\Field\Link
{
   /**
    * Define Link Label
    * @var string $linkLabel
    */
    protected string $linkLabel = 'Stores > Configuration > Sales > Payment Methods > Go Cuotas';

    /**
     * Define Link Path
     * @var string $linkPath
     */
    protected string $linkPath = 'adminhtml/system_config/edit/section/payment';
}

<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Block\Adminhtml\System\Config;

class Information extends \MageRocket\Core\Block\Adminhtml\System\Config\Information
{
    /**
     * Define User Guide
     * @var string $userGuide
     */
    protected $userGuide = 'https://docs.magerocket.com/guides/go-cuotas';

    /**
     * Define Module Code
     * @var string $moduleCode
     */
    protected string $moduleCode = 'MageRocket_GoCuotas';

    /**
     * Define Module Page
     * @var string $modulePage
     */
    protected string $modulePage = 'https://magerocket.com/go-cuotas';

    /**
     * Define Feature Request
     * @var bool $allowFeatureRequest
     */
    protected bool $allowFeatureRequest = false;
}

<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Logger\Handler;

use Monolog\Logger as MonologLogger;
use Magento\Framework\Logger\Handler\Base as BaseHandler;

class CustomHandler extends BaseHandler
{
    /**
     * @var int $loggerType
     */
    protected $loggerType = MonologLogger::INFO;

    /**
     * @var string $fileName
     */
    protected $fileName = 'var/log/gocuotas/info.log';
}

<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2025 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;
use Monolog\LogRecord;

class AbstractHandler extends Base
{
    /**
     * @var array
     */
    protected $loggerTypes = [
        Logger::DEBUG,
        Logger::INFO,
        Logger::NOTICE,
        Logger::WARNING,
        Logger::ERROR,
        Logger::CRITICAL,
        Logger::ALERT,
        Logger::EMERGENCY,
    ];

    /**
     * Is Handling
     *
     * @param array|LogRecord $record
     * @return bool
     */
    public function isHandling($record): bool
    {
        return in_array($record['level'], $this->loggerTypes);
    }
}

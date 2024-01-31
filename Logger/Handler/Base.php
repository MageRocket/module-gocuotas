<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Logger\Handler;

use Exception;
use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Logger;

class Base extends AbstractHandler
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var array
     */
    protected $loggerTypes = [
        Logger::DEBUG,
        Logger::INFO,
        Logger::NOTICE,
        Logger::WARNING,
        Logger::ALERT,
        Logger::EMERGENCY,
    ];

    /**
     * @var string
     */
    protected $fileName = '/var/log/gocuotas/debug.log';

    /**
     * Init Construct
     *
     * @param DriverInterface $filesystem
     * @param string|null $filePath
     * @param string|null $fileName
     * @throws Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        ?string         $filePath = null,
        ?string         $fileName = null
    ) {
        $this->fileName = '/var/log/gocuotas/debug_' . date('m_Y') . '.log';
        parent::__construct($filesystem, $filePath, $fileName);
    }
}

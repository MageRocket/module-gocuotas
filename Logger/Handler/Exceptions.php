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

class Exceptions extends AbstractHandler
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::ERROR;

    /**
     * @var array
     */
    protected $loggerTypes = [
        Logger::ERROR,
        Logger::CRITICAL,
    ];

    /**
     * @var string
     */
    protected $fileName = '/var/log/gocuotas/exception.log';

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
        $this->fileName = '/var/log/gocuotas/exception_' . date('m_Y') . '.log';
        parent::__construct($filesystem, $filePath, $fileName);
    }
}

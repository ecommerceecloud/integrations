<?php

namespace Ecloud\Integrations\Logger\Handler;

use Monolog\Logger;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\App\Helper\Context;

class Api extends \Magento\Framework\Logger\Handler\Base
{
    const FILE_PATH = "/var/log/";
    const DEFAULT_FILE_NAME = "ecloud_integrations";
    const LOG_FILE_NAME_PREFIX = "api";
    const LOG_FILE_EXTENSION = "log";

    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    public function __construct(
        DriverInterface $driverInterface,
        Context $context
    ) {
        $fileName = $context->getScopeConfig()->getValue(
            "ecloud_integrations/general/log_name",
            ScopeInterface::SCOPE_STORE,
        );
        $fileName = self::FILE_PATH . ($fileName ?? self::DEFAULT_FILE_NAME);
        $fileName = $fileName . "_" . self::LOG_FILE_NAME_PREFIX . "." . self::LOG_FILE_EXTENSION;
        return parent::__construct($driverInterface, null, $fileName);
    }
}

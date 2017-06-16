<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as MonologLogger;

class FileHandler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = MonologLogger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/viaebay_connector.log';
}

<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Logger;

use Magento\Framework\Message\ManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger as MonologLogger;

class NotificationHandler extends AbstractProcessingHandler
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    public function __construct(ManagerInterface $messageManager, $level = MonologLogger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->messageManager = $messageManager;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        $level = $record['level'];
        $message = (string)$record['formatted'];

        if ($level >= Logger::ERROR) {
            $this->messageManager->addErrorMessage($message);
        } else if ($level >= Logger::WARNING) {
            $this->messageManager->addWarningMessage($message);
        } else if ($level >= Logger::NOTICE) {
            $this->messageManager->addNoticeMessage($message);
        } else if ($level >= Logger::INFO) {
            $this->messageManager->addSuccessMessage($message);
        }
    }
}

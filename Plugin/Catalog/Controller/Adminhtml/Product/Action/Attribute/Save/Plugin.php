<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Plugin\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save;

use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute;
use Magento\Framework\App\Request\Http;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\Service\Backlog;

/**
 * Plugin used to intercept product changes by massaction.
 * Class Plugin
 * @package VIAeBay\Connector\Plugin
 */
class Plugin
{
    /**
     * @var Backlog
     */
    private $backlog;

    /**
     * @var Attribute
     */
    private $attributeHelper;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var Logger
     */
    private $logger;

    function __construct(Http $request, Backlog $backlog, Attribute $attributeHelper, Logger $logger)
    {
        $this->request = $request;
        $this->backlog = $backlog;
        $this->attributeHelper = $attributeHelper;
        $this->logger = $logger;
    }

    public function afterExecute(Save $subject, $result)
    {
        try {
            $productIds = $this->attributeHelper->getProductIds();
            $attributesData = $this->request->getParam('attributes', []);

            $changes = null;
            if ($attributesData) {
                $changedAttributes = [];

                foreach ($attributesData as $attributeCode => $value) {
                    $changedAttributes[] = $attributeCode;
                }
                $changes = join(',', $changedAttributes);
            }

            if ($productIds) {
                foreach ($productIds as $productId) {
                    $this->backlog->createBacklogByProductId($productId, $changes);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->addError(__("Could not create backlog") . "\n" . $exception->__toString());
        }

        return $result;
    }
}
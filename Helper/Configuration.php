<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */
namespace VIAeBay\Connector\Helper;

use GuzzleHttp\Psr7\Uri;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Configuration extends AbstractHelper
{
    /**
     * Config paths for using throughout the code
     */
    const XML_PATH_ACTIVE = 'viaebay_connector/account_settings/active';
    const XML_PATH_STORE = 'viaebay_connector/account_settings/store';
    const XML_PATH_SANDBOX = 'viaebay_connector/account_settings/sandbox';
    const XML_PATH_API_KEY = 'viaebay_connector/account_settings/api_key';
    const XML_PATH_USERNAME = 'viaebay_connector/account_settings/username';
    const XML_PATH_PASSWORD = 'viaebay_connector/account_settings/password';
    const XML_PATH_FORCE_HTTP_MEDIA = 'viaebay/extended/force_http_media';
    const XML_PATH_USE_CONFIGURABLE_PRODUCT_PRICE = 'viaebay/catalog/use_configurable_product_price'; //TODO: Add to config

    /**
     * Sandbox endpoint.
     */
    const SANDBOX_ENDPOINT = 'https://sandboxapi.via.de/';

    /**
     * Live endpoint.
     */
    const LIVE_ENDPOINT = 'https://ebayapi.via.de/';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Name of this extension.
     */
    const COMPONENT_NAME = 'VIAeBay_Connector';

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Context $context, StoreManagerInterface $storeManager)
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * Is active
     * @return boolean
     */
    public function isActive()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE) == 1;
    }

    /**
     * Is sandbox
     * @return boolean
     */
    public function isSandbox()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SANDBOX, ScopeInterface::SCOPE_STORE);
    }


    /**
     * Get Api Key
     * @return String
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Api Key
     * @return String
     */
    public function getUsername()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_USERNAME, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Api Key
     * @return String
     */
    public function getPassword()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PASSWORD, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Api Key
     * @return String
     */
    public function getStoreId()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_STORE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * For http media urls
     * @return bool
     */
    public function isForceHttpMedia()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_FORCE_HTTP_MEDIA, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface|Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->getStoreId());
    }

    /**
     * Endpoint.
     * @return Uri
     */
    public function getEndpoint()
    {
        return new Uri($this->isSandbox() ? self::SANDBOX_ENDPOINT : self::LIVE_ENDPOINT);
    }

    public function isUseConfigurableProductPrice()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_USE_CONFIGURABLE_PRODUCT_PRICE, ScopeInterface::SCOPE_STORE);
    }
}
<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Helper;


use Magento\Framework\App\Area;
use Magento\Framework\App\State\Proxy;

class State
{
    /**
     * @var Proxy
     */
    private $state;

    /**
     * ImportCommand constructor.
     * @param Proxy|State $state
     */
    function __construct(Proxy $state)
    {
        $this->state = $state;
    }

    /**
     */
    public function initializeAreaCode()
    {
        try {
            $this->state->getAreaCode();
        } catch (\Exception $e) {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        }
    }

    /**
     * @return string
     */
    public function getAreaCode()
    {
        return $this->state->getAreaCode();
    }
}
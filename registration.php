<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */
use Magento\Framework\Component\ComponentRegistrar;

use VIAeBay\Connector\Helper\Configuration;

ComponentRegistrar::register(ComponentRegistrar::MODULE, Configuration::COMPONENT_NAME, __DIR__);
<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\Payment;

use \Magento\Payment\Model\Method\AbstractMethod;

class VIAeBayPayment extends AbstractMethod
{
    protected $_code = 'viaebaypayment';

    protected $_canUseCheckout = false;
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;
}
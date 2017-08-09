<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\OData;


use VIAeBay\Connector\Exception\Product;

abstract class AbstractRequest implements IRequest
{
    protected function checkResponse(array $body) {
        if (array_key_exists('error', $body)) {
            $errorObj = $body['error'];

            $code = array_key_exists('code', $errorObj) ? $errorObj['code'] : '';
            $lang = '';
            $message = '';

            if (array_key_exists('message', $errorObj)) {
                $messageObj = $errorObj['message'];
                $lang = array_key_exists('lang', $messageObj) ? $messageObj['lang'] : '';
                $message = array_key_exists('value', $messageObj) ? $messageObj['value'] : '';
            }

            throw new Product(__('Remote error message: %1 %2', [$code, $message, $lang]));
        }
    }
}
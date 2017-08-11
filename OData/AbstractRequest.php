<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\OData;


use VIAeBay\Connector\Exception\Product;

abstract class AbstractRequest implements IRequest
{
    /**
     * @param array $body
     * @throws Product
     */
    protected function checkResponse(array $body)
    {
        if ($body && array_key_exists('error', $body)) {
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

    /**
     * @param array $response
     * @param $header
     * @return array
     */
    protected function normalizeResponse(array $response, $header)
    {
        if (array_key_exists('d', $response)) {
            $normalizedResponse = $response['d'];
            if ($header != null && array_key_exists('DataServiceVersion', $header)
                && strpos($header ['DataServiceVersion'][0], '2.0') === 0
                && is_array($normalizedResponse)
                && array_key_exists('results', $normalizedResponse)
            ) {
                // Ignore collection metadata for now
                return $normalizedResponse['results'];
            } else {
                return $normalizedResponse;
            }
        }
        return $response;
    }
}
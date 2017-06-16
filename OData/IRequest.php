<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\OData;


use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\StreamInterface;

interface IRequest
{
    /**
     * @return string content type contained within this request
     */
    function getContentType();

    /**
     * @return string method used
     */
    function getMethod();

    /**
     * @param Uri|null $uriPrefix
     * @return Uri method used
     */
    function getUri(Uri $uriPrefix = null);

    /**
     * @param Uri $servicePath relative path to service. If != null causes headers to be rendered.
     * @param array $additionalHeaders for content
     * @return StreamInterface
     */
    function getContentStream(Uri $servicePath = null, array $additionalHeaders = []);

    /**
     * @param Uri $servicePath relative path to service. If != null causes headers to be rendered.
     * @param array $additionalHeaders for content
     * @return StreamInterface
     */
    function getBatchContentStream(Uri $servicePath = null, array $additionalHeaders = []);

    /**
     * @param $body
     * @param $header
     * @param bool $bodyIncludesHeader
     * @return mixed
     */
    function processResponse($body, $header = null, $bodyIncludesHeader = false);
}
<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\OData;


use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\AppendStream;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Psr\Http\Message\StreamInterface;
use VIAeBay\Connector\Helper\OData;


class Request extends AbstractRequest
{
    private $method;
    private $path;
    private $contentType;
    private $content;
    private $promise;

    /**
     * Part constructor.
     * @param string $method to use for request
     * @param $uri Uri relative path for request
     * @param $contentType string|null mime content type send
     * @param $content string|object|array content send
     * @param $promise Promise used to fulfill request
     */
    public function __construct($method, Uri $uri, $contentType = null, $content = null, Promise $promise = null)
    {
        if ($contentType == null) {
            $this->contentType = 'application/json';
        } else {
            $this->contentType = $contentType;
        }

        $this->method = $method;
        $this->path = $uri;
        $this->content = $content;
        $this->promise = $promise;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return bool
     */
    public function hasPromise()
    {
        return $this->promise !== null;
    }

    /**
     * @return Promise
     */
    public function getPromise()
    {
        if ($this->promise == null) {
            $this->promise = new Promise();
        }
        return $this->promise;
    }

    /**
     * @return Request
     */
    public function withPromise(Promise $promise)
    {
        $this->promise = $promise;
        return $this;
    }

    public function getUri(Uri $uriPrefix = null)
    {
        if (strpos($this->path->getPath(), '$') === 0) {
            return $this->path;
        }

        if ($uriPrefix == null) {
            $uriPrefix = new Uri();
        }

        //return Uri::resolve($uriPrefix, $this->_path);
        return UriResolver::resolve($uriPrefix, $this->path);
    }


    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return null
     */
    public function getContent()
    {
        return $this->content;
    }

    public function getContentAsJson()
    {
        if (empty($this->content)) {
            return "";
        } else {
            return \GuzzleHttp\json_encode($this->content) . "\n\r";
        }
    }

    /**
     *
     *
     * @param Uri $servicePath relative path to service. If != null causes headers to be rendered.
     * @param array $additionalHeaders added to content
     * @return StreamInterface
     */
    function getContentStream(Uri $servicePath = null, array $additionalHeaders = [])
    {
        $content = $this->getContentAsJson();
        $contentLength = strlen($content);

        $streams = [];

        if ($servicePath != null) {
            //$uri = $this->getUri($servicePath);
            $uri = $this->getUri();

            $header = $this->getMethod() . ' ' . $uri . " HTTP/1.1\r\n";
            $header .= 'Accept: ' . $this->getContentType() . "\r\n";

            if ($contentLength > 0) {
                $header .= 'Content-Type: ' . $this->getContentType() . "\r\n";
                $header .= 'Content-Length: ' . $contentLength . "\r\n";
            }

            foreach ($additionalHeaders as $key => $value) {
                $header .= $key . ': ' . $value . "\r\n";
            }

            $header .= "\r\n";

            $streams[] = Psr7\stream_for($header);
        }

        if ($contentLength > 0) {
            $streams[] = Psr7\stream_for($content . "\r\n");
        }

        return new AppendStream($streams);
    }

    /**
     *
     *
     * @param Uri $servicePath relative path to service. If != null causes headers to be rendered.
     * @param array $additionalHeaders added to content
     * @return StreamInterface
     */
    function getBatchContentStream(Uri $servicePath = null, array $additionalHeaders = [])
    {
        $content = $this->getContentStream($servicePath, $additionalHeaders);
        $contentLength = $content->getSize();

        // Add start and headers
        $header = "Content-Type: application/http\r\n";
        $header .= "Content-Transfer-Encoding: binary\r\n";

        if ($contentLength > 0) {
            //$header.="Content-Length: $contentLength\r\n";
        }
        $header .= "\r\n";

        $headerStream = Psr7\stream_for($header);

        return new AppendStream([$headerStream, $content]);
    }

    function processResponse($body, $header = [], $bodyIncludesHeader = false)
    {
        if ($bodyIncludesHeader) {
            list($realHeader, $realBody) = OData::parseBody($body);
        } else {
            $realHeader = $header;
            $realBody = (string)$body;
        }

        $decodedResponse = json_decode($realBody, true);
        $normalizedResponse = null;

        if ($decodedResponse != null && is_array($decodedResponse)) {
            $this->checkResponse($decodedResponse);
            $normalizedResponse = $this->normalizeResponse($decodedResponse, $realHeader);
        }

        if ($this->promise != null) {
            $this->promise->resolve($normalizedResponse);
        }

        return $normalizedResponse;
    }
}

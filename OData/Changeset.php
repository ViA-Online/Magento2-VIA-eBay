<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\OData;


use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\AppendStream;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\StreamInterface;
use Ramsey\Uuid\Uuid;
use VIAeBay\Connector\Helper\OData;


class Changeset extends AbstractRequest
{
    /**
     * @var int
     */
    private $contentId = 0;

    /**
     * @var array
     */
    private $changes = [];

    /**
     * @var string|null
     */
    private $boundary = null;

    /**
     * @param Request $request
     * @return int contentId of added change
     */
    public function addChange(Request $request)
    {
        $contentId = ++$this->contentId;
        $this->changes[$contentId] = $request;
        return $contentId;
    }

    /**
     * @return string
     */
    function getContentType()
    {
        return 'multipart/mixed; boundary=' . $this->getBoundary();
    }

    /**
     * @return null
     */
    public function getBoundary()
    {
        if (empty($this->boundary)) {
            $this->boundary = 'changeset_' . Uuid::uuid4()->toString();
        }
        return $this->boundary;
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
        $boundary = $this->getBoundary();

        $streams = [];

        foreach ($this->changes as $contentID => $request) {
            /* @var $request \VIAeBay\Connector\OData\IRequest */

            $additionalHeaders['Content-ID'] = $contentID;

            $content = $request->getContentStream($servicePath, $additionalHeaders);
            $contentLength = $content->getSize();

            // Add start and headers
            $header = "--{$boundary}\r\n";
            $header .= "Content-Type: application/http\r\n";
            $header .= "Content-Transfer-Encoding: binary\r\n";
            //$header .= "Content-ID: $contentID\r\n";
            $header .= "\r\n";

            $headerStream = Psr7\stream_for($header);

            $streams[] = $headerStream;

            // Convert the stream to string
            $streams [] = $content;
        }

        // Append end
        $streams[] = Psr7\stream_for("--{$boundary}--\r\n\r\n");

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
        $contentType = $this->getContentType();

        $content = $this->getContentStream($servicePath, $additionalHeaders);
        $contentLength = $content->getSize();

        // Add start and headers
        $header = "Content-Type: $contentType\r\n";
        //$header .= "Content-Transfer-Encoding: binary\r\n";

        if ($contentLength > 0) {
            $header .= "Content-Length: $contentLength\r\n";
        }
        $header .= "\r\n";

        $headerStream = Psr7\stream_for($header);

        return new AppendStream([$headerStream, $content]);
    }

    function processResponse($body, $header = null, $bodyIncludesHeader = false)
    {
        $requestCount = count($this->changes);
        $responseBoundary = OData::parseBoundaryHeader($header['Content-Type'][0]);

        $parts = OData::parseMessageStruct($body, $responseBoundary);

        $responseCount = count($parts);

        $batchResult = [];

        for ($i = 1; $i <= $responseCount; $i++) {
            /* @var $request \VIAeBay\Connector\OData\IRequest */
            $request = $this->changes[$i];
            $response = $parts[$i - 1];

            $batchResult[] = $request->processResponse($response['body'], $response['header'], true);
        }

        return $batchResult;
    }

    public function isEmpty()
    {
        return empty($this->changes);
    }

    /**
     * @return string method used
     */
    function getMethod()
    {
        return 'POST';
    }

    /**
     * @param Uri|null $uriPrefix
     * @return Uri method used
     */
    function getUri(Uri $uriPrefix = null)
    {
        if ($uriPrefix == null) {
            $uriPrefix = new Uri();
        }

        //return Uri::resolve($uriPrefix, $this->_path);
        return Psr7\UriResolver::resolve($uriPrefix, new Uri('$batch'));
    }
}
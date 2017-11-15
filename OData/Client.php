<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\OData;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Cookie\CookieJar as GuzzleCookieJar;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Request as PSR7Request;
use Http\Message\CookieJar;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\ModuleListInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use VIAeBay\Connector\Helper\Configuration;
use VIAeBay\Connector\Helper\OData;
use VIAeBay\Connector\Logger\Logger;


class Client
{
    const LOGIN_URL = 'Authentication_JSON_AppService.axd/Login';
    const SERVICE_URL = 'publicapi/v1/api.svc/';

    /**
     * @var GuzzleHttpClient
     */
    protected $client;

    /**
     * @var CookieJar
     */
    protected $jar;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var OData
     */
    protected $odataHelper;

    function __construct(Configuration $configuration, OData $OData, ProductMetadataInterface $productMetadata,
                         ModuleListInterface $moduleList, Logger $logger)
    {
        $this->configuration = $configuration;
        $this->odataHelper = $OData;
        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
        $this->logger = $logger; // new Logger('viaebay');

        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                $logger,
                new MessageFormatter("{request}\n\n{response}"),
                Logger::DEBUG
            )
        );

        $this->jar = new GuzzleCookieJar();
        $this->client = new GuzzleHttpClient(
            [
                'handler' => $stack,
                'cookies' => $this->jar,
                'allow_redirects' => false
            ]
        );

        //$this->login();
    }

    /**
     * Login to VIA-Connect.
     */
    function login()
    {
        $stream = Psr7\stream_for(\GuzzleHttp\json_encode([
            'userName' => trim($this->configuration->getUsername()),
            'password' => trim($this->configuration->getPassword()),
            'createPersistentCookie' => false
        ]));

        $request = (new Request('POST', $this->configuration->getEndpoint() . 'Authentication_JSON_AppService.axd/Login'))
            ->withBody($stream);

        $this->sendInternal($request, true);
    }

    function extendRequest(RequestInterface $request)
    {
        if (!$request->hasHeader('Content-Type')) {
            $request = $request->withHeader('Content-Type', 'application/json');
        }

        $request = $request->withHeader('Accept', 'application/json')
            ->withHeader('DataServiceVersion', '2.0')
            ->withHeader('MaxDataServiceVersion', '3.0')
            ->withHeader('SubscriptionToken', $this->configuration->getApiKey())
            ->withHeader('Vendor', $this->productMetadata->getName() . ' ' . $this->productMetadata->getEdition()
                . ' ' . $this->productMetadata->getVersion())
            ->withHeader('Version', $this->moduleList->getOne(Configuration::COMPONENT_NAME)['setup_version']);
        return $request;
    }

    public function send(IRequest $request)
    {
        $httpRequest = new PSR7Request($request->getMethod(), $request->getUri($this->odataHelper->getRequestTarget()));

        $contentStream = $request->getContentStream();
        if ($contentStream !== null) {
            $httpRequest = $httpRequest->withBody($contentStream);
        }

        $httpResponse = $this->sendInternal($httpRequest);

        if ($httpResponse != null) {
            return $request->processResponse($httpResponse->getBody(), $httpResponse->getHeaders());
        }

        return null;
    }

    public function sendBatch(IRequest ...$requests)
    {
        $requestCount = count($requests);
        $boundary = 'batch_' . Uuid::uuid4()->toString();

        $streams = [];
        foreach ($requests as $request) {
            /* @var $request IRequest */
            $streams[] = Psr7\stream_for("--{$boundary}\r\n");
            $streams[] = $request->getBatchContentStream($this->odataHelper->getServicePath());
        }

        if (empty($streams)) {
            return null;
        }

        // Append end
        $streams[] = Psr7\stream_for("--{$boundary}--\r\n");

        $batchUri= $this->odataHelper->joinURI($this->odataHelper->getRequestTarget(), '$batch');

        $result = $this->sendInternal(new Request('POST', $batchUri, [
            'Content-Type' => 'multipart/mixed; boundary=' . $boundary,
            'Content-Transfer-Encoding' => 'binary'
        ], new Psr7\AppendStream($streams)));

        $responseBoundary = OData::parseBoundaryHeader($result->getHeaderLine('Content-Type'));

        $parts = OData::parseMessageStruct($result->getBody(), $responseBoundary);

        if ($requestCount !== count($parts)) {
            throw new LocalizedException(__("Invalid response"));
        }

        $batchResult = [];

        for ($i = 0; $i < $requestCount; $i++) {
            /* @var $request IRequest */
            $request = $requests[$i];
            $response = $parts[$i];

            $batchResult[] = $request->processResponse($response['body'], $response['header'], true);
        }

        return $batchResult;
    }

    /**
     * @param Request $request
     * @param boolean $skipLogin true if this is a login call that skips calling login ;-)
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    protected function sendInternal(Request $request, $skipLogin = false)
    {
        $extendedRequest = $this->extendRequest($request);
        $response = null;

        if (!$skipLogin && $this->jar->count() <= 0) {
            $this->login();
        }

        $response = $this->client->sendAsync($extendedRequest, ['http_errors' => false])->wait(true);

        if ($response != null) {
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400 && $statusCode < 500) {
                throw new LocalizedException(__("Client error %1 %2", $statusCode, $response->getReasonPhrase()));
            } else if ($statusCode >= 500 && $statusCode < 600) {
                throw new LocalizedException(__("Server error %1 %2", $statusCode, $response->getReasonPhrase()));
            }
        }

        return $response;
    }
}

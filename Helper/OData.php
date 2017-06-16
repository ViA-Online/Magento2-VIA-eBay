<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Helper;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use VIAeBay\Connector\OData\Request;

class OData extends AbstractHelper
{
    /**
     * @var Uri
     */
    private $requestTarget = null;

    /**
     * @var Uri
     */
    private $servicePath = null;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * OData constructor.
     * @param Context $context
     * @param Configuration $dataHelper
     */
    public function __construct(Context $context, Configuration $dataHelper)
    {
        parent::__construct($context);
        $this->configuration = $dataHelper;
    }

    /**
     * Parse API Date.
     *
     * @param string $field
     * @param array $arr
     * @return string
     */
    public static function parseDate($field, array $arr)
    {
        if (array_key_exists($field, $arr)) {
            $date = $arr [$field];
            if ($date != '' && preg_match('|/Date\((\d*)\)/|', $date, $match)) {
                if (isset($match [1]) && $match [1] != '') {
                    return date('c', $match [1] / 1000);
                }
            }
        }
        return '';
    }

    /**
     * Update array diff at key where value at key differs from orig.
     *
     * @param array $diff
     *            changed values
     * @param array|null $orig
     *            original object
     * @param string $key
     *            to check
     * @param mixed $newValue
     *            new value
     * @param bool $allowNullValue
     * allow null value
     * @return array diff
     */
    public static function updateDelta(array &$diff, $orig, $key, $newValue, $allowNullValue = true)
    {
        if ($orig == null || !is_array($orig) || $orig [$key] != $newValue) {
            if ($newValue != null || $allowNullValue) {
                $diff [$key] = $newValue;
            }
        }
        return $diff;
    }

    /**
     * Merge update results into old object
     *
     * @param array $target
     *            of merge
     * @param array $newValues
     *            to merge
     * @return array target
     */
    public static function mergeArray(array &$target, array $newValues)
    {
        foreach ($newValues as $key => $value) {
            $target [$key] = $value;
        }
        return $target;
    }

    /**
     * Parse boundary header and extract boundary value
     * @param $header string to parse
     * @return string|null boundary or null if not found
     */
    public static function parseBoundaryHeader($header)
    {
        $matches = [];
        if (preg_match("/boundary=(.*)/", $header, $matches)) {
            return $matches[1];
        };
        return null;
    }

    /**
     * @param string $body
     * @return array
     */
    public static function parseBody($body)
    {
        $parts = preg_split("/\R\R/", $body, 2);

        return [self::parseHeaders($parts[0]), $parts[1]];
    }

    /**
     * @param string $header
     * @return array
     */
    public static function parseHeaders($header, $firstLineIsStatus = true)
    {
        $headers = [];
        $first = $firstLineIsStatus;

        $parts = preg_split("/((\r?\n)|(\r\n?))/", $header);

        foreach ($parts as $headerLine) {
            if ($first) {
                $parts = [];

                preg_match('/HTTP\/(\S+) (\S+) (.*)/', $headerLine, $parts);

                $headers['_http_status_line_'] = $parts;
            } else {
                $headerComponents = explode(':', $headerLine, 2);

                if (!empty($headerComponents)) {
                    $key = trim($headerComponents[0]);
                    $value = trim($headerComponents[1]);
                    if (!array_key_exists($key, $headers)) {
                        $headers[$key] = [];
                    }
                    $headers[$key][] = $value;
                }
            }

            $first = false;
        }
        return $headers;
    }

    /**
     * @param string $body
     * @param string $boundary
     * @return array
     */
    public static function parseMessageStruct($body, $boundary)
    {
        $escapedBoundary = preg_quote('--' . $boundary);

        $parts = preg_split('/' . $escapedBoundary . '(\-\-)?(\R)/', $body, null, PREG_SPLIT_NO_EMPTY);

        $result = [];

        foreach ($parts as $part) {
            $parts = preg_split("/\R\R/", $part, 2);

            if (count($parts) >= 2) {
                list($header, $message) = $parts;
                $headers = self::parseHeaders($header, false);
            } else {
                $message = $parts[0];
                $headers = [];
            }

            $result[] = ['header' => $headers, 'body' => $message];
        }

        return $result;
    }

    /**
     * @param array $collection to search in
     * @param $id mixed to search
     * @param string $field to search. Defaults to id
     * @return array|null
     */
    public function searchCollectionForEntity(array $collection = null, $id = null, $field = 'Id')
    {
        return $this->searchCollectionByKeyAndValue($collection, [$field => $id]);
    }

    /**
     * Search array for key and value.
     *
     * @param array $array
     * @param array $fields
     * @return NULL|mixed
     */
    public function searchCollectionByKeyAndValue(array $array = null, array $fields = [])
    {
        if ($array == null || empty($array)) {
            return null;
        }

        if ($fields == null || empty($fields)) {
            return null;
        }

        foreach ($array as $item) {
            foreach ($fields as $k => $v) {
                if (!array_key_exists($k, $item) || $item [$k] != $v) {
                    continue 2;
                }
            }
            return $item;
        }
        return null;
    }

    /**
     * Save a single entities to VIA.
     *
     * @param string|Uri $uri
     * @param array $data
     * @param bool $uriIsRelative
     * @return Request
     */
    public function saveObject($uri, $data, $uriIsRelative = true)
    {
        if ($uriIsRelative) {
            $uri = $this->joinURI($this->getRequestTarget(), $uri);
        }

        return new Request('POST', $uri, null, $data);
    }

    /**
     * Join given parameters to a url.
     * The first parameter is used as base. If the fist parameter is an array the uri is resolved using
     * $this->resolvUri. If there is a second parameter and it is an array it's values are concatenated to the base.
     * If the second parameter is not an array all paramaters are concatenated to the base.
     *
     * @param string|array|Uri
     * @param array $args
     * @return NULL|Uri
     */
    public function joinURI($uri, ...$args)
    {
        if ($uri == null) {
            return null;
        }

        if (count($args) <= 0) {
            return $uri;
        }

        $uri = self::resolveUri($uri);

        foreach ($args as $value) {
            $uri = UriResolver::resolve($uri, new Uri(rtrim($uri, '/') . '/' . ltrim($value, '/')));
        }

        return $uri;
    }

    /**
     * Append ?key=value parameters to the uri.
     *
     * @param Uri $uri to append parameters to
     * @param array $parameters to append as key => value
     * @return Uri modified uri
     */
    public function uriAppendParameters(Uri $uri, array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $uri = Uri::withQueryValue($uri, $key, $value);
        }

        return $uri;
    }

    /**
     * Resolve uri by entity array, string or Uri.
     *
     * @param string|array|Uri $uriObj
     * @return Uri
     * @throws \Exception if invalid parameter is given
     */
    public function resolveUri($uriObj)
    {
        if (is_string($uriObj)) {
            return new Uri($uriObj);
        } elseif (is_array($uriObj)
            && array_key_exists('__metadata', $uriObj)
            && array_key_exists('uri', $uriObj ['__metadata'])
        ) {
            return new Uri($uriObj ['__metadata'] ['uri']);
        } elseif ($uriObj instanceof Uri) {
            return $uriObj;
        }

        throw new LocalizedException(__('Invalid type given {}', $uriObj));
    }

    /**
     * @return Uri
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget == null) {
            $this->requestTarget = $this->joinURI($this->configuration->getEndpoint(), $this->getServicePath());
        }
        return $this->requestTarget;
    }

    /**
     * @return string
     */
    public function getServicePath()
    {
        if ($this->servicePath == null) {
            $this->servicePath = new Uri('/publicapi/v1/api.svc/');
        }
        return $this->servicePath;
    }

    /**
     * Update a single entities by its collection id.
     *
     * @param string|Uri $id
     * @param array $data
     * @return Request
     */
    public function updateObjectByCollectionId($id, $data)
    {
        return $this->updateObject($this->joinURI($this->getRequestTarget(), $id), $data);
    }

    /**
     * Update an entities.
     *
     * @param Uri|array $uri
     * @param array $data
     * @return Request
     */
    public function updateObject($uri, $data)
    {
        $uri = $this->resolveUri($uri);
        return new Request('MERGE', $uri, null, $data);
    }

    /**
     * Update a single entities field.
     *
     * @param Uri|string $uri
     * @param string $field
     * @param mixed $data
     * @return Request
     */
    public function updateObjectField($uri, $field, $data)
    {
        $uri = $this->resolveUri($uri);
        //$fieldUri = $this->joinURI($uri, $field . '/$value');
        //return new Request('PUT', $fieldUri, 'text/plain', $data);
        return $this->updateObject($uri, [$field => $data] );
    }

    /**
     * Add a link between two entities.
     *
     * @param array $source
     * @param string $collection
     * @param array $target
     * @return mixed
     * @throws \Exception
     */
    public function addLink(array $source, $collection, array $target)
    {
        $targetUri = $this->resolveUri($target);

        if (empty($targetUri)) {
            throw new LocalizedException(__('Target cannot be null'));
        }

        $uri = $this->joinURI($this->resolveUri($source), '$links', $collection);
        return new Request('POST', $uri, null, ['uri' => $targetUri->__toString()]);
    }

    /**
     * Delete a link between two entities.
     *
     * @param array $source
     * @param string $collection
     * @param array $target
     * @return Request
     */
    public function deleteLink(array $source, $collection, array $target)
    {
        $targetUrl = $this->resolveUri($target);
        $targetId = strrchr($targetUrl, '(');
        $uri = $this->joinURI($this->resolveUri($source), '$links', $collection . $targetId);
        return new Request('DELETE', $uri);
    }

    /**
     * Delete given entities.
     *
     * @param array|string|Uri $uri
     * @param string $key
     * @return Request
     */
    public function deleteObject($uri, $key = null)
    {
        if (is_array($uri)) {
            $uri = $this->resolveUri($uri);
        } elseif ($key != null) {
            $uri = $this->getEntityUri($uri, $key);
        }
        return new Request('DELETE', $uri);
    }

    /**
     * Call a service operation.
     *
     * @param mixed $uri
     * @param array $params
     * @param bool $uriIsRelative
     * @return Request
     */
    public function call($uri, array $params, $uriIsRelative = true)
    {
        if ($uriIsRelative) {
            $uri = $this->joinURI($this->getRequestTarget(), $uri);
        }

        $uri = $this->uriAppendParameters($uri, $params);

        return new Request('POST', $uri);
    }

    /**
     * Returns the url using the collection.
     *
     * @param string $collection
     * @param string $key
     * @return NULL|Uri
     */
    public function getEntityUri($collection, $key)
    {
        return $this->joinURI($this->getServicePath(), $collection . '(' . $key . 'L)');
    }
}

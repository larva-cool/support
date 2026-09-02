<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

use ArrayAccess;
use Larva\Support\Exception\RequestException;
use LogicException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class HTTPResponse
 * @author Tongle Xu <xutongle@gmail.com>
 */
class HttpResponse implements ArrayAccess
{
    /**
     * The transfer stats.
     *
     * @var mixed
     */
    public $transferStats;

    /**
     * The cookies for the request.
     *
     * @var mixed
     */
    public $cookies;

    /**
     * The underlying PSR response.
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * The decoded JSON response.
     *
     * @var array|null
     */
    protected ?array $decoded = null;

    /**
     * Create a new response instance.
     *
     * @param MessageInterface $response
     * @return void
     */
    public function __construct(MessageInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function body(): string
    {
        return (string)$this->response->getBody();
    }

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function json(?string $key = null, $default = null)
    {
        if (!$this->decoded) {
            $this->decoded = Json::decode($this->body(), true);
        }
        if (is_null($key)) {
            return $this->decoded;
        }
        return $this->decoded[$key] ?? $default;
    }

    /**
     * Get the XML decoded body of the response as an array or scalar value.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function xml(?string $key = null, $default = null)
    {
        if (!$this->decoded) {
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->loadXML($this->body(), LIBXML_NOCDATA);
            $this->decoded = $this->convertXmlToArray(simplexml_import_dom($dom->documentElement));
        }
        if (is_null($key)) {
            return $this->decoded;
        }
        return $this->decoded[$key] ?? $default;
    }

    /**
     * Get the JSON decoded body of the response as an object.
     *
     * @return object
     */
    public function object()
    {
        return json_decode($this->body(), false);
    }

    /**
     * Get a header from the response.
     *
     * @param string $header
     * @return string
     */
    public function header(string $header): string
    {
        return $this->response->getHeaderLine($header);
    }

    /**
     * Retrieves all message header values.
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function statusCode(): int
    {
        return (int)$this->response->getStatusCode();
    }

    /**
     * Get the effective URI of the response.
     *
     * @return UriInterface
     */
    public function effectiveUri(): UriInterface
    {
        return $this->transferStats->getEffectiveUri();
    }

    /**
     * Determine if the request was successful.
     *
     * @return bool
     */
    public function successful(): bool
    {
        return $this->statusCode() >= 200 && $this->statusCode() < 300;
    }

    /**
     * Determine if the response code was "OK".
     *
     * @return bool
     */
    public function ok(): bool
    {
        return $this->statusCode() === 200;
    }

    /**
     * Determine if the response was a redirect.
     *
     * @return bool
     */
    public function redirect(): bool
    {
        return $this->statusCode() >= 300 && $this->statusCode() < 400;
    }

    /**
     * Determine if the response indicates a client or server error occurred.
     *
     * @return bool
     */
    public function failed(): bool
    {
        return $this->serverError() || $this->clientError();
    }

    /**
     * Determine if the response indicates a client error occurred.
     *
     * @return bool
     */
    public function clientError(): bool
    {
        return $this->statusCode() >= 400 && $this->statusCode() < 500;
    }

    /**
     * Determine if the response indicates a server error occurred.
     *
     * @return bool
     */
    public function serverError(): bool
    {
        return $this->statusCode() >= 500;
    }

    /**
     * Execute the given callback if there was a server or client error.
     *
     * @param \Closure|callable $callback
     * @return $this
     */
    public function onError(callable $callback)
    {
        if ($this->failed()) {
            $callback($this);
        }
        return $this;
    }

    /**
     * Get the underlying PSR response for the response.
     *
     * @return ResponseInterface
     */
    public function toPsrResponse()
    {
        return $this->response;
    }

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @param \Closure|null $callback
     * @return $this
     * @throws RequestException
     */
    public function throw()
    {
        $callback = func_get_args()[0] ?? null;

        if ($this->failed()) {
            $exception = new RequestException($this);
            if ($callback && is_callable($callback)) {
                $callback($this, $exception);
            }
            throw $exception;
        }
        return $this;
    }

    /**
     * 保存到本地路径
     * @param string $path
     * @param int $mode 权限
     * @param false $lock
     * @return bool|int
     * @throws \Exception
     */
    public function saveAs(string $path, int $mode = 0755, bool $lock = false)
    {
        FileHelper::readyDirectory($path, $mode);
        $name = StringHelper::random(40) . '.' . FileHelper::getStreamExtension($this->body());
        $path = trim($path . '/' . $name, '/');
        if (FileHelper::put($path, $this->body(), $lock)) {
            return $path;
        }
        return false;
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->json()[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->json()[$offset];
    }

    /**
     * Set the value at the given offset.
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     *
     * @throws \LogicException
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }

    /**
     * Unset the value at the given offset.
     *
     * @param string $offset
     * @return void
     *
     * @throws \LogicException
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->body();
    }

    /**
     * Dynamically proxy other methods to the underlying response.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->response->{$method}(...$parameters);
    }

    /**
     * Converts XML document to array.
     * @param string|\SimpleXMLElement $xml xml to process.
     * @return array XML array representation.
     */
    protected function convertXmlToArray($xml): array
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        $result = (array)$xml;
        foreach ($result as $key => $value) {
            if (!is_scalar($value)) {
                $result[$key] = $this->convertXmlToArray($value);
            }
        }
        return $result;
    }
}

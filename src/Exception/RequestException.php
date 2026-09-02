<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Larva\Support\Exception;

use GuzzleHttp\Psr7\Message;
use Larva\Support\HttpResponse;

/**
 * Class RequestException
 * @author Tongle Xu <xutongle@gmail.com>
 * @codeCoverageIgnore
 */
class RequestException extends HttpClientException
{
    /**
     * The response instance.
     *
     * @var HttpResponse
     */
    public HttpResponse $response;

    /**
     * Create a new exception instance.
     *
     * @param HttpResponse $response
     * @return void
     */
    public function __construct(HttpResponse $response)
    {
        parent::__construct($this->prepareMessage($response), $response->statusCode());
        $this->response = $response;
    }

    /**
     * Prepare the exception message.
     *
     * @param HttpResponse $response
     * @return string
     */
    protected function prepareMessage(HttpResponse $response): string
    {
        $message = "HTTP request returned status code {$response->statusCode()}";
        $summary = Message::bodySummary($response->toPsrResponse());
        return is_null($summary) ? $message : $message .= ":\n{$summary}\n";
    }
}

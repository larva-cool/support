<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

use Larva\Support\Exception\InvalidUrlException;

/**
 * Url 助手
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Url
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var array|false
     */
    protected $parsedUrl;

    /**
     * Url constructor.
     * @param string $url
     * @throws InvalidUrlException
     */
    public function __construct(string $url)
    {
        if (!StringHelper::startsWith($url, ['http://', 'https://', 'ssl://'])) {
            $url = "https://{$url}";
        }
        if (function_exists('idn_to_ascii') && strlen($url) < 61) {
            $url = idn_to_ascii($url, false, INTL_IDNA_VARIANT_UTS46);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException("String `{$url}` is not a valid url.");
        }
        $this->url = $url;
        $this->parsedUrl = parse_url($url);
        if (!isset($this->parsedUrl['host'])) {
            throw new InvalidUrlException("Could not determine host from url `{$url}`.");
        }
    }

    /**
     * 获取协议
     * @return mixed
     */
    public function getScheme(): string
    {
        return $this->parsedUrl['scheme'];
    }

    /**
     * 获取主机名
     * @return string
     */
    public function getHostName(): string
    {
        return $this->parsedUrl['host'];
    }

    /**
     * 获取端口
     * @return int
     */
    public function getPort(): int
    {
        if (isset($this->parsedUrl['port'])) {
            return $this->parsedUrl['port'];
        } elseif ($this->parsedUrl['scheme'] == 'http') {
            return 80;
        } else {
            return 443;
        }
    }

    /**
     * 获取 User
     * @return string
     */
    public function getUser(): string
    {
        return $this->parsedUrl['user'] ?? '';
    }

    /**
     * 获取密码
     * @return string
     */
    public function getPassWord(): string
    {
        return $this->parsedUrl['pass'] ?? '';
    }

    /**
     * 获取 Query
     * @return string
     */
    public function getQuery(): string
    {
        return $this->parsedUrl['query'] ?? '';
    }

    /**
     * 获取 Path
     * @return string
     */
    public function getPath(): string
    {
        return $this->parsedUrl['path'] ?? '';
    }

    /**
     * 获取 Fragment
     * @return string
     */
    public function getFragment(): string
    {
        return $this->parsedUrl['fragment'] ?? '';
    }
}

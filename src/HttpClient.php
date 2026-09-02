<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Larva\Support\Exception\ConnectionException;
use Larva\Support\Traits\HasHttpRequest;

/**
 * HTTP 客户端
 * @author Tongle Xu <xutongle@gmail.com>
 */
class HttpClient extends BaseObject
{
    use HasHttpRequest;

    /**
     * @var mixed
     */
    protected $guzzle_handler;

    /**
     * 设置 guzzle handler
     * @param mixed $handler
     * @return $this
     */
    public function setGuzzleHandler($handler): HttpClient
    {
        $this->guzzle_handler = $handler;
        return $this;
    }

    /**
     * Issue a GET request to the given URL.
     *
     * @param string $url
     * @param array|string|null $query
     * @return array
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public function getJSON(string $url, $query = null)
    {
        $response = $this->acceptJson()->get($url, $query);
        return $response->json();
    }

    /**
     * Issue a POST request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return array
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public function postJSON(string $url, array $data = [])
    {
        $response = $this->asJson()->acceptJson()->post($url, $data);
        return $response->json();
    }

    /**
     * Issue a PATCH request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return array
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public function patchJSON(string $url, $data = [])
    {
        $response = $this->asJson()->acceptJson()->patch($url, $data);
        return $response->json();
    }

    /**
     * Issue a PUT request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return array
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public function putJSON(string $url, $data = [])
    {
        $response = $this->asJson()->acceptJson()->put($url, $data);
        return $response->json();
    }

    /**
     * Issue a DELETE request to the given URL.
     *
     * @param string $url
     * @param array $data
     * @return array
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public function deleteJSON(string $url, $data = []): array
    {
        $response = $this->asJson()->acceptJson()->delete($url, $data);
        return $response->json();
    }

    /**
     * @return $this
     */
    public static function make(): HttpClient
    {
        return new static();
    }

    /**
     * 仅使用IPV4
     */
    public static function onlyIPv4(): void
    {
        self::$defaultOptions[RequestOptions::FORCE_IP_RESOLVE] = 'v4';
    }

    /**
     * 仅使用IPV6
     */
    public static function onlyIPv6(): void
    {
        self::$defaultOptions[RequestOptions::FORCE_IP_RESOLVE] = 'v6';
    }

    /**
     * 获取 SSL 证书链
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @return array|false
     */
    public static function getSSLCertChain(string $host, int $port = 443, int $timeout = 60): bool|array
    {
        $context = stream_context_create();
        stream_context_set_option($context, 'ssl', 'verify_peer', false);//不验证证书合法
        stream_context_set_option($context, 'ssl', 'verify_peer_name', false);//不验证主机名是否对应
        stream_context_set_option($context, 'ssl', 'capture_peer_cert_chain', true);//获取证书链
        try {
            $stream = stream_socket_client('ssl://' . $host . ':' . $port, $errno, $errStr, $timeout, STREAM_CLIENT_CONNECT, $context);
            $params = stream_context_get_params($stream);
            stream_socket_shutdown($stream, STREAM_SHUT_WR);
            $certificateChain = [];
            foreach ($params['options']['ssl']['peer_certificate_chain'] as $cert) {
                $certificateChain[] = SSLCertificate::make($cert);
            }
            return $certificateChain;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 获取服务器 SSL 证书
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @return SSLCertificate|false
     */
    public static function getSSLCert(string $host, int $port = 443, int $timeout = 60): bool|SSLCertificate
    {
        $context = stream_context_create();
        stream_context_set_option($context, 'ssl', 'verify_peer', false);//不验证证书合法
        stream_context_set_option($context, 'ssl', 'verify_peer_name', false);//不验证主机名是否对应
        stream_context_set_option($context, 'ssl', 'capture_peer_cert', true);//获取证书详情
        try {
            $stream = stream_socket_client('ssl://' . $host . ':' . $port, $errno, $errStr, $timeout, STREAM_CLIENT_CONNECT, $context);
            $params = stream_context_get_params($stream);
            stream_socket_shutdown($stream, STREAM_SHUT_WR);
            return SSLCertificate::make($params['options']['ssl']['peer_certificate']);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 模拟浏览器下载远程文件内容(不保存)
     * @param string $url
     * @param int $timeout
     * @return false|string
     */
    public static function getRemoteContent(string $url, int $timeout = 5): bool|string
    {
        try {
            return static::make()
                ->withoutVerifying()
                ->timeout($timeout)
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.125 Safari/537.36 Edg/84.0.522.59')
                ->get($url)->body();
        } catch (\Exception|\Throwable $e) {
            return false;
        }
    }

    /**
     * 保存远程文件到本地目录
     * @param string $url 远程Url
     * @param string $path 保存路径
     * @param int $mode
     * @param false $lock
     * @return bool|int
     * @throws GuzzleException
     */
    public static function saveRemoteFileAs(string $url, string $path, int $mode = 0755, bool $lock = false)
    {
        try {
            return static::make()
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.125 Safari/537.36 Edg/84.0.522.59')
                ->get($url)->throw()->saveAs($path, $mode, $lock);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 获取 响应的 Header
     * @param string $url 目标Url
     * @param array $headers Headers
     * @param int $timeout 超时时间
     * @return array
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public static function getHeaders(string $url, array $headers = [], int $timeout = 5): array
    {
        $headers = static::make()
            ->withoutVerifying()
            ->withHeaders($headers)
            ->timeout($timeout)
            ->get($url)
            ->getHeaders();
        $newHeaders = [];
        foreach ($headers as $key => $header) {
            $newHeaders[strtolower($key)] = $header;
        }
        return $newHeaders;
    }

    /**
     * 检查 CORS 跨域
     * @param string $url 检查的Url
     * @param string $origin 来源
     * @param int $timeout 超时时间
     * @return bool
     * @throws ConnectionException
     * @throws GuzzleException
     */
    public static function checkCORS(string $url, string $origin, int $timeout = 5): bool
    {
        $headers = static::getHeaders($url, ['Referer' => $origin, 'Origin' => $origin], $timeout);
        if (isset($headers['Access-Control-Allow-Origin']) && in_array($headers['Access-Control-Allow-Origin'][0], [$origin, '*'])) {
            return true;
        }
        return false;
    }

    /**
     * 从 Url 中抽取 主机名
     * @param string $url
     * @return string
     */
    public static function getUrlHostname(string $url): bool|string
    {
        try {
            return (new Url($url))->getHostName();
        } catch (Exception\InvalidUrlException $e) {
            return false;
        }
    }

    /**
     * 获取网页TDK
     * @param string $url
     * @param int $timeout
     * @return array|false
     */
    public static function getTDK(string $url, int $timeout = 5): bool|array
    {
        $info = [
            'ip' => '',
            'https' => false,
            'title' => '',
            'keywords' => '',
            'description' => ''
        ];
        //解析IP
        if (!($info['hostname'] = static::getUrlHostname($url))) {
            return false;
        }
        if (!($info['ip'] = IPHelper::getHostIpV4($info['hostname']))) {
            return false;
        }
        if (($body = static::getRemoteContent("https://" . $info['hostname'], $timeout))) {
            $info['https'] = true;
            $heads = HtmlHelper::getHeadTags($body);
            $info = array_merge($info, $heads);
        } elseif (($body = static::getRemoteContent("http://" . $info['hostname'], $timeout))) {
            $info['https'] = false;
            $heads = HtmlHelper::getHeadTags($body);
            $info = array_merge($info, $heads);
        } else {
            return false;
        }

        return $info;
    }

    /**
     * 获取连接信息
     * @param string $url 目标Url
     * @param int $connectTimeout 连接超时时间
     * @param int $timeout 总超时时间
     * @return array
     */
    public static function getInfo(string $url, int $connectTimeout = 3, int $timeout = 10): array
    {
        // 创建 cURL 句柄
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);//强制获取一个新的连接，而不是缓存中的连接。
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);//在完成交互以后强制明确的断开连接，不能在连接池中重用。
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);//允许 cURL 函数执行的最长秒数。
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);//连接超时的秒数

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);//根据 Location: 重定向时，自动设置 header 中的Referer:信息。
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);//根据服务器返回 HTTP 头中的 "Location: " 重定向。
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);//最大重定向次数

        curl_setopt($ch, CURLOPT_HEADER, true);
        //curl_setopt($ch,CURLINFO_HEADER_OUT,true);

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36');

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//禁止 cURL 验证对等证书（peer's certificate）。
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//验证 SSL 对等证书中的公用名称字段或主题备用名称（Subject Alternate Name，简称 SNA）字段是否与提供的主机名匹配。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//将curl_exec()获取的信息以字符串返回，而不是直接输出。
        $info = [];
        $result = curl_exec($ch);
        if ($result) {
            $info = curl_getinfo($ch);
            $header = substr($result, 0, $info['header_size']);
            $info['response_header'] = $header;
        }
        curl_close($ch);
        return $info;
    }
}

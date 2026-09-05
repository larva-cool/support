<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

use Carbon\Carbon;
use Larva\Support\Exception\InvalidUrlException;
use Larva\Support\Exception\RuntimeException;

/**
 * SSL 证书助手
 * @author Tongle Xu <xutongle@gmail.com>
 */
class SSLCertificate
{
    /**
     * @var array 原始证书字段
     */
    protected array $rawCertificateFields;

    /**
     * @var string 证书指纹
     */
    protected string $fingerprint = '';

    /**
     * @var string SHA256指纹
     */
    protected string $fingerprintSha256 = '';

    /**
     * SSLCertificate constructor.
     * @param array $rawCertificateFields 原始证书字段
     * @param string $fingerprint 指纹
     * @param string $fingerprintSha256 SHA256指纹
     */
    public function __construct(array $rawCertificateFields, string $fingerprint = '', string $fingerprintSha256 = '')
    {
        $this->rawCertificateFields = $rawCertificateFields;
        $this->fingerprint = $fingerprint;
        $this->fingerprintSha256 = $fingerprintSha256;
    }

    /**
     * 创建证书实例
     * @param string $certificatePem PEM 编码的证书内容
     * @return SSLCertificate
     * @throws RuntimeException 当证书解析失败时
     */
    public static function make(string $certificatePem): self
    {
        $certificateFields = openssl_x509_parse($certificatePem);
        if ($certificateFields === false) {
            throw new RuntimeException('Could not parse the given SSL certificate.');
        }
        $fingerprint = openssl_x509_fingerprint($certificatePem);
        $fingerprintSha256 = openssl_x509_fingerprint($certificatePem, 'sha256');
        if ($fingerprint === false || $fingerprintSha256 === false) {
            throw new RuntimeException('Could not compute certificate fingerprint.');
        }
        return new self($certificateFields, $fingerprint, $fingerprintSha256);
    }

    /**
     * 从文件创建证书实例
     * @param string $pathToCertificate 证书文件路径
     * @return SSLCertificate
     * @throws RuntimeException 当文件不存在或解析失败时
     */
    public static function makeFromFile(string $pathToCertificate): self
    {
        if (!is_file($pathToCertificate) || !is_readable($pathToCertificate)) {
            throw new RuntimeException("Certificate file `{$pathToCertificate}` does not exist or is not readable.");
        }
        $contents = file_get_contents($pathToCertificate);
        if ($contents === false) {
            throw new RuntimeException("Could not read certificate file `{$pathToCertificate}`.");
        }
        return self::make($contents);
    }

    /**
     * 获取证书原始字段
     * @return array
     */
    public function getRawCertificateFields(): array
    {
        return $this->rawCertificateFields;
    }

    /**
     * 获取发行人
     * @return string
     */
    public function getIssuer(): string
    {
        return $this->rawCertificateFields['issuer']['CN'] ?? '';
    }

    /**
     * 获取签名算法
     * @return string
     */
    public function getSignatureAlgorithm(): string
    {
        return $this->rawCertificateFields['signatureTypeSN'] ?? '';
    }

    /**
     * 获取签名算法长名
     * @return string
     */
    public function getSignatureAlgorithmLongName(): string
    {
        return $this->rawCertificateFields['signatureTypeLN'] ?? '';
    }

    /**
     * 获取发行人组织
     * @return string
     */
    public function getIssuerOrganization(): string
    {
        return $this->rawCertificateFields['issuer']['O'] ?? '';
    }

    /**
     * 获取证书版本
     * @return int
     */
    public function getVersion(): int
    {
        return (int)($this->rawCertificateFields['version'] ?? 0);
    }

    /**
     * 获取序列号
     * @return string
     */
    public function getSerialNumber(): string
    {
        $serial = $this->rawCertificateFields['serialNumber'] ?? '';
        return is_string($serial) ? $serial : (string)$serial;
    }

    /**
     * 获取证书指纹
     * @return string
     */
    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    /**
     * 获取 SHA256 指纹
     * @return string
     */
    public function getFingerprintSha256(): string
    {
        return $this->fingerprintSha256;
    }

    /**
     * 获取证书域名
     * @return string
     */
    public function getDomain(): string
    {
        if (!isset($this->rawCertificateFields['subject']['CN'])) {
            return '';
        }

        $cn = $this->rawCertificateFields['subject']['CN'];
        if (is_string($cn)) {
            return $cn;
        }

        if (is_array($cn) && isset($cn[0]) && is_string($cn[0])) {
            return $cn[0];
        }

        return '';
    }

    /**
     * 获取额外的主机名 (SAN)
     * @return array
     */
    public function getAdditionalDomains(): array
    {
        $subjectAltName = $this->rawCertificateFields['extensions']['subjectAltName'] ?? '';
        if (!is_string($subjectAltName) || $subjectAltName === '') {
            return [];
        }
        $additionalDomains = explode(', ', $subjectAltName);
        $domains = array_map(static function (string $domain): string {
            return str_replace('DNS:', '', $domain);
        }, $additionalDomains);
        return array_values(array_filter($domains, static function ($domain): bool {
            return is_string($domain) && $domain !== '';
        }));
    }

    /**
     * 获取证书域名列表 (主域 + SAN)
     * @return array
     */
    public function getDomains(): array
    {
        $allDomains = $this->getAdditionalDomains();
        $domain = $this->getDomain();
        if ($domain !== '') {
            $allDomains[] = $domain;
        }
        $uniqueDomains = array_unique($allDomains);
        return array_values(array_filter($uniqueDomains, static function ($item): bool {
            return is_string($item) && $item !== '';
        }));
    }

    /**
     * 获取原始证书JSON
     * @return string
     */
    public function getRawCertificateFieldsJson(): string
    {
        return Json::encode($this->getRawCertificateFields());
    }

    /**
     * 获取 MD5 哈希
     * @return string
     */
    public function getHash(): string
    {
        return md5($this->getRawCertificateFieldsJson());
    }

    public function __toString(): string
    {
        return $this->getRawCertificateFieldsJson();
    }

    /**
     * 证书签发日期
     * @return Carbon
     * @throws RuntimeException 当原始字段缺失时间戳时
     */
    public function validFromDate(): Carbon
    {
        if (!isset($this->rawCertificateFields['validFrom_time_t'])) {
            throw new RuntimeException('Certificate does not contain a "validFrom_time_t" field.');
        }
        return Carbon::createFromTimestampUTC((int)$this->rawCertificateFields['validFrom_time_t']);
    }

    /**
     * 证书截止日期
     * @return Carbon
     * @throws RuntimeException 当原始字段缺失时间戳时
     */
    public function expirationDate(): Carbon
    {
        if (!isset($this->rawCertificateFields['validTo_time_t'])) {
            throw new RuntimeException('Certificate does not contain a "validTo_time_t" field.');
        }
        return Carbon::createFromTimestampUTC((int)$this->rawCertificateFields['validTo_time_t']);
    }

    /**
     * 已经签发的天数
     * @return int
     */
    public function lifespanInDays(): int
    {
        return (int)$this->validFromDate()->diffInDays($this->expirationDate());
    }

    /**
     * 是否已经过期
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expirationDate()->isPast();
    }

    /**
     * 是否是自签名的
     * @return bool
     */
    public function isSelfSigned(): bool
    {
        return $this->getIssuer() === $this->getDomain() && $this->getIssuer() !== '';
    }

    /**
     * 是否是 SHA1 签名的证书
     * @return bool
     */
    public function usesSha1Hash(): bool
    {
        $certificateFields = $this->getRawCertificateFields();
        $shortName = $certificateFields['signatureTypeSN'] ?? '';
        $longName = $certificateFields['signatureTypeLN'] ?? '';
        if ($shortName === 'RSA-SHA1' || $shortName === 'dsaWithSHA1' || $shortName === 'ecdsa-with-SHA1') {
            return true;
        }
        if ($longName === 'sha1WithRSAEncryption'
            || $longName === 'sha1WithEncryption'
            || $longName === 'ecdsa-with-SHA1') {
            return true;
        }
        return false;
    }

    /**
     * 获取剩余有效的天数 (负数表示已过期)
     * @return int
     */
    public function daysUntilExpirationDate(): int
    {
        $endDate = $this->expirationDate();
        $interval = Carbon::now()->diff($endDate);
        return (int)$interval->format('%r%a');
    }

    /**
     * 是否适用于指定的 URL
     * @param string $url URL 或主机/IP
     * @return bool
     */
    public function appliesToUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_IP)) {
            $host = $url;
        } else {
            try {
                $host = (new Url($url))->getHostName();
            } catch (InvalidUrlException $e) {
                return false;
            }
        }
        $host = strtolower($host);
        $certificateHosts = $this->getDomains();
        foreach ($certificateHosts as $certificateHost) {
            $certificateHost = strtolower(str_replace('ip address:', '', (string)$certificateHost));
            if ($host === $certificateHost) {
                return true;
            }
            if ($this->wildcardHostCoversHost($certificateHost, $host)) {
                return true;
            }
        }
        return false;
    }

    /**
     * URL 或者证书是否有效
     * @param string|null $url
     * @return bool
     */
    public function isValid(?string $url = null): bool
    {
        try {
            $now = Carbon::now();
            if (!$now->between($this->validFromDate(), $this->expirationDate())) {
                return false;
            }
        } catch (RuntimeException $e) {
            return false;
        }
        if (!empty($url)) {
            return $this->appliesToUrl($url);
        }
        return true;
    }

    /**
     * 验证有效期是否超过指定时间
     * @param Carbon $carbon 比较时间点
     * @param string|null $url 可选的 URL 校验
     * @return bool
     */
    public function isValidUntil(Carbon $carbon, ?string $url = null): bool
    {
        if ($this->expirationDate()->lte($carbon)) {
            return false;
        }
        return $this->isValid($url);
    }

    /**
     * 是否包含指定的域名 (主域完全匹配, 或子域匹配)
     * @param string $domain
     * @return bool
     */
    public function containsDomain(string $domain): bool
    {
        $domain = strtolower($domain);
        $certificateHosts = $this->getDomains();
        foreach ($certificateHosts as $certificateHost) {
            $certificateHost = strtolower((string)$certificateHost);
            if ($certificateHost === '') {
                continue;
            }
            if ($certificateHost === $domain) {
                return true;
            }
            if (StringHelper::endsWith($domain, '.' . $certificateHost)) {
                return true;
            }
        }
        return false;
    }

    /**
     * CT Precertificate Poison.
     * @return bool
     */
    public function isPreCertificate(): bool
    {
        if (!array_key_exists('extensions', $this->rawCertificateFields)) {
            return false;
        }
        if (!array_key_exists('ct_precert_poison', $this->rawCertificateFields['extensions'])) {
            return false;
        }
        return true;
    }

    /**
     * 是否匹配通配符主机
     * @param string $wildcardHost 例如 *.example.com
     * @param string $host 已转小写的主机
     * @return bool
     */
    protected function wildcardHostCoversHost(string $wildcardHost, string $host): bool
    {
        if ($host === $wildcardHost) {
            return true;
        }
        if (!StringHelper::startsWith($wildcardHost, '*.')) {
            return false;
        }
        // RFC 6125: 通配符 "*" 只能匹配单个最左侧标签
        // 因此 $wildcardHost 的最左标签必须是单独的 "*"，且 $host 的最左标签必须存在且非空
        $suffix = substr($wildcardHost, 2); // "example.com"
        $suffixLength = strlen($suffix);
        if ($suffixLength >= strlen($host)) {
            return false;
        }
        $hostSuffix = substr($host, -$suffixLength);
        if ($hostSuffix !== $suffix) {
            return false;
        }
        $leftLabel = substr($host, 0, -$suffixLength - 1); // 去掉 ".suffix" 后的左侧
        return $leftLabel !== '' && strpos($leftLabel, '.') === false;
    }
}

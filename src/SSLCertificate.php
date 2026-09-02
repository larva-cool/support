<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

use Carbon\Carbon;

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
    protected string $fingerprint;

    /**
     * @var string SHA256指纹
     */
    private string $fingerprintSha256;

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
     * @param mixed $certificatePem
     * @return SSLCertificate
     */
    public static function make($certificatePem): self
    {
        $certificateFields = openssl_x509_parse($certificatePem);
        $fingerprint = openssl_x509_fingerprint($certificatePem);
        $fingerprintSha256 = openssl_x509_fingerprint($certificatePem, 'sha256');
        return new self($certificateFields, $fingerprint, $fingerprintSha256);
    }

    /**
     * 从文件创建证书实例
     * @param string $pathToCertificate
     * @return SSLCertificate
     */
    public static function makeFromFile(string $pathToCertificate): self
    {
        return self::make(file_get_contents($pathToCertificate));
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
     * 获取发行人组织
     * @return string
     */
    public function getIssuerOrganization(): string
    {
        return $this->rawCertificateFields['issuer']['O'] ?? '';
    }

    /**
     * 获取证书指纹
     * @return string
     */
    public function getFingerprint(): string
    {
        return $this->fingerprint ?? '';
    }

    /**
     * 获取 SHA256 指纹
     * @return string
     */
    public function getFingerprintSha256(): string
    {
        return $this->fingerprintSha256 ?? '';
    }

    /**
     * 获取证书域名
     * @return string
     */
    public function getDomain(): string
    {
        if (!array_key_exists('CN', $this->rawCertificateFields['subject'])) {
            return '';
        }

        if (is_string($this->rawCertificateFields['subject']['CN'])) {
            return $this->rawCertificateFields['subject']['CN'];
        }

        if (is_array($this->rawCertificateFields['subject']['CN'])) {
            return $this->rawCertificateFields['subject']['CN'][0];
        }

        return '';
    }

    /**
     * 获取额外的主机名
     * @return array
     */
    public function getAdditionalDomains(): array
    {
        $additionalDomains = explode(', ', $this->rawCertificateFields['extensions']['subjectAltName'] ?? '');
        return array_map(function (string $domain) {
            return str_replace('DNS:', '', $domain);
        }, $additionalDomains);
    }

    /**
     * 获取证书域名列表
     * @return array
     */
    public function getDomains(): array
    {
        $allDomains = $this->getAdditionalDomains();
        $allDomains[] = $this->getDomain();
        $uniqueDomains = array_unique($allDomains);
        return array_values(array_filter($uniqueDomains));
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
     */
    public function validFromDate(): Carbon
    {
        return Carbon::createFromTimestampUTC($this->rawCertificateFields['validFrom_time_t']);
    }

    /**
     * 证书截止日期
     * @return Carbon
     */
    public function expirationDate(): Carbon
    {
        return Carbon::createFromTimestampUTC($this->rawCertificateFields['validTo_time_t']);
    }

    /**
     * 已经签发的天数
     * @return int
     */
    public function lifespanInDays(): int
    {
        return $this->validFromDate()->diffInDays($this->expirationDate());
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
        return $this->getIssuer() === $this->getDomain();
    }

    /**
     * 是否是 RSA-SHA1 签名的证书
     * @return bool
     */
    public function usesSha1Hash(): bool
    {
        $certificateFields = $this->getRawCertificateFields();
        if ($certificateFields['signatureTypeSN'] === 'RSA-SHA1') {
            return true;
        }
        if ($certificateFields['signatureTypeLN'] === 'sha1WithRSAEncryption') {
            return true;
        }
        return false;
    }

    /**
     * 获取剩余有效的天数
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
     * @param string $url
     * @return bool
     */
    public function appliesToUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_IP)) {
            $host = $url;
        } else {
            try {
                $host = (new Url($url))->getHostName();
            } catch (Exception\InvalidUrlException $e) {
                return false;
            }
        }
        $certificateHosts = $this->getDomains();
        foreach ($certificateHosts as $certificateHost) {
            $certificateHost = str_replace('ip address:', '', strtolower($certificateHost));
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
        if (!Carbon::now()->between($this->validFromDate(), $this->expirationDate())) {
            return false;
        }
        if (!empty($url)) {
            return $this->appliesToUrl($url ?? $this->getDomain());
        }
        return true;
    }

    /**
     * 验证有效期是否小于于指定时间
     * @param Carbon $carbon
     * @param string|null $url
     * @return bool
     */
    public function isValidUntil(Carbon $carbon, string $url = null): bool
    {
        if ($this->expirationDate()->lte($carbon)) {
            return false;
        }
        return $this->isValid($url);
    }

    /**
     * 是否包含指定的域名
     * @param string $domain
     * @return bool
     */
    public function containsDomain(string $domain): bool
    {
        $certificateHosts = $this->getDomains();
        foreach ($certificateHosts as $certificateHost) {
            if ($certificateHost === $domain) {
                return true;
            }
            if (StringHelper::endsWith($domain, '.'.$certificateHost)) {
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
     * @param string $wildcardHost
     * @param string $host
     * @return bool
     */
    protected function wildcardHostCoversHost(string $wildcardHost, string $host): bool
    {
        if ($host === $wildcardHost) {
            return true;
        }
        if (!StringHelper::startsWith($wildcardHost, '*')) {
            return false;
        }
        if (substr_count($wildcardHost, '.') < substr_count($host, '.')) {
            return false;
        }
        $wildcardHostWithoutWildcard = substr($wildcardHost, 1);
        $hostWithDottedPrefix = ".{$host}";
        return StringHelper::endsWith($hostWithDottedPrefix, $wildcardHostWithoutWildcard);
    }
}

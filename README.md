# Larva 公共支持

一个面向 PHP 项目的轻量级公共支持库，封装日常开发中常用的工具类、助手函数和基础组件，零业务依赖、开箱即用。

[![Coding Style](https://github.com/larvatecn/support/actions/workflows/Linter.yml/badge.svg)](https://github.com/larvatecn/support/actions/workflows/Linter.yml)
[![Tester](https://github.com/larvatecn/support/actions/workflows/Tester.yml/badge.svg)](https://github.com/larvatecn/support/actions/workflows/Tester.yml)
[![License](https://poser.pugx.org/larva/support/license.svg)](https://packagist.org/packages/larva/support)
[![Latest Stable Version](https://poser.pugx.org/larva/support/v/stable.png)](https://packagist.org/packages/larva/support)
[![Total Downloads](https://poser.pugx.org/larva/support/downloads.png)](https://packagist.org/packages/larva/support)

## 特性

- 🚀 **零业务依赖**：仅依赖 `guzzlehttp/guzzle`、`nesbot/carbon`、`ezyang/htmlpurifier` 等通用库
- 🧰 **丰富工具集**：覆盖 HTTP、字符串、数组、文件、时间、加密、网络、树、JSON、HTML 等常用场景
- 🇨🇳 **本土化增强**：内置身份证、统一社会信用代码、MAC 地址、IP、ISO 3166、地理位置等中国场景工具
- 🧱 **统一基类**：提供 `BaseObject`、Trait（`HasAttributes` / `HasHttpRequest` / `Macroable`）等基础设施
- ✅ **完整测试**：基于 PHPUnit 11 编写单元测试
- 🎨 **代码风格**：使用 `friendsofphp/php-cs-fixer` 统一编码风格

## 目录

- [环境需求](#环境需求)
- [安装](#安装)
- [快速开始](#快速开始)
- [组件一览](#组件一览)
- [使用示例](#使用示例)
- [开发与测试](#开发与测试)
- [许可证](#许可证)

## 环境需求

- PHP `^8.1 || ^8.2 || ^8.3 || ^8.4`
- 扩展：`ext-curl`、`ext-dom`、`ext-json`、`ext-openssl`、`ext-mbstring`、`ext-simplexml`、`ext-libxml`、`ext-fileinfo`

## 安装

```bash
composer require larva/support -vv
```

## 快速开始

```php
<?php

use Larva\Support\HttpClient;
use Larva\Support\StringHelper;
use Larva\Support\Number;
use Larva\Support\TimeHelper;
use Larva\Support\IDCard;

// 1. 发起一个 JSON HTTP 请求
$client = new HttpClient();
$data = $client->getJSON('https://api.example.com/users', ['page' => 1]);

// 2. 生成 UUID、随机字符串
$uuid = StringHelper::createUuid('usr_');
$rand = StringHelper::random(16);

// 3. 金额格式化（人民币）
echo Number::rmb(100.5); // ￥100.50

// 4. 友好的时间显示
echo TimeHelper::humanDateTime(time() - 3600); // 1 小时前

// 5. 校验中国大陆身份证
if (IDCard::validate('11010519491231002X')) {
    echo IDCard::getSex('11010519491231002X');      // 男 / 女
    echo IDCard::getBirthday('11010519491231002X'); // 1949-12-31
    echo IDCard::getLocation('11010519491231002X'); // 北京市 / 朝阳区
}
```

## 组件一览

| 类 / Trait | 说明 |
| --- | --- |
| `BaseObject` | 基础对象类，支持数组式属性访问与配置初始化 |
| `ArrayHelper` | 数组 / 对象取值、合并、映射等操作 |
| `ArrayAccessible` | 让对象可被 `foreach` 遍历 |
| `Collection` | 集合类，提供 map / filter / reduce 等链式操作 |
| `Config` | 轻量级配置容器 |
| `FileHelper` | 文件 / 目录读写、复制、遍历等常用操作 |
| `HtmlHelper` | HTML 安全过滤、编码（基于 HTMLPurifier） |
| `HttpClient` | 基于 Guzzle 的 HTTP 客户端，支持 JSON、表单、文件上传 |
| `HttpResponse` | 统一的 HTTP 响应封装 |
| `IDCard` | 中国大陆身份证号校验、解析出生日期 / 性别 / 地区 |
| `IPHelper` | IP 解析、内网段判断、IP ↔ 长整型互转 |
| `ISO3166` | 国家 / 地区代码数据 |
| `Json` | JSON 编码 / 解码工具 |
| `LBSHelper` | 地理位置（LBS）相关工具 |
| `MacAddres` | MAC 地址校验与格式化 |
| `Number` | 数字、金额格式化（人民币、千分位等） |
| `SSLCertificate` | SSL 证书解析 |
| `Socket` | Socket 通信 |
| `SqlHelper` | SQL 拼接助手 |
| `StringHelper` | 字符串处理：UUID、随机数、命名风格转换、截断等 |
| `TimeHelper` | 时间格式化、人性化时间显示（基于 Carbon） |
| `Tree` | 树形结构构建与遍历 |
| `UnifiedSocialCreditIdentifier` | 统一社会信用代码校验 |
| `Url` | URL 解析、拼接与签名 |
| `Traits\HasAttributes` | 对象属性读写（类似 Yii AR） |
| `Traits\HasHttpRequest` | HTTP 请求相关通用方法 |
| `Traits\Macroable` | 宏能力（动态扩展类方法） |

> 完整的接口说明请参考 `src/` 目录下各类的 PHPDoc 注释。

## 使用示例

### HTTP 客户端

```php
use Larva\Support\HttpClient;
use Larva\Support\Exception\ConnectionException;

$client = new HttpClient([
    'base_uri' => 'https://api.example.com',
    'timeout'  => 10,
]);

// 发起 JSON 请求
$user = $client->getJSON('/users/1');

// 提交表单
$client->post('/users', ['name' => 'Tom', 'age' => 18]);

// 上传文件
$client->upload('/avatar', '/path/to/file.png', ['field' => 'file']);
```

### 字符串助手

```php
use Larva\Support\StringHelper;

StringHelper::random(32);                  // 随机字符串
StringHelper::createUuid();                // UUID v4
StringHelper::camel('user_name');          // userName
StringHelper::snake('UserName');           // user_name
StringHelper::studly('user_profile');      // UserProfile
StringHelper::mask('13800001234', 3, 7);   // 138****1234
```

### 数字与金额

```php
use Larva\Support\Number;

Number::float(1.236, 2);    // 1.24
Number::priceFormat(9.8);   // 9.80
Number::rmb(199.9);         // ￥199.90
Number::toChinese(100.50);  // 壹佰元伍角
```

### 时间助手

```php
use Larva\Support\TimeHelper;

TimeHelper::humanDateTime('2024-01-01 12:00:00'); // 相对时间描述
TimeHelper::timestampToDateTime(1704067200);      // 2024-01-01 08:00:00
```

### 数组 / 集合

```php
use Larva\Support\ArrayHelper;
use Larva\Support\Collection;

$value = ArrayHelper::getValue($array, 'user.profile.name', 'default');

$collection = new Collection([1, 2, 3, 4, 5]);
$result = $collection->map(fn ($v) => $v * 2)
                    ->filter(fn ($v) => $v > 4)
                    ->values();
```

### 树形结构

```php
use Larva\Support\Tree;

$rows = [
    ['id' => 1, 'parent_id' => 0, 'name' => 'A'],
    ['id' => 2, 'parent_id' => 1, 'name' => 'B'],
    ['id' => 3, 'parent_id' => 1, 'name' => 'C'],
];

$tree = Tree::build($rows, 'id', 'parent_id', 'children');
```

### 身份证

```php
use Larva\Support\IDCard;

IDCard::validate('11010519491231002X'); // true / false
IDCard::getSex('11010519491231002X');   // '男' / '女'
IDCard::getBirthday('11010519491231002X'); // '1949-12-31'
IDCard::getAge('11010519491231002X');   // int
IDCard::getLocation('11010519491231002X'); // 行政区划
```

### 统一社会信用代码

```php
use Larva\Support\UnifiedSocialCreditIdentifier;

UnifiedSocialCreditIdentifier::validate('91110000600037341L'); // true
```

## 开发与测试

克隆仓库后，安装依赖：

```bash
composer install
```

运行单元测试：

```bash
composer test
```

检查并自动修复代码风格：

```bash
composer check-style   # 仅检查
composer fix-style     # 自动修复
```

> 本项目使用 `friendsofphp/php-cs-fixer` 统一编码风格，提交前请确保通过检查。

## 异常体系

`src/Exception/` 目录提供了一系列业务无关的基础异常类，便于业务层统一处理：

- `Exception`：根异常
- `RuntimeException` / `InvalidCallException` / `InvalidConfigException` / `InvalidUrlException`
- `HttpClientException` / `ConnectionException` / `RequestException`
- `FileException` / `FileNotFoundException`
- `NotSupportedException` / `UnknownMethodException` / `UnknownPropertyException`

## 路线图

- [ ] 持续完善单元测试覆盖率
- [ ] 增加更多中国本土化工具（如车牌号校验等）
- [ ] 提供更丰富的 TypeScript / IDE 提示

## 贡献

欢迎提交 Issue 与 Pull Request。提交前请：

1. 通过 `composer test` 全部测试
2. 通过 `composer check-style` 风格检查
3. 在 PR 中描述清楚变更背景与影响范围

## 安全

如发现安全漏洞，请联系：<support@larva.com.cn>，请勿在公开 Issue 中披露。

## 许可证

本项目基于 [MIT](LICENSE) 协议发布。

<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support\Traits;

use Larva\Support\Json;

trait HasAttributes
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Return the attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Return the extra attribute.
     *
     * @param string $name
     * @param null $default
     * @return mixed
     */
    public function getAttribute(string $name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * Set extra attributes.
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setAttribute(string $name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Map the given array onto the properties.
     *
     * @param array $attributes
     * @return $this
     */
    public function merge(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists(string $offset): bool
    {
        return array_key_exists($offset, $this->attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($property)
    {
        return $this->getAttribute($property);
    }

    /**
     * Return array.
     * @return array
     */
    public function toArray(): array
    {
        return $this->getAttributes();
    }

    /**
     * Return JSON.
     * @return string
     */
    public function toJSON(): string
    {
        return Json::encode($this->getAttributes(), JSON_UNESCAPED_UNICODE);
    }
}

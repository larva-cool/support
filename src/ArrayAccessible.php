<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

use ArrayAccess;
use ArrayIterator;
use Larva\Support\Contracts\Arrayable;
use IteratorAggregate;

class ArrayAccessible implements ArrayAccess, IteratorAggregate, Arrayable
{
    private array $array;

    public function __construct(array $array = [])
    {
        $this->array = $array;
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->array);
    }

    public function offsetGet($offset): mixed
    {
        return $this->array[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->array[] = $value;
        } else {
            $this->array[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->array[$offset]);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->array);
    }

    public function toArray(): array
    {
        return $this->array;
    }
}

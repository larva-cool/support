<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Larva\Support;

/**
 * 树
 */
class Tree
{
    /**
     * 生成树型结构所需要的2维数组
     * @var array
     */
    public array $data = [];

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     * @var array
     */
    public array $icon = ['│', '├', '└'];
    public string $blankSpace = "&nbsp;";

    // 查询
    public string $idKey = "id";
    public string $parentIdKey = "parent_id";
    public string $spacerKey = "spacer";
    public string $hasChildKey = "has_child";

    // 返回子级key
    public string $buildChildKey = "child";

    /**
     * 创建
     * @return Tree
     */
    public static function create(): Tree
    {
        return new static();
    }

    /**
     * 构造函数，初始化类
     * @param array 2维数组，例如：
     * array(
     *      1 => array('id'=>'1','parent_id'=>0,'title'=>'一级栏目一'),
     *      2 => array('id'=>'2','parent_id'=>0,'title'=>'一级栏目二'),
     *      3 => array('id'=>'3','parent_id'=>1,'title'=>'二级栏目一'),
     *      4 => array('id'=>'4','parent_id'=>1,'title'=>'二级栏目二'),
     *      5 => array('id'=>'5','parent_id'=>2,'title'=>'二级栏目三'),
     *      6 => array('id'=>'6','parent_id'=>3,'title'=>'三级栏目一'),
     *      7 => array('id'=>'7','parent_id'=>3,'title'=>'三级栏目二')
     * )
     */
    public function withData($data = []): Tree
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 设置配置
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function withConfig(string $key, string $value): Tree
    {
        if (isset($this->{$key})) {
            $this->{$key} = $value;
        }
        return $this;
    }

    /**
     * 构建数组
     * @param string|int $id 要查询的ID
     * @param string $itemPrefix 前缀
     * @return array
     */
    public function buildArray($id, string $itemPrefix = ''): array
    {
        $child = $this->getListChild($this->data, $id);
        if (!is_array($child)) {
            return [];
        }

        $data = [];
        $number = 1;

        $total = count($child);
        foreach ($child as $value) {
            $childInfo = $value;
            $j = '';
            if ($number == $total) {
                if (isset($this->icon[2])) {
                    $j .= $this->icon[2];
                }
                $k = $itemPrefix ? $this->blankSpace : '';
            } else {
                if (isset($this->icon[1])) {
                    $j .= $this->icon[1];
                }
                $k = $itemPrefix ? ($this->icon[0] ?? '') : '';
            }
            $spacer = $itemPrefix ? $itemPrefix . $j : '';
            $childInfo[$this->spacerKey] = $spacer;

            $childList = $this->buildArray($value[$this->idKey], $itemPrefix . $k . $this->blankSpace);
            if (!empty($childList)) {
                $childInfo[$this->buildChildKey] = $childList;
            }

            $data[] = $childInfo;
            $number++;
        }

        return $data;
    }

    /**
     * 所有父节点
     * @param array $list 数据集
     * @param string|int $parent_id 节点的parent_id
     * @param string $sort 排序
     * @return array
     */
    public function getListParents(array $list = [], $parent_id = '', string $sort = 'desc'): array
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }

        $result = [];
        foreach ($list as $value) {
            if ((string)$value[$this->idKey] == (string)$parent_id) {
                $result[] = $value;
                $parent = $this->getListParents($list, $value[$this->parentIdKey], $sort);
                if (!empty($parent)) {
                    if ($sort == 'asc') {
                        $result = array_merge($result, $parent);
                    } else {
                        $result = array_merge($parent, $result);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 所有父节点的ID列表
     * @param array $list 数据集
     * @param string|int $parent_id 节点的parent_id
     * @return array
     */
    public function getListParentsId(array $list = [], $parent_id = ''): array
    {
        $parents = $this->getListParents($list, $parent_id);
        if (empty($parents)) {
            return [];
        }

        $ids = [];
        foreach ($parents as $parent) {
            $ids[] = $parent[$this->idKey];
        }

        return $ids;
    }

    /**
     * 获取当前ID的所有子节点
     * @param array $list 数据集
     * @param string|int $id 当前id
     * @param string $sort 排序
     * @return array
     */
    public function getListChildren(array $list = [], $id = '', string $sort = 'desc'): array
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }

        $result = [];
        foreach ($list as $value) {
            if ((string)$value[$this->parentIdKey] == (string)$id) {
                $result[] = $value;
                $child = $this->getListChildren($list, $value[$this->idKey], $sort);
                if (!empty($child)) {
                    if ($sort == 'asc') {
                        $result = array_merge($result, $child);
                    } else {
                        $result = array_merge($child, $result);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 获取当前ID的所有子节点id
     * @param array $list 数据集
     * @param string|int $id 当前id
     * @return array
     */
    public function getListChildrenIds(array $list = [], $id = ''): array
    {
        $childrenIds = $this->getListChildren($list, $id);
        if (empty($childrenIds)) {
            return [];
        }
        $ids = [];
        foreach ($childrenIds as $child) {
            $ids[] = $child[$this->idKey];
        }
        return $ids;
    }

    /**
     * 得到子级第一级数组
     * @param array $list 数据集
     * @param string|int $id 当前id
     * @return array
     */
    public function getListChild(array $list, $id): array
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }

        $id = (string)$id;
        $newData = [];
        foreach ($list as $key => $data) {
            $dataParentId = (string)$data[$this->parentIdKey];
            if ($dataParentId == $id) {
                $newData[$key] = $data;
            }
        }

        return $newData ?: [];
    }

    /**
     * 获取ID自己的数据
     * @param array $list 数据集
     * @param string|int $id 当前id
     * @return array
     */
    public function getListSelf(array $list, $id): array
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }

        $id = (string)$id;
        foreach ($list as $data) {
            $dataId = (string)$data[$this->idKey];
            if ($dataId == $id) {
                return $data;
            }
        }
        return [];
    }

    /**
     * 将 buildArray 的结果返回为二维数组
     * @param array $data 数据
     * @return array
     */
    public function buildFormatList(array $data = []): array
    {
        if (empty($data)) {
            return [];
        }
        $list = [];
        foreach ($data as $v) {
            if (!empty($v)) {
                if (!isset($v[$this->spacerKey])) {
                    $v[$this->spacerKey] = '';
                }
                $child = $v[$this->buildChildKey] ?? [];
                $v[$this->hasChildKey] = $child ? 1 : 0;
                unset($v[$this->buildChildKey]);
                $list[] = $v;
                if (!empty($child)) {
                    $list = array_merge($list, $this->buildFormatList($child));
                }
            }
        }
        return $list;
    }
}

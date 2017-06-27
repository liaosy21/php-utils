<?php
/**
 * Created by PhpStorm.
 * User: shiyang
 * Date: 17/6/27
 * Time: 18:08
 */

namespace My;


class ConsistentHash
{

    /**
     * 键值-节点列表
     * @var array
     */
    private $key_node_list = [];
    /**
     * 节点-键值列表
     * @var array
     */
    private $node_key_list = [];
    /**
     * 是否已排序
     * @var bool
     */
    private $has_sorted = false;
    /**
     * 哈希算法类
     * @var Haser|MD5Hasher|null
     */
    private $_hasher = null;
    /**
     * 虚拟节点数
     * @var int
     */
    private $virtual_node_num = 32;
    public function __construct(Haser $hasher = null) {
        $this->_hasher = !is_null($hasher) ? $hasher : new MD5Hasher();
    }
    /**
     * 设置虚拟节点数
     * @param $virtual_node_num
     */
    public function setVirtualNodeNum($virtual_node_num) {
        $this->virtual_node_num = $virtual_node_num;
    }
    /**
     * 添加节点
     * @param $node_name
     *
     * @return $this
     */
    public function addNode($node_name) {
        foreach (range(0, $this->virtual_node_num - 1) as $i) {
            $node_key = $this->_hasher->hash($node_name . $i);
            // $node_key = $this->_hasher->hash($node_name);
            if (!array_key_exists($node_key, $this->key_node_list)) {
                $this->key_node_list[$node_key] = $node_name . '#' . $i;
                $this->node_key_list[$node_name] = $node_key;
                $this->has_sorted = false;
            }
        }
        return $this;
    }
    /**
     * 批量添加节点
     * @param array $nodes
     *
     * @throws Exception
     */
    public function addNodes($nodes = []) {
        if (!is_array($nodes)) {
            throw new \Exception('不合法的参数');
        }
        foreach ($nodes as $node) {
            $this->addNode($node);
        }
    }
    /**
     * 通过 键值 获取对应节点
     * @param $key
     *
     * @return array
     */
    public function getNode($key) {
        if (!$this->has_sorted) {
            ksort($this->key_node_list);
            $this->has_sorted = true;
        }
        $hash_key = $this->_hasher->hash($key);
        $len = count($this->key_node_list);
        if ($len == 0) {
            return [];
        }
        if ($len == 1) {
            return array_values($this->key_node_list);
        }
        $keys = array_keys($this->key_node_list);
        $node_names = array_values($this->key_node_list);
        if ($hash_key > $keys[$len - 1] && $hash_key < $keys[0]) {
            return $node_names[0];
        }

        $results = [];
        $collect = false;
        foreach ($this->key_node_list as $node_key=>$node_name) {
            if (!$collect && $hash_key >= $node_key) {
                $collect = true;
                continue;
            }
            if ($collect && $hash_key <= $node_key && !in_array($node_name, $results)) {
                $results[] = $node_name;
                return $results;
            }
        }
        return [array_shift($this->key_node_list)];
    }
    /**
     * 获取所有节点
     * @return array
     */
    public function getAllNodes() {
        return $this->key_node_list;
    }
}


/**
 * 哈希算法 统一接口
 * Interface Hasher
 */
interface Hasher {
    public function hash($str);
}
/**
 * MD5 哈希算法
 * Class MD5Hasher
 */
class MD5Hasher implements Hasher
{
    /**
     * hash 方法
     * @param $str
     *
     * @return int
     */
    public function hash($str) {
        $str = md5($str);
        $len = 32;
        $seed = 5;
        $hash = 0;
        for ($i = 0; $i < $len; $i++) {
            $hash = $hash + ($hash << $seed) + ord($str{$i});
        }
        return $hash & 0x7FFFFFFF;
    }
}
/**
 * crc32 哈希算法
 * Class Crc32Hasher
 */
class Crc32Hasher implements Hasher
{
    /**
     * hash 方法
     * @param $str
     *
     * @return int
     */
    public function hash($str) {
        return crc32($str);
    }
}
$ch = new ConsistentHash();
$ch->addNode('192.168.20.1');
foreach (range(2, 20) as $i) {
    $ch->addNode('192.168.200.' . $i);
}
foreach (range(1, 99) as $key) {
    echo ($ch->getNode('key' . $key)[0] . PHP_EOL);
}
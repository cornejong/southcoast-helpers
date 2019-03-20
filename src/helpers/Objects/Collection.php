<?php

namespace SouthCoast\Helpers\Objects;

use \ArrayAccess;
use \IteratorAggregate;
use \Countable;
use \Exception;

use SouthCoast\Helpers\ArrayHelper;
use SouthCoast\Helpers\Json;

class Collection implements ArrayAccess, IteratorAggregate, Countable
{
    const STATUS_NEW = 0;
    const STATUS_LOADED = 1;

    private $data = [];
    private $status = self::STATUS_NEW;

    public function __construct(array $data = []) {
        if(!empty($data)) {
            $this->load($data);
        }
    }   

    public function load(array $data) : bool
    {
        $this->status = self::STATUS_LOADED;
        $data = ArrayHelper::sanitize($data);
        return ($this->data = $data) ? true : false;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    public function statusIs(int $status) : bool
    {
        return $status === $this->status ? true : false;
    }

    public function isNew() : bool
    {
        return ($this->status == self::STATUS_NEW) ? true : false;
    }

    public function isLoaded() : bool
    {
        return ($this->status == self::STATUS_LOADED) ? true : false;
    }

    public function reset() : bool
    {
        return ($this->data = []) ? true : false;
    }

    public function offsetSet($offset, $value)
    {          
        if($offset) {
            return ($this->data[$offset] = $value) ? true : false;
        } else {
            return ($this->data[] = $value) ? true : false;
        }
    }

    public function offsetExists($offset) : bool
    {
        return isset($this->data[$offset]) ? true : false;
    }

    public function offsetUnset($offset) : void
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset) 
    {
        return ($this->offsetExists($offset)) ? $this->data[$offset] : null;
    }

    public function getIterator() : IteratorObject
    {
        return new IteratorObject($this->data);
    }

    public function count() : int
    {
        return count($this->data);
    }

    public function countAll() : int
    {
        return ArrayHelper::recursiveCount($this->data);
    }

    public function contains(string $element, bool $strict = true) : bool
    {
        return in_array($element, $this->data, $strict) ? true : false;
    }

    public function get(string $query)
    {
        return ArrayHelper::get($query, $this->data);
    }

    public function search(string $query, &$found, bool $strict = false)
    {
        return ArrayHelper::searchByQuery($query, $this->data, $found, $strict);
    }

    public function asJson()
    {
        return Json::prettyEncode($this->data);
    }

    public function asArray()
    {
        return ArrayHelper::sanitize($this->data);
    }

    public function hibernate()
    {
        return serialize($this);
    }
}
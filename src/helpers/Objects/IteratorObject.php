<?php

namespace SouthCoast\Helpers\Objects;

use \Iterator;

class IteratorObject implements Iterator 
{
    private $data = [];

    public function __construct($data)
    {
        if (is_array($data)) {
            $this->data = $data;
        }
    }

    public function rewind()
    {
        reset($this->data);
    }
  
    public function current()
    {
        return current($this->data);
    }
  
    public function key() 
    {
        return key($this->data);
    }
  
    public function next() 
    {
        return next($this->data);
    }
  
    public function valid()
    {
        $key = key($this->data);
        return ($key !== NULL && $key !== FALSE) ? true : false;
    }

}
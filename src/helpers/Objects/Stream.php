<?php

namespace SouthCoast\Helpers\Objects;

class Stream extends Generator
{
    /**
     * @var mixed
     */
    protected $path;
    /**
     * @var mixed
     */
    protected $mode;
    /**
     * @var mixed
     */
    protected $filters;
    /**
     * @var mixed
     */
    protected $handler;

    /**
     * @var mixed
     */
    protected $context;

    /**
     * @var mixed
     */
    protected $content;

    /**
     * @var int
     */
    public $read_size = 8192;

    /**
     * @param string $path
     * @param string $mode
     */
    public function __construct(string $path = null, string $mode = 'r')
    {
        /* If a path was provided */
        if (!is_null($path)) {
            /* Set the path */
            $this->setPath($path);
            /* Set the mode */
            $this->setMode($mode);
            /* And init the stream */
            $this->init();
        }

        return $this;
    }

    /**
     * @param bool $reload
     */
    protected function init(bool $reload = false)
    {
        if ($reload) {
            unset($this->handler);
        }

        $this->handler = fopen($this->path, $this->mode);

        if ($this->handler === false) {
            throw new \Exception('Could not open stream to: ' . $this->path, 1);
        }

        return $this;
    }

    public function read(): string
    {
        return fread($this->handler, $this->read_size);
    }

    /**
     * @return mixed
     */
    public function readAll(): string
    {
        while ($this->hasContent()) {
            $this->content .= $this->read();
        }
        return $this->content;
    }

    /**
     * @param string $data
     */
    public function write(string $data)
    {
        # code...
    }

    /**
     * @param array $header
     * @param array $data
     */
    public function writeAsCsv(array $header, array $data)
    {
        # code...
    }

    /**
     * @param array $data
     */
    public function writeArray(array $data)
    {
        # code...
    }

    public function hasContent()
    {
        return !feof($this->handler);
    }
}

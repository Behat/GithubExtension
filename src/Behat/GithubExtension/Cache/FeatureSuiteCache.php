<?php

namespace Behat\GithubExtension\Cache;

class FeatureSuiteCache
{
    protected $path;

    public function __construct($path = '.git')
    {
        $this->path = $path;
    }

    public function isFresh($resource, $timestamp)
    {
        if (!file_exists($this->path.'/'.$resource)) {
            return false;
        }

        return filemtime($this->path.'/'.$resource) > $timestamp;
    }

    public function read($resource)
    {
        return unserialize(file_get_contents($this->path.'/'.$resource));
    }

    public function write($resource, $value)
    {
        return file_put_contents($this->path.'/'.$resource, serialize($value));
    }
}


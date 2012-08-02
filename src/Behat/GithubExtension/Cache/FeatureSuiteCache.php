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
        if (!file_exists($this->getPathFor($resource))) {
            return false;
        }

        return filemtime($this->getPathFor($resource)) > $timestamp;
    }

    public function read($resource)
    {
        return unserialize(file_get_contents($this->getPathFor($resource)));
    }

    public function write($resource, $value)
    {
        return file_put_contents($this->getPathFor($resource), serialize($value));
    }

    public function getPathFor($resource)
    {
        return rtrim($this->path, '/').'/'.trim($resource, '/');
    }
}


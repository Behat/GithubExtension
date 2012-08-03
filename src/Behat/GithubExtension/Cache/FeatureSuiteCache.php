<?php

namespace Behat\GithubExtension\Cache;

use Behat\Gherkin\Node\FeatureNode;

class FeatureSuiteCache
{
    protected $path;

    public function __construct($path = '.git')
    {
        $this->path = $path;
    }

    public function read($path)
    {
        return unserialize(file_get_contents($path));
    }

    public function all()
    {
        if (!$handler = @opendir($this->getPathFor(''))) {
            return array();
        }

        $features = array();
        while (false !== $file = readdir($handler)) {
            if (in_array($file,  array('.', '..'))) {
                continue;
            }

            $features[] = $this->read($this->getPathFor($file));
        }

        return $features;
    }

    public function write(FeatureNode $feature)
    {
        $path = $this->getPathFor(md5($feature->getFile()));
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        return file_put_contents($path, serialize($feature));
    }

    public function updateMeta()
    {
        return touch($this->getMetaFilePath());
    }

    public function getPathFor($resource)
    {
        return rtrim($this->path, '/').'/features/'.trim($resource, '/');
    }

    public function getLastModifiedIssuesTimestamp()
    {
        if (file_exists($this->getMetaFilePath())) {
            return filemtime($this->getMetaFilePath());
        }
    }

    private function getMetaFilePath()
    {
        return rtrim($this->path, '/').'/issues.meta';
    }
}


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

    public function write(FeatureNode $feature)
    {
        return file_put_contents($this->getPathFor($feature), serialize($feature));
    }

    public function all()
    {
        if (!$handler = opendir($this->getFeatureDirPath())) {
            return array();
        }

        $features = array();
        while (false !== $file = readdir($handler)) {
            if (in_array($file,  array('.', '..'))) {
                continue;
            }

            $features[] = $this->read(sprintf('%s%s', $this->getFeatureDirPath(), $file ));
        }

        return $features;
    }

    public function updateMeta()
    {
        return touch($this->getMetaFilePath());
    }

    public function getLastModifiedIssuesTimestamp()
    {
        if (file_exists($this->getMetaFilePath())) {
            return filemtime($this->getMetaFilePath());
        }
    }

    public function getPathFor(FeatureNode $feature)
    {
        return sprintf('%s%s', $this->getFeatureDirPath(), $this->getCacheKey($feature));
    }

    private function getFeatureDirPath()
    {
        $path = sprintf('%s/features/', rtrim($this->path, '/'));

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    private function getMetaFilePath()
    {
        return sprintf('%s/issues.meta', rtrim($this->path, '/'));
    }

    private function getCacheKey(FeatureNode $feature)
    {
        return null !== $feature ? md5($feature->getFile()) : '';
    }
}

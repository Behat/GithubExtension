<?php

namespace Behat\GithubExtension\Gherkin\Loader\Github\V3;

use Behat\GithubExtension\Cache\FeatureSuiteCache;

use Behat\Gherkin\Exception\ParserException;

use Github\Client;

use Behat\GithubExtension\Gherkin\Node\GithubFeatureNode;

use Behat\Gherkin\Parser;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Loader\AbstractFileLoader;

class Loader extends AbstractFileLoader
{
    protected  $parser;
    protected  $client;
    protected  $user;
    protected  $repository;
    protected  $cache;

    public function __construct(Parser $parser, Client $client, $user, $repository, FeatureSuiteCache $cache)
    {
        $this->parser     = $parser;
        $this->client     = $client;
        $this->user       = $user;
        $this->repository = $repository;
        $this->cache      = $cache;
    }

    /**
     * Checks if current loader supports provided resource.
     *
     * @param mixed $resource Resource to load
     *
     * @return Boolean
     */
    public function supports($resource)
    {
        return true;
    }

    /**
     * Loads features from provided resource.
     *
     * @param mixed $resource Resource to load
     *
     * @return array
     */
    public function load($resource)
    {
        if ($this->cache->isFresh($this->getCacheKey(), $this->getLastModifiedIssuesTimestamp())) {
           return $this->filter($this->cache->read($this->getCacheKey()), $resource);
        }

        $issues = $this->getIssues();

        $features = $this->createFeatureNodes($issues);
        $this->cache->write($this->getCacheKey(), $features);

        return $this->filter($features, $resource);
    }

    private function filter(array $features, $resource)
    {
        if (false === strpos($resource, 'github.com')) {
            return $features;
        }

        return array_filter($features, function($feature) use($resource) {
            return $feature->getFile() === $resource;
        });
    }

    protected function getIssues()
    {
        return $this->client->api('issue')->all($this->user, $this->repository);
    }

    private function getCacheKey()
    {
        return $this->user.'_'.$this->repository;
    }

    protected function getLastModifiedIssuesTimestamp()
    {
        $url = 'repos/'.urlencode($this->user).'/'.urlencode($this->repository).'/issues';
        $httpClient = $this->client->getHttpClient();
        $headers = $httpClient->head($url);

        return strtotime($headers['Last-Modified']);
    }

    private function createFeatureNodes(array $issues)
    {
        $features = [];
        foreach ($issues as $issue) {
            try {
                $features[] = $this->createFeatureNode($issue);
            }
            catch(ParserException $e) {
                // @TODO log this to output
                var_dump($e->getMessage(), $issue['body']);
                continue;
            }
        }

        return $features;
    }

    private function createFeatureNode(array $issue)
    {
        // clean issue content to get only parseable features
        $body = str_replace(['```gherkin', '``` gherkin', '```'], '', $issue['body']);

        $node = $this->parser->parse($body, $issue['html_url']);

        foreach ($issue['labels'] as $label) {
            $node->addTag($label['name']);
        }

        if (isset($issue['assignee']['login'])) {
            $node->addTag('assignee:'.str_replace(array(' ', '@'), '_', $issue['assignee']['login']));
        }

        if (isset($issue['milestone']['title'])) {
            $node->addTag('milestone:'.str_replace(array(' ', '@'), '_', $issue['milestone']['title']));
        }

        return $node;
    }
}


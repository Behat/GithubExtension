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
        return 0     === strpos($resource, 'github') ||
               false !== strpos($resource, 'github.com')
        ;
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
           return $this->cache->read($this->getCacheKey());
        }

        $issues = $this->getIssues($resource);

        $features = $this->createFeatureNodes($issues);
        $this->cache->write($this->getCacheKey(), $features);

        return $features;
    }

    protected function getIssues($resource)
    {
        $parameters = $this->getParameters($resource);

        return $this->client->api('issue')->all($this->user, $this->repository, $parameters);
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

    protected function getParameters($resource)
    {
        $parsedUrl = parse_url($resource);
        $queryString = @$parsedUrl['query'];
        parse_str($queryString, $parameters);

        return $parameters;
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

        return $this->parser->parse($body, 'github:'.$issue['number']);
    }
}


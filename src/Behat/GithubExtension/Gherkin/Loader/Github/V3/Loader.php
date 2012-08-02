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
        return 0 === strpos($resource, 'github');
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
        $parameters = $this->getParameters($resource);

        if ($this->cache->isFresh($this->user.$this->repository, $this->getLastModifiedIssuesTimestamp())) {
           return $this->cache->read($this->user.$this->repository);
        }

        $issues = $this->client->api('issue')->all($this->user, $this->repository, $parameters);

        $features = $this->createFeatureNodes($issues);
        $this->cache->write($this->user.$this->repository, $features);

        return $features;
    }

    private function getLastModifiedIssuesTimestamp()
    {
        $url = 'repos/'.urlencode($this->user).'/'.urlencode($this->repository).'/issues';
        $httpClient = $this->client->getHttpClient();
        $headers = $httpClient->head($url);

        return strtotime($headers['Last-Modified']);
    }

    private function getParameters($resource)
    {
        $parsedUrl = parse_url($resource);
        $queryString = @$parsedUrl['query'];
        parse_str($queryString, $parameters);

        return $parameters ?: ['labels' => 'Feature'];
    }


    private function createFeatureNodes(array $issues)
    {
        foreach ($issues as $issue) {
            $features[] = $this->createFeatureNode($issue);
        }

        return $features;
    }

    private function createFeatureNode(array $issue)
    {
        $body = str_replace(['```gherkin', '``` gherkin', '```'], '', $issue['body']);

        try {
            $feature = $this->parser->parse($body, 'github:'.$issue['number']);
        }
        catch(ParserException $e) {
            var_dump($e->getMessage(), $issue['body']);
            continue;
        }

        return $feature;
    }
}


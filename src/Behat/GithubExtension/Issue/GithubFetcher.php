<?php

namespace Behat\GithubExtension\Issue;

use Behat\GithubExtension\Issue\FetcherInterface;
use Behat\GithubExtension\Cache\FeatureSuiteCache;

use Behat\Gherkin\Parser;
use Behat\Gherkin\Exception\ParserException;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\FeatureNode;

use Github\Client;

class GithubFetcher implements FetcherInterface
{
    protected $client;
    protected $cache;
    protected $user;
    protected $repository;

    public function __construct(Client $client, Parser $parser, $user, $repository, FeatureSuiteCache $cache)
    {
        $this->client     = $client;
        $this->cache      = $cache;
        $this->user       = $user;
        $this->repository = $repository;
        $this->parser     = $parser;
    }

    public function getIssues()
    {
        $timestamp = $this->cache->getLastModifiedIssuesTimestamp();
        $parameters = array();
        if ($timestamp) {
            $this->client->setHeaders(array('If-Modified-Since', date('c', $timestamp)));
            $parameters = array(
                'since' => date('c', $timestamp),
            );
        }

        $issues = $this->fetchIssues();
        $this->cache->updateMeta();

        foreach ($this->createFeatureNodes($issues) as $feature) {
            $this->cache->write($feature);
        }

        return $this->cache->all();
    }

    protected function fetchIssues()
    {
        return $this->client->api('issue')->all($this->user, $this->repository, $parameters);
    }

    private function createFeatureNodes(array $issues)
    {
        $features = array();
        foreach ($issues as $issue) {
            try {
                $features[] = $this->createFeatureNode($issue);
            }
            catch(ParserException $e) {
                // @TODO log this to output
                continue;
            }
        }

        return $features;
    }

    private function createFeatureNode(array $issue)
    {
        // clean issue content to get only parseable features
        $body = str_replace(array('```gherkin', '``` gherkin', '```'), '', $issue['body']);

        $node = $this->parser->parse($body, $issue['html_url']);
        if (!$node instanceof FeatureNode) {
            throw new ParserException('No feature inside');
        }

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


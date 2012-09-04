<?php

namespace Behat\GithubExtension\Issue;

use Behat\Gherkin\Node\FeatureNode;
use Github\Client;
use Behat\GithubExtension\Issue\UrlExtractor;

class IssueManager
{
    private $client;
    private $urlExtractor;

    public function __construct(Client $client, UrlExtractor $urlExtractor, $basePath)
    {
        $this->basePath     = $this->getBasePath($basePath);
        $this->client       = $client;
        $this->urlExtractor = $urlExtractor;
    }

    private function getBasePath($basePath)
    {
        if (is_dir($basePath.'/.git')) {
            return $basePath;
        }

        throw new \RuntimeException('No .git directory in '.$basePath);
    }

    public function isMappedToGithubIssue(FeatureNode $feature)
    {
        if (is_file($feature->getFile())) {
            $description = $feature->getDescription();
            if (is_array($matches = $this->urlExtractor->getMatches($description))) {
                return true;
            }
        }

        if (is_array($matches = $this->urlExtractor->getMatches($feature->getFile()))) {
            return true;
        }

        return false;
    }

    public function createIssueFor(FeatureNode $feature)
    {
        $params = array(
            'title' => $this->generateTitle($feature),
            'body'  => $this->generateBody($feature),
        );

        $response = $this->client->createIssue($params);

        if (isset($response['html_url'])) {
            return $response['html_url'];
        }

        throw new \RuntimeException(sprintf('github responded with: %s', print_r($response, true)));
    }

    private function generateTitle(FeatureNode $feature)
    {
        return ucfirst(str_replace('_', ' ', basename($feature->getFile(), '.feature')));
    }

    private function generateBody(FeatureNode $feature)
    {
        if (is_file($feature->getFile())) {

            return sprintf(
                "Feature file: %s/blob/master/%s\nEdit: %s/edit/master/%s",
                $this->client->getIssuesUrl(),
                $this->getRelativePath($feature),
                $this->client->getIssuesUrl(),
                $this->getRelativePath($feature)
            );
        }
    }

    private function getRelativePath(FeatureNode $feature)
    {
        if (0 === strpos($feature->getFile(), $this->basePath)) {
            return substr($feature->getFile(), strlen($this->basePath) + 1);
        }

        return $feature->getFile();
    }
}

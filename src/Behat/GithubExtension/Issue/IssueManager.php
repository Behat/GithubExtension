<?php

namespace Behat\GithubExtension\Issue;

use Behat\Gherkin\Node\FeatureNode;
use Github\Client;
use Behat\GithubExtension\Issue\UrlExtractor;

class IssueManager implements ManagerInterface
{
    private $client;
    private $urlExtractor;

    public function __construct(
        Client $client,
        UrlExtractor $urlExtractor
    )
    {
        $this->client       = $client;
        $this->urlExtractor = $urlExtractor;
    }

    public function handle(FeatureNode $feature, array $results)
    {
        return $this->createIssueIfNotExist($feature);
    }

    private function createIssueIfNotExist(FeatureNode $feature)
    {
        if (null === $issue = $this->getIssue($feature)) {
            $issue = $this->createIssue($feature);

            // @TODO Automatically update file with issue url
        }

        return $issue;
    }

    /**
     * Get the github issue from the FeatureNode
     *
     * @return string|null the url of the github issue
     */
    protected function getIssue(FeatureNode $feature)
    {
        if (is_file($feature->getFile())) {
            $content = file($feature->getFile(), FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

            // if the first line of the feature is a valid github url
            if (is_array($matches = $this->urlExtractor->getMatches($content[0]))) {
                return $matches[0];
            } 
        }

        // else if the file of the FeatureNode is a valid github url
        if (is_array($matches = $this->urlExtractor->getMatches($feature->getFile()))) {
            return $matches[0];
        }
    }

    private function createIssue(FeatureNode $feature)
    {
        $params = array(
            'title' => $this->generateTitle($feature),
            'body'  => $this->generateBody($feature),
        );

        return $this->client->createIssue($params);
    }

    private function generateTitle(FeatureNode $feature)
    {
        return ucfirst(str_replace('_', ' ', basename($feature->getFile(), '.feature')));
    }

    private function generateBody(FeatureNode $feature)
    {
        if (is_file($feature->getFile())) {
            return sprintf("```gherkin \n%s \n```", file_get_contents($feature->getFile()));
        }
    }
}

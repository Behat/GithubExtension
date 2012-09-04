<?php

namespace Behat\GithubExtension\Issue;

use Behat\GithubExtension\DataCollector\IssueDataCollector;
use Github\Client;
use Behat\Behat\Event\StepEvent;
use Behat\Gherkin\Node\FeatureNode;

class CommentManager implements ManagerInterface
{
    private $statuses = array(
        StepEvent::PASSED    => 'Passed',
        StepEvent::SKIPPED   => 'Skipped',
        StepEvent::PENDING   => 'Pending',
        StepEvent::UNDEFINED => 'Undefined',
        StepEvent::FAILED    => 'Failed'
    );
    private $urlExtractor;
    private $client;
    private $generator;

    public function __construct(
        UrlExtractor $urlExtractor,
        Client $client,
        GeneratorInterface $generator
    )
    {
        $this->urlExtractor = $urlExtractor;
        $this->client       = $client;
        $this->generator    = $generator;
    }

    public function handle(FeatureNode $feature, array $results)
    {
        $comment = $this->generator->render($this->createResult($results));

        return $this->postComment($feature, $comment);
    }

    private function createResult(array $results)
    {
        $res = array();

        foreach ($results['scenarios'] as $scenario => $result) {
            $res[$scenario] = $this->statuses[$result];
        }

        return $res;
    }

    private function postComment(FeatureNode $feature, $comment)
    {
        if (empty($comment)) {
            throw new \InvalidArgumentException(
                'You must provide a non empty content for the comment to post.'
            );
        }

        return $this
            ->client
            ->api('issue')
            ->comments()
            ->create(
                $this->urlExtractor->getUser($feature->getFile()),
                $this->urlExtractor->getRepository($feature->getFile()),
                $this->urlExtractor->getIssueNumber($feature->getFile()),
                array('body' => $comment)
            )
        ;
    }
}

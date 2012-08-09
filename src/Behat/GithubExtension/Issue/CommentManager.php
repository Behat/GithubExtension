<?php

namespace Behat\GithubExtension\Issue;

use Behat\GithubExtension\DataCollector\IssueDataCollector;
use Github\Client;

class CommentManager implements ManagerInterface
{
    private $dataCollector;
    private $client;
    private $generator;
    private $user;
    private $repository;

    public function __construct(
        IssueDataCollector $dataCollector,
        Client $client,
        GeneratorInterface $generator,
        $user,
        $repository
    )
    {
        $this->dataCollector = $dataCollector;
        $this->client        = $client;
        $this->generator     = $generator;
        $this->user          = $user;
        $this->repository    = $repository;
    }

    public function handle($issueNumber)
    {
        $result  = $this->dataCollector->getScenarioResult();
        $comment = $this->generator->render($result);

        return $this->postComment($issueNumber, $comment);
    }

    private function postComment($issueNumber, $comment)
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
                $this->user,
                $this->repository,
                $issueNumber,
                array('body' => $comment)
            )
        ;
    }
}

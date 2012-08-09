<?php

namespace Behat\GithubExtension\Issue;

use Behat\GithubExtension\DataCollector\IssueDataCollector;
use Github\Client;

class CommentManager implements ManagerInterface
{
    private $dataCollector;
    private $client;
    private $user;
    private $repository;

    public function __construct(
        IssueDataCollector $dataCollector,
        Client $client,
        $user,
        $repository
    )
    {
        $this->dataCollector = $dataCollector;
        $this->client        = $client;
        $this->user          = $user;
        $this->repository    = $repository;
    }

    public function handle($issueNumber)
    {
        return $this->postComment($issueNumber, $this->generateComment());
    }

    private function generateComment()
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../views');
        $twig   = new \Twig_Environment($loader, array());
        $result = $this->dataCollector->getScenarioResult();

        return $twig->render('result.md.twig', array(
            'run_date' => new \DateTime(),
            'results'  => $result,
        ));
    }

    private function postComment($issueNumber, $comment)
    {
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

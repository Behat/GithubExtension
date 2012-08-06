<?php

namespace Behat\GithubExtension\Listener;

use Github\Client;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Event\SuiteEvent;
use Behat\Behat\Event\FeatureEvent;

class FeatureListener implements EventSubscriberInterface
{
    protected $client;
    protected $user;
    protected $repository;
    protected $auth;
    protected $urlPattern;
    protected $result;

    public function __construct(Client $client, $user, $repository, array $auth, $urlPattern)
    {
        $this->client     = $client;
        $this->user       = $user;
        $this->repository = $repository;
        $this->auth       = $auth;
        $this->urlPattern = $urlPattern;
    }

    public static function getSubscribedEvents()
    {
        $events = array('afterScenario', 'afterFeature');

        return array_combine($events, $events);
    }

    public function afterScenario(ScenarioEvent $event)
    {
        $statuses = array(
            StepEvent::PASSED      => 'Passed',
            StepEvent::SKIPPED     => 'Skipped',
            StepEvent::PENDING     => 'Pending',
            StepEvent::UNDEFINED   => 'Undefined',
            StepEvent::FAILED      => 'Failed'
        );

        $this->result[$event->getScenario()->getTitle()] =
            $statuses[$event->getResult()];
    }

    public function afterFeature(FeatureEvent $event)
    {
        $feature = $event->getFeature();

        if (!preg_match($this->urlPattern, $feature->getFile(), $matches)) {
            return;
        }
        $issueNumber = $matches[3];

        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../views');
        $twig = new \Twig_Environment($loader, array());

        $comment = $twig->render('result.md.twig', array(
            'run_date' => new \DateTime(),
            'results' => $this->result,
        ));

        $response = $this->postComment($comment, $issueNumber);
    }

    private function postComment($message, $number)
    {
        if (isset($this->auth['token'])) {
            $this->client->authenticate($this->auth['token'], $this->auth['token'], Client::AUTH_HTTP_TOKEN);
        }
        else {
            $this->client->authenticate($this->auth['username'], $this->auth['password'], Client::AUTH_HTTP_PASSWORD);
        }

        return $this->client->api('issue')->comments()->create($this->user, $this->repository, $number, array('body' => $message));
    }
}


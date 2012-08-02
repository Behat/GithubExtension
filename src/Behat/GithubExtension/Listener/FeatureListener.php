<?php

namespace Behat\GithubExtension\Listener;

use Github\Client;

use Behat\GithubExtension\Gherkin\Node\GithubFeatureNode;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;

class FeatureListener implements EventSubscriberInterface
{
    protected $client;
    protected $user;
    protected $password;
    protected $repository;
    protected $auth;

    public function __construct(Client $client, $user, $password, $repository, array $auth)
    {
        $this->client     = $client;
        $this->user       = $user;
        $this->password   = $password;
        $this->repository = $repository;
        $this->auth       = $auth;
    }

    public static function getSubscribedEvents()
    {
        return array('afterScenario' => 'afterScenario');
    }

    public function afterScenario(ScenarioEvent $event)
    {
        $feature = $event->getScenario()->getFeature();
        if (0 !== strpos($feature->getFile(), 'github:')) {
            return;
        }

        $issueNumber = substr($feature->getFile(), 7);

        if (StepEvent::FAILED === $event->getResult()) {
            $this->postComment('Scenario failed', $issueNumber);
        }

        if (StepEvent::PASSED === $event->getResult()) {
            $this->postComment('Scenario passed', $issueNumber);
        }
    }

    private function postComment($message, $number)
    {
        $this->client->authenticate($this->auth['token'], $this->auth['token'], Client::AUTH_HTTP_TOKEN);

        return $this->client->api('issue')->comments()->create($this->user, $this->repository, $number, ['body' => $message]);
    }
}


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
    protected $repository;
    protected $auth;
    protected $urlPattern;

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
        return array('afterScenario' => 'afterScenario');
    }

    public function afterScenario(ScenarioEvent $event)
    {
        $scenario = $event->getScenario();
        $feature = $scenario->getFeature();
        if (!preg_match($this->urlPattern, $feature->getFile(), $matches)) {
            return;
        }
        $issueNumber = $matches[3];

        if (StepEvent::FAILED === $event->getResult()) {
            $this->postComment(sprintf('Scenario "%s" failed', $scenario->getTitle()), $issueNumber);
        }

        if (StepEvent::PASSED === $event->getResult()) {
            $this->postComment(sprintf('Scenario "%s" passed', $scenario->getTitle()), $issueNumber);
        }
    }

    private function postComment($message, $number)
    {
        if (isset($this->auth['token'])) {
            $this->client->authenticate($this->auth['token'], $this->auth['token'], Client::AUTH_HTTP_TOKEN);
        }
        else {
            $this->client->authenticate($this->auth['username'], $this->auth['password'], Client::AUTH_HTTP_PASSWORD);
        }

        return $this->client->api('issue')->comments()->create($this->user, $this->repository, $number, ['body' => $message]);
    }
}


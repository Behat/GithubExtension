<?php

namespace Behat\GithubExtension\Listener;

use Github\Client;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Event\SuiteEvent;
use Behat\Behat\Event\FeatureEvent;
use Behat\GithubExtension\Issue\ManagerInterface;

class FeatureListener implements EventSubscriberInterface
{
    protected $client;
    protected $auth;
    protected $commentManager;
    protected $labelManager;
    protected $authenticated = false;
    protected $result = array();

    public function __construct(
        Client $client,
        array $auth,
        ManagerInterface $commentManager,
        ManagerInterface $labelManager
    )
    {
        $this->client         = $client;
        $this->auth           = $auth;
        $this->commentManager = $commentManager;
        $this->labelManager   = $labelManager;
    }

    public static function getSubscribedEvents()
    {
        $events = array('afterScenario', 'afterFeature');

        return array_combine($events, $events);
    }

    public function afterScenario(ScenarioEvent $event)
    {
        $this->result['scenarios'][$event->getScenario()->getTitle()] = $event->getResult();
    }

    public function afterFeature(FeatureEvent $event)
    {
        $feature = $event->getFeature();

        $this->result['feature'] = $event->getResult();

        // Necessary or other calls to the githup api will return content of https://github.com/500 (strange...)
        if (!$this->authenticated) {
            $this->authenticate();
        }

        $this->commentManager->handle($feature, $this->result);
        $this->labelManager->handle($feature, $this->result);

        $this->result = array();
    }

    private function authenticate()
    {
        if (isset($this->auth['token'])) {
            $this->client->authenticate($this->auth['token'], $this->auth['token'], Client::AUTH_HTTP_TOKEN);
        } else {
            $this->client->authenticate($this->auth['username'], $this->auth['password'], Client::AUTH_HTTP_PASSWORD);
        }

        $this->authenticated = true;
    }
}


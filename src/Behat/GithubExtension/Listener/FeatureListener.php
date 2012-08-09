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
    protected $user;
    protected $repository;
    protected $auth;
    protected $urlPattern;
    protected $commentManager;
    protected $labelManager;
    protected $authenticated = false;

    public function __construct(
        Client $client,
        $user,
        $repository,
        array $auth,
        $urlPattern,
        ManagerInterface $commentManager,
        ManagerInterface $labelManager
    )
    {
        $this->client         = $client;
        $this->user           = $user;
        $this->repository     = $repository;
        $this->auth           = $auth;
        $this->urlPattern     = $urlPattern;
        $this->commentManager = $commentManager;
        $this->labelManager   = $labelManager;
    }

    public static function getSubscribedEvents()
    {
        $events = array('afterFeature');

        return array_combine($events, $events);
    }

    public function afterFeature(FeatureEvent $event)
    {
        $feature = $event->getFeature();

        if (!preg_match($this->urlPattern, $feature->getFile(), $matches)) {
            return;
        }
        $issueNumber = $matches[3];

        // Necessary or other calls to the githup api will return content of https://github.com/500 (strange...)
        if (!$this->authenticated) {
            $this->authenticate();
        }

        $this->commentManager->handle($issueNumber);
        $this->labelManager->handle($issueNumber);
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


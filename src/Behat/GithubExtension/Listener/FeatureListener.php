<?php

namespace Behat\GithubExtension\Listener;

use Behat\GithubExtension\Github\Client;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Event\SuiteEvent;
use Behat\Behat\Event\FeatureEvent;
use Behat\GithubExtension\Issue\ManagerInterface;

class FeatureListener implements EventSubscriberInterface
{
    protected $client;
    protected $commentManager;
    protected $labelManager;
    protected $result = array();

    public function __construct(
        Client $client,
        ManagerInterface $commentManager,
        ManagerInterface $labelManager
    )
    {
        $this->client         = $client;
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

        $this->commentManager->handle($feature, $this->result);
        $this->labelManager->handle($feature, $this->result);

        $this->result = array();
    }
}


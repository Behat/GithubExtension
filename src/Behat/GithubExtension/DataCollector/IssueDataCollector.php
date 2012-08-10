<?php

namespace Behat\GithubExtension\DataCollector;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\FeatureEvent;

class IssueDataCollector implements EventSubscriberInterface
{
    private $featureResult  = StepEvent::FAILED;
    private $scenarioResult = array();

    public static function getSubscribedEvents()
    {
        $events = array('afterScenario', 'afterFeature');

        return array_combine($events, $events);
    }

    public function getScenarioResult()
    {
        return $this->scenarioResult;
    }

    public function getFeatureResult()
    {
        return $this->featureResult;
    }

    public function afterScenario(ScenarioEvent $event)
    {
        $this->scenarioResult[$event->getScenario()->getTitle()] = $event->getResult();
    }

    public function afterFeature(FeatureEvent $event)
    {
        $this->featureResult = $event->getResult();
    }
}

<?php

namespace Behat\GithubExtension\DataCollector;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Event\ScenarioEvent;

class CommentDataCollector implements EventSubscriberInterface
{
    private $statuses = array(
        StepEvent::PASSED      => 'Passed',
        StepEvent::SKIPPED     => 'Skipped',
        StepEvent::PENDING     => 'Pending',
        StepEvent::UNDEFINED   => 'Undefined',
        StepEvent::FAILED      => 'Failed'
    );
    private $scenarioResult = array();

    public static function getSubscribedEvents()
    {
        $events = array('afterScenario');

        return array_combine($events, $events);
    }

    public function getScenarioResult()
    {
        return $this->scenarioResult;
    }

    public function afterScenario(ScenarioEvent $event)
    {
        $this->scenarioResult[$event->getScenario()->getTitle()] =
            $this->statuses[$event->getResult()];
    }
}

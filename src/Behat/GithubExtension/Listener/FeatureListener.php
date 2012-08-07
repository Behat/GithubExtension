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
    private $statuses = array(
        StepEvent::PASSED      => 'Passed',
        StepEvent::SKIPPED     => 'Skipped',
        StepEvent::PENDING     => 'Pending',
        StepEvent::UNDEFINED   => 'Undefined',
        StepEvent::FAILED      => 'Failed'
    );
    private $labels = array(
        StepEvent::PASSED      => array('name' => 'passed', 'color' => '02e10c'),
        StepEvent::SKIPPED     => array('name' => 'skipped', 'color' => 'ffcc00'),
        StepEvent::PENDING     => array('name' => 'pending', 'color' => 'ffcc00'),
        StepEvent::UNDEFINED   => array('name' => 'undefined', 'color' => 'ffcc00'),
        StepEvent::FAILED      => array('name' => 'failed', 'color' => 'e10c02')
    );


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
        $this->result[$event->getScenario()->getTitle()] = $this->statuses[$event->getResult()];
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

        $this->authenticate();
        $this->postComment($comment, $issueNumber);
        $this->markIssue($event->getResult(), $issueNumber);
    }

    private function postComment($message, $number)
    {
        return $this->client->api('issue')->comments()->create($this->user, $this->repository, $number, array('body' => $message));
    }

    private function markIssue($featureResult, $issueNumber)
    {
        return $this->setIssueLabel($this->labels[$featureResult], $issueNumber);
    }

    private function setIssueLabel($label, $issueNumber)
    {
        $hasCorrectLabel = false;
        $issueLabels     = $this->client->api('issue')->labels()->all($this->user, $this->repository, $issueNumber);
        if($this->containsLabel($label, $issueLabels)) {
            $hasCorrectLabel = true;
        }

        $labelNames = array_map(function($label) { return $label['name']; }, $this->labels);
        $behatLabels = array_filter($issueLabels, function($label) use ($labelNames) {
            return in_array($label['name'], $labelNames);
        });

        if (!$hasCorrectLabel || ($hasCorrectLabel && count($behatLabels) > 1)) {
            foreach ($behatLabels as $value) {
                $this->client->api('issue')->labels()->remove($this->user, $this->repository, $issueNumber, $value['name']);
            }

            return $this->client->api('issue')->labels()->add($this->user, $this->repository, $issueNumber, $this->findOrCreateLabel($label));
        }
    }

    private function containsLabel(array $label, array &$labels)
    {
        array_walk($labels, function($label, $key) use (&$labels) {
            unset($labels[$key]['url']);
        });

        foreach ($labels as $value) {
            if ($value['name'] === $label['name']) {
                return true;
            }
        }

        return false;
    }

    private function findOrCreateLabel($label)
    {
        $labels = $this->client->api('repo')->labels()->all($this->user, $this->repository);

        if (!$this->containsLabel($label, $labels)) {
             $response = $this->client->api('repo')->labels()->create($this->user, $this->repository, $label);

             return $response['name'];
        } else {
            return $label['name'];
        }
    }

    private function authenticate()
    {
        if (isset($this->auth['token'])) {
            $this->client->authenticate($this->auth['token'], $this->auth['token'], Client::AUTH_HTTP_TOKEN);
        } else {
            $this->client->authenticate($this->auth['username'], $this->auth['password'], Client::AUTH_HTTP_PASSWORD);
        }
    }
}


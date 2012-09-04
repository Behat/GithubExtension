<?php

namespace Behat\GithubExtension\Issue;

use Github\Client;
use Behat\Behat\Event\StepEvent;
use Behat\GithubExtension\DataCollector\IssueDataCollector;
use Behat\Gherkin\Node\FeatureNode;

class LabelManager implements ManagerInterface
{
    private $urlExtractor;
    private $client;
    private $user;
    private $repository;
    private $issueNumber;
    private $labels = array(
        StepEvent::PASSED      => array('name' => 'passed',     'color' => '02e10c'),
        StepEvent::SKIPPED     => array('name' => 'skipped',    'color' => '49afcd'),
        StepEvent::PENDING     => array('name' => 'pending',    'color' => 'ffcc00'),
        StepEvent::UNDEFINED   => array('name' => 'undefined',  'color' => 'ffcc00'),
        StepEvent::FAILED      => array('name' => 'failed',     'color' => 'e10c02')
    );

    public function __construct(
        UrlExtractor $urlExtractor,
        Client $client
    )
    {
        $this->urlExtractor = $urlExtractor;
        $this->client       = $client;
    }

    public function handle(FeatureNode $feature, array $result)
    {
        $hasCorrectLabel   = false;
        $this->issueNumber = $this->urlExtractor->getIssueNumber($feature->getFile());
        $this->user        = $this->urlExtractor->getUser($feature->getFile());
        $this->repository  = $this->urlExtractor->getRepository($feature->getFile());

        $issueLabels  = $this->client->api('issue')->labels()->all($this->user, $this->repository, $this->issueNumber);
        $featureLabel = $this->labels[$result['feature']];

        if($this->containsLabel($featureLabel, $issueLabels)) {
            $hasCorrectLabel = true;
        }

        $reservedLabels = $this->getReservedLabels($issueLabels);

        if (!$hasCorrectLabel || ($hasCorrectLabel && count($reservedLabels) > 1)) {
            foreach ($reservedLabels as $reservedLabel) {
                $this->client->api('issue')->labels()->remove($this->user, $this->repository, $this->issueNumber, $reservedLabel['name']);
            }

            return $this
                ->client
                ->api('issue')
                ->labels()
                ->add(
                    $this->user,
                    $this->repository,
                    $this->issueNumber,
                    $this->findOrCreateLabel($featureLabel)
                )
            ;
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

    /**
     * Get all reserved labels applied on the current issue.
     *
     * This is usefull when user has manually applied many behat labels
     * through the github issue interface
     */
    public function getReservedLabels(array $issueLabels)
    {
        // We only compare to the label's name because this value is unique
        $labelNames = array_map(function($label) { return $label['name']; }, $this->labels);
        return array_filter($issueLabels, function($label) use ($labelNames) {
            return in_array($label['name'], $labelNames);
        });
    }

    /**
     * Fetch remote repository for a specific label
     * and create it if it doesn't exist
     *
     * @Return the name of the label
     */
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
}

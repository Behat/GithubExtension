<?php

namespace Behat\GithubExtension\Formatter;

use Behat\Behat\Formatter\ConsoleFormatter;
use Behat\Behat\Event\FeatureEvent;
use Github\Client;
use Behat\GithubExtension\Issue\IssueManager;

class IssueCreateFormatter extends ConsoleFormatter
{
    public function getDefaultParameters()
    {
        return array();
    }

    public static function getSubscribedEvents()
    {
        $events = array('afterFeature');

        return array_combine($events, $events);
    }

    public function afterFeature(FeatureEvent $event)
    {
        $feature = $event->getFeature();
        if (false === $this->issueManager->isMappedToGithubIssue($feature)) {
            $url = $this->issueManager->createIssueFor($feature);
            $this->writeln(sprintf(
                '+ {+passed}Github issue for:{-passed} %s',
                $this->relativizePathsInString($feature->getFile())
            ));
            $this->writeln('  {+passed}Add the following line to the feature description:{-passed}');
            $this->writeln(sprintf('    {+undefined}discuss at %s{-undefined}', $url));
            $this->writeln();
        }
    }

    public function setIssueManager(IssueManager $issueManager)
    {
        $this->issueManager = $issueManager;
    }

    protected function relativizePathsInString($string)
    {
        $string = str_replace(getcwd().'/', '', $string);

        return $string;
    }
}

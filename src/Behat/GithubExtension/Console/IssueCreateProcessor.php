<?php

namespace Behat\GithubExtension\Console;

use Behat\GithubExtension\Issue\IssueManager;

use Behat\Behat\Console\Processor\ProcessorInterface;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

class IssueCreateProcessor implements ProcessorInterface
{
    private $issueManager;
    private $hookDispatcher;
    private $formatterManager;

    public function __construct(IssueManager $issueManager, $hookDispatcher, $formatterManager)
    {
        $this->issueManager   = $issueManager;
        $this->hookDispatcher = $hookDispatcher;
        $this->formatterManager = $formatterManager;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command
            ->addOption('--create-issues', null, InputOption::VALUE_NONE,
                "Create Github issues for local features"
            )
        ;
    }

    /**
     * Processes data from container and console input.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('create-issues')) {
            return;
        }

        $this->hookDispatcher->setDryRun(true);
        $this->formatterManager->disableFormatters();
        $formatter = $this->formatterManager->initFormatter('Behat\GithubExtension\Formatter\IssueCreateFormatter');
        $formatter->setIssueManager($this->issueManager);
    }
}

<?php

namespace Behat\GithubExtension\Issue;

use Behat\Gherkin\Node\FeatureNode;

interface ManagerInterface
{
    public function handle(FeatureNode $feature, array $results);
}

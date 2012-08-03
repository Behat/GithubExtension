<?php

namespace Behat\GithubExtension\Issue;

interface FetcherInterface
{
    /**
     * return array of FeatureNode
     */
    public function getIssues();
}


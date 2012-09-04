<?php

namespace Behat\GithubExtension\Issue;

class UrlExtractor
{
    const USER         = 1;
    const REPOSITORY   = 2;
    const ISSUE_NUMBER = 3;

    private $urlPattern;

    public function __construct($urlPattern)
    {
        $this->urlPattern = $urlPattern;
    }

    public function getUser($url)
    {
        $matches = $this->getMatches($url);

        return $matches[self::USER];
    }

    public function getRepository($url)
    {
        $matches = $this->getMatches($url);

        return $matches[self::REPOSITORY];
    }

    public function getIssueNumber($url)
    {
        $matches = $this->getMatches($url);

        return $matches[self::ISSUE_NUMBER];
    }

    public function getMatches($url)
    {
        if (!preg_match($this->urlPattern, $url, $matches)) {
            return null;
        }

        return $matches;
    }
}

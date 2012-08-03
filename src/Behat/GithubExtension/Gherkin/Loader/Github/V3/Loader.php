<?php

namespace Behat\GithubExtension\Gherkin\Loader\Github\V3;

use Behat\GithubExtension\Issue\FetcherInterface;

use Behat\Gherkin\Loader\AbstractFileLoader;

class Loader extends AbstractFileLoader
{
    protected $issueFetcher;

    public function __construct(FetcherInterface $issueFetcher)
    {
        $this->issueFetcher = $issueFetcher;
    }

    /**
     * Checks if current loader supports provided resource.
     *
     * @param mixed $resource Resource to load
     *
     * @return Boolean
     */
    public function supports($resource)
    {
        return true;
    }

    /**
     * Loads features from provided resource.
     *
     * @param mixed $resource Resource to load
     *
     * @return array
     */
    public function load($resource)
    {
        $features = $this->filter(
            $this->issueFetcher->getIssues(),
            $resource
        );

        return $features;
    }

    private function filter(array $features, $resource)
    {
        if (false === strpos($resource, 'github.com')) {
            return $features;
        }

        return array_filter($features, function($feature) use($resource) {
            return $feature->getFile() === $resource;
        });
    }
}


<?php

namespace Behat\GithubExtension\Github;

use Github\HttpClient\HttpClientInterface;

use Github\Client as BaseClient;

class Client extends BaseClient
{
    private $user;
    private $repository;

    public function __construct($user, $repository, array $auth, HttpClientInterface $httpClient = null)
    {
        parent::__construct($httpClient);

        $this->user       = $user;
        $this->repository = $repository;

        if (true === $auth['always']) {
            $this->configureAuthentication($auth);
        }
    }

    public function createIssue(array $params)
    {
        return $this->api('issue')->create($this->user, $this->repository, $params);
    }

    private function configureAuthentication(array $auth)
    {
        if (isset($auth['token'])) {
            return $this->authenticate($auth['token'], $auth['token'], BaseClient::AUTH_HTTP_TOKEN);
        }

        return $this->authenticate($auth['username'], $auth['password'], BaseClient::AUTH_HTTP_PASSWORD);
    }
}


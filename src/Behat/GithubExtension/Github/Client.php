<?php

namespace Behat\GithubExtension\Github;

use Github\HttpClient\HttpClientInterface;

use Github\Client as BaseClient;

class Client extends BaseClient
{
    public function __construct(array $auth, HttpClientInterface $httpClient = null)
    {
        parent::__construct($httpClient);

        if (true === $auth['always']) {
            $this->configureAuthentication($auth);
        }
    }

    private function configureAuthentication(array $auth)
    {
        if (isset($auth['token'])) {
            return $this->authenticate($auth['token'], $auth['token'], BaseClient::AUTH_HTTP_TOKEN);
        }

        return $this->authenticate($auth['username'], $auth['password'], BaseClient::AUTH_HTTP_PASSWORD);
    }
}


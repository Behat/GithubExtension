<?php

namespace Behat\GithubExtension\Github;

use Github\HttpClient\HttpClient as BaseHttpClient;

class HttpClient extends BaseHttpClient
{
    public function head($path, array $parameters = array(), array $options = array())
    {
        $path .= (false === strpos($path, '?') ? '?' : '&').http_build_query($parameters, '', '&');
        $options = array_merge($this->options, $options);

        // create full url
        $url = strtr($options['url'], array(
            ':path' => trim($path, '/')
        ));

        $response = $this->doRequest($url, $parameters, 'HEAD', $options);

        $headers = isset($response['headers']) ? $response['headers'] : array();

        $hash = array();
        foreach ($headers as $header) {
            if (false === strpos($header, ':')) {
                continue;
            }
            list($key, $value) = explode(':', $header, 2);
            $hash[trim($key)] = trim($value);
        }

        return $hash;
    }
}


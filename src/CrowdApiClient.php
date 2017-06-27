<?php

namespace Drupal\crowd_auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception;
use Drupal\crowd_auth\CrowdUserException;
use Drupal\crowd_auth\CrowdGroupException;
use GuzzleHttp\Psr7\Response;


class CrowdApiClient extends Client {
    private $apiVersion = 1;
    const HTTP_SUCCESS = 200;

    function getUser($username, $expand = FALSE) {
        $query = [
            'query' => [
                'username' => $username
            ]
        ];
        if ($expand) {
            $query['query']['expand'] = 'attributes';
        }
        $response = $this->get($this->getRequestUrl('usermanagement','user'), $query);
        if ($response->getStatusCode() === CrowdApiClient::HTTP_SUCCESS) {
            return $this->getEncodedJson($response);
        }
        throw new CrowdUserException($response->getStatusCode());
    }

    private function getRequestUrl($module, $method) {
        $uri = $this->getConfig('base_uri');
        return implode('/', [$module, $this->apiVersion, $method]);
    }

    private function getEncodedJson(Response $response) {
        return json_decode($response->getBody(), true);
    }

    function authentication($username, $password) {
        $query = [
            'query' => [
                'username' => $username
            ],
            'json' => [
                'value' => $password
            ]
        ];
        $response = $this->post($this->getRequestUrl('usermanagement', 'authentication'), $query);
        if ($response->getStatusCode() === CrowdApiClient::HTTP_SUCCESS) {
            return $this->getEncodedJson($response);
        }
        throw new CrowdUserException($response->getStatusCode());
    }

    function getDirectGroups($username, $expand = false) {
        $query = [
             'query' => [
                'username' => $username
            ]
        ];
        if ($expand) {
            $query['query']['expand'] = 'group';
        }
        $response = $this->get($this->getRequestUrl('usermanagement','user/group/direct'),$query);
        if ($response->getStatusCode() === CrowdApiClient::HTTP_SUCCESS) {
            return $this->getEncodedJson($response);
        }
        throw new CrowdGroupException($response->getStatusCode());
    }

}
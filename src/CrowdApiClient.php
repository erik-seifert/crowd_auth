<?php

namespace Drupal\crowd_auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception;
use Drupal\crowd_auth\CrowdUserException;
use Drupal\crowd_auth\CrowdGroupException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;


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
        try {
            $response = $this->get($this->getRequestUrl('usermanagement','user'), $query);
        } catch (ConnectException $e) {
            throw new CrowdUserException(404);
        }
        if ($response->getStatusCode() === CrowdApiClient::HTTP_SUCCESS) {
            return $this->getEncodedJson($response);
        }
        throw new CrowdUserException($response->getStatusCode());
    }

    private function getRequestUrl($module, $method) {
        $uri = $this->getConfig('base_uri');
        return implode('/', ['rest', $module, $this->apiVersion, $method]);
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
        try {
            $response = $this->post($this->getRequestUrl('usermanagement', 'authentication'), $query);
        } catch (ConnectException $e) {
            throw new CrowdUserException(404);
        }
        if ($response->getStatusCode() === CrowdApiClient::HTTP_SUCCESS) {
            return $this->getEncodedJson($response);
        }
        throw new CrowdUserException($response->getStatusCode());
    }

    function search($type) {
        $query = [
            'query' => [
                'entity-type' => $type
            ]
        ];
        try {
            $response = $this->get($this->getRequestUrl('usermanagement', 'search'), $query);
        } catch (ConnectException $e) {
            throw new CrowdUserException(404);
        }
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
        try {
            $response = $this->get($this->getRequestUrl('usermanagement','user/group/direct'),$query);
        } catch (ConnectException $e) {
            throw new CrowdUserException(404);
        }
        if ($response->getStatusCode() === CrowdApiClient::HTTP_SUCCESS) {
            return $this->getEncodedJson($response);
        }
        throw new CrowdGroupException($response->getStatusCode());
    }

}
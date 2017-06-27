<?php

namespace Drupal\crowd_auth;

use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\crowd_auth\Entity\Server;
use Drupal\crowd_auth\CrowdApiClient;
use Drupal\crowd_auth\EffectiveUrlMiddleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Cookie\SessionCookieJar;

class CrowdClientFactory {
    private $logger;

    public function __construct(LoggerChannelFactory $logger) {
        $this->logger = $logger->get('crowd_auth');
    }

    public function get(Server $server, array $conf = []) {
        $stack = HandlerStack::create();
        $stack->push(EffectiveUrlMiddleware::middleware());
        $jar = new SessionCookieJar('crowd_cookie_jar', true);

        $config = [
            'cookies' => $jar,
            'http_errors' => false,
            'base_uri' => $server->get('address') . '/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Atlassian-Token' => 'no-check'
            ],
            'auth' => [
                $server->get('app_login'), $server->get('app_pass')
            ],
            'handler' => $stack
        ];

        if (substr($config['base_uri'],-1) !== '/') {
            $config['base_uri'] += '/';
        }

        $config += $conf;
        return new CrowdApiClient($config);
    }

}
<?php

namespace Drupal\crowd_auth;

use GuzzleHttp\Command\Exception\CommandException;
use Drupal\crowd_auth\Entity\Server;

class CrowdUserException extends \Exception {
    static function getInstance($statusCode) {
        if ($statusCode === 403) {
            return new CrowdUserException(t('Could not authenticat user'), Server::ERROR_USER_COULD_NOT_AUTHENTICATE, $e);
        }
        if ($statusCode === 404) {
            return new CrowdUserException(t('User not found'), Server::ERROR_USER_NOT_FOUND);
        }
        if ($statusCode === 400) {
            return new CrowdUserException(t('Bad request'), Server::ERROR_UNKNOW);
        }
        return new CrowdUserException(t('Server not reachable'), Server::ERROR_UNKNOW);
    }
}
<?php

namespace Drupal\crowd_auth;

class CrowdGroupException extends Exception {
    static function getInstance($status_code) {
        if ($status_code === 403) {
            return new CrowdUserException(t('Could not authenticat user'), Server::ERROR_USER_COULD_NOT_AUTHENTICATE, $e);
        }
        if ($status_code === 404) {
            return new CrowdUserException(t('Group not found'), Server::ERROR_USER_NOT_FOUND);
        }
        if ($status_code === 400) {
            return new CrowdUserException(t('Bad request'), Server::ERROR_UNKNOW);
        }
        return new CrowdUserException(t('Server not reachable'), Server::ERROR_UNKNOW);
    }
}
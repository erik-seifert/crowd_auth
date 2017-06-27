<?php

namespace Drupal\crowd_auth;

class CrowdGroupException extends Exception {
    static function getInstance(CommandException $e) {
        if ($e->getResponse()) {
            if ($e->getResponse()->getStatusCode() === 403) {
                return new CrowdUserException(t('Could not authenticat user'), Server::ERROR_USER_COULD_NOT_AUTHENTICATE, $e);
            }
            if ($e->getResponse()->getStatusCode() === 404) {
                return new CrowdUserException(t('Group not found'), Server::ERROR_USER_NOT_FOUND);
            }
            if ($e->getResponse()->getStatusCode() === 400) {
                return new CrowdUserException(t('Bad request'), Server::ERROR_UNKNOW);
            }
        }
        if ($e->getRequest()) {
            return new CrowdUserException(t('Server not reachable'), Server::ERROR_NOT_REACHABLE);
        }
        return new CrowdUserException(t('Server not reachable'), Server::ERROR_UNKNOW);
    }
}
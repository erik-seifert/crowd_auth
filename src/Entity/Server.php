<?php

namespace Drupal\crowd_auth\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use bconnect\crowd\api\CrowdClient;
use GuzzleHttp\Command\Exception\CommandException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Command\Exception\CommandClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception as GuzzleException;

/**
 * Defines the Server entity.
 *
 * @ConfigEntityType(
 *   id = "crowd_server",
 *   label = @Translation("Crowd Server"),
 *   handlers = {
 *     "list_builder" = "Drupal\crowd_auth\ServerListBuilder",
 *     "form" = {
 *       "add" = "Drupal\crowd_auth\Form\ServerForm",
 *       "edit" = "Drupal\crowd_auth\Form\ServerForm",
 *       "delete" = "Drupal\crowd_auth\Form\ServerDeleteForm",
 *       "test" = "Drupal\crowd_auth\Form\ServerTestForm",
 *       "enable_disable" = "Drupal\crowd_auth\Form\EnableDisableForm"
 *     }
 *   },
 *   config_prefix = "server",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/people/crowd/server/{server}",
 *     "edit-form" = "/admin/config/people/crowd/server/{server}/edit",
 *     "delete-form" = "/admin/config/people/crowd/server/{server}/delete",
 *     "collection" = "/admin/config/people/crowd/server"
 *   }
 * )
 */

class Server extends ConfigEntityBase {
    protected $client;

    const ERROR_USER_COULD_NOT_AUTHENTICATE = 1;
    const ERROR_USER_NOT_FOUND = 2;
    const ERROR_NOT_REACHABLE = 3;
    const ERROR_UNKNOW = 4;


    /**
     * Connect to crowd server
     *
     * @return boolean;
     */
    public function connect() {
        try {
            $this->client = CrowdClient::create([
                'user' => $this->get('app_login'),
                'pass' => $this->get('app_pass'),
                'cookies' => true,
                'http_errors' => false,
                'base_uri' => $this->get('address') .'/'
            ]);
            return true;
        } catch (CommandException $e) {
            return false;
        } catch (GuzzleException $e) {
            return false;
        }
    }    
    
    /**
     * getUsers
     *
     * @return void
     */
    public function getUsers() {        
        try { 
            return $this->client->getUser(['username' => 'admin']);
        } catch (CommandException $e) {
            return false;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    /**
     * getGroups
     *
     * @return void
     */
    public function getGroups() {
        try { 
          return $this->client->search(['entity-type' => 'group']);
        } catch (ConnectException $ex) {
            return false;
        } catch (CommandException $e) {
            return false;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    /**
     * get a user
     *
     * @param String $username
     * @param boolean $expand
     * @return array
     */
    public function getUser($username, $expand = false) {
        try {
            $params = ['username' => $username];
            if ($expand) {
                $params['expand'] = 'fields';
            }
            return $this->client->getUser($params);
        } catch (CommandException $e) {
            return false;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    /**
     * authenticate
     *
     * @param [type] $username
     * @param [type] $password
     * @return void
     */
    public function authentication($username, $password) {
        try {
            return $this->client->authentication([
                'username' => $username,
                'password' => $password
            ]);
        } catch (CommandException $e) {
            if ($e->getResponse()->getStatusCode() === 403) {
                return Server::ERROR_USER_COULD_NOT_AUTHENTICATE;
            }
            if ($e->getResponse()->getStatusCode() === 404) {
                return Server::ERROR_USER_NOT_FOUND;
            }
            return Server::ERROR_UNKNOW;
        }
    }

    /**
     * registerAndLogin
     *
     * @param [type] $username
     * @param [type] $password
     * @return void
     */
    public function registerAndLogin($username, $password) {
        $result = $this->authentication($username, $password);
        $authService = \Drupal::service('externalauth.externalauth');
        if (!is_object($result)) {
            return $result;
        }

        $authname = $result['name'];
        $account_data = [
            'name' => $result['name'],
            'mail' => $result['email']
        ];
        $authmap_data = [
        'server_id' => $this->id(),
        ] + (array) $result;

        $account = $authService->login($username, CROWD_AUTH_PROVIDER);
        if (!$account) {
            $account = $authService->register($username, CROWD_AUTH_PROVIDER, $account_data, $authmap_data);
        }
        if (!$account) {
            return false;
        }
        $authService->login($username, CROWD_AUTH_PROVIDER);
        return $account;    
    }

}
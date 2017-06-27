<?php

namespace Drupal\crowd_auth\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use bconnect\crowd\api\CrowdClient;
use GuzzleHttp\Command\Exception\CommandException;
use GuzzleHttp\Command\Exception\CommandClientException;
use Drupal\crowd_auth\CrowdUserException;
use Drupal\crowd_auth\CrowdGroupException;
use Drupal\user\Entity\Role;

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
 *       "mapping" = "Drupal\crowd_auth\Form\MappingForm",
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
    protected $client = null;

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
        $this->client = CrowdClient::create([
            'user' => $this->get('app_login'),
            'pass' => $this->get('app_pass'),
            'cookies' => true,
            'http_errors' => false,
            'debug' => false,
            'base_uri' => $this->get('address') .'/'
        ]);
    }

    public function ping() {
        $this->connect();
        $this->authentication($this->get('admin_login'), $this->get('admin_pass'));
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
            throw CrowdUserException::getInstance($e);
        }
    }

    /**
     * getGroups
     *
     * @return void
     */
    public function getGroups($expand = false) {
        try {
          if ($expand) {
            return $this->client->search(['entity-type' => 'group', 'expand' => 'group']);
          }
          return $this->client->search(['entity-type' => 'group']);
        } catch (CommandException $e) {
            throw CrowdGroupException::getInstance($e);
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
            throw CrowdUserException::getInstance($e);
        }
    }

    public function getUserGroups($username, $expand = false) {
        try {
            $params = ['username' => $username];
            if ($expand) {
                $params['expand'] = 'group';
            }
            return $this->client->getUserDirectGroups($params);
        } catch (CommandException $e) {
            throw CrowdUserException::getInstance($e);
        }
    }

    public function getMappingForGroup($remoteGroup) {
        $maps = $this->get('group_mapping');
        foreach ($maps as $key => $map) {
            if ($map['group_remote'] == $remoteGroup) {
                return Role::load($map['group_drupal']);
            }
        }
        return false;
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
            $result = $this->client->authentication([
                'username' => $username,
                'password' => $password
            ]);
            return $result;
        } catch (CommandException $e) {
            throw CrowdUserException::getInstance($e);
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
        try {
            $result = $this->authentication($username, $password);
        } catch (CrowdUserException $e) {
            return false;
        }
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
        $groups = $this->getUserGroups($username);

        foreach ($groups['groups'] as $group) {
            if ($role = $this->getMappingForGroup($group['name'])) {
                $account->addRole($role->id());
            }
        }

        $account->save();
        try {
            $result = $this->getUser($username);
            if ($result['active'] != 1) {
                $user->block();
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }
        $authService->login($username, CROWD_AUTH_PROVIDER);
        return $account;
    }

}

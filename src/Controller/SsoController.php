<?php

namespace Drupal\crowd_auth\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\crowd_auth\CrowdGroupException;
use Drupal\user\Entity\User;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Routing\LocalRedirectResponse;

/**
 * Class SsoController.
 */
class SsoController extends ControllerBase {

  use RedirectDestinationTrait;

  /**
   * Hello.
   *
   * @return string
   *   Return Hello string.
   */
  public function login($token) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    $servers = \Drupal::service('crowd.servers')->getEnabledServers();
    foreach($servers as $server) {
      try {
        $auth = $server->checkSsoToken($token);
        $user = \Drupal::entityQuery('user')->condition('name', $auth['user']['name'])->execute();
        if (!$user) {
          continue;
        }
        $user = array_shift($user);
        $user = User::load($user);
        if (!$user) {
          return drupal_not_found();
        }
        user_login_finalize($user);
        $response = new LocalRedirectResponse('/');
        return $response;

      } catch (CrowdUserException $ex) {
        return drupal_not_found();
      }
    }
  }

}

<?php

namespace Drupal\crowd_auth;

use Drupal\Core\Url;
use Drupal\crowd_auth\Entity\Server;

/**
 *
 */
class ServerFactory {

  /**
   * @return \Drupal\crowd_auth\Entity\Server
   */
  public function getServerById($sid) {
    return Server::load($sid);
  }

  /**
   * @return \Drupal\crowd_auth\Entity\Server|bool
   */
  public function getServerByIdEnabled($sid) {
    $server = Server::load($sid);
    if ($server && $server->status()) {
      return $server;
    }
    else {
      return FALSE;
    }
  }

  /**
   * @return \Drupal\crowd_auth\Entity\Server[]
   */
  public function getAllServers() {
    $query = \Drupal::entityQuery('crowd_server');
    $ids = $query->execute();
    return Server::loadMultiple($ids);
  }

  /**
   * @return \Drupal\crowd_auth\Entity\Server[]
   */
  public function getEnabledServers() {
    $query = \Drupal::entityQuery('crowd_server')
      ->condition('status', 1);
    $ids = $query->execute();
    return Server::loadMultiple($ids);
  }

}

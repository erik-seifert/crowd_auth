<?php

namespace Drupal\crowd_auth;

use Drupal\Core\Url;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\crowd_auth\Entity\Server;

/**
 * Provides a listing of Server entities.
 */
class ServerListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the server list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['label'] = $this->t('Name');
    $header['type'] = $this->t('Type');
    $header['status'] = $this->t('Enabled');
    $header['address'] = $this->t('Server address');
    $header['current_status'] = $this->t('Server reachable');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];
    $row['label'] = $this->getLabel($entity);
    $row['type'] = $entity->get('type');
    $row['status'] = $entity->get('status') ? 'Yes' : 'No';
    $row['address'] = $entity->get('address');
    $row['current_status'] = $this->checkStatus($entity->id());
    return $row + parent::buildRow($entity);
  }

  /**
   *
   */
  private function checkStatus($server_id) {
    $server = Server::load($server_id);
    //$connection_result = $server->connect();
    if ($server->get('status')) {
      return 1;
    }
    return 0;
  }

  /**
   *
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    // if (!isset($operations['test'])) {
    //   $operations['test'] = [
    //     'title' => $this->t('Test'),
    //     'weight' => 10,
    //     'url' => Url::fromRoute('entity.crowd_server.test_form', ['crowd_server' => $entity->id()]),
    //   ];
    // }
    if ($entity->get('status') == 1) {
      $operations['disable'] = [
        'title' => $this->t('Disable'),
        'weight' => 15,
        'url' => Url::fromRoute('entity.crowd_server.enable_disable_form', ['crowd_server' => $entity->id()]),
      ];
    }
    else {
      $operations['enable'] = [
        'title' => $this->t('Enable'),
        'weight' => 15,
        'url' => Url::fromRoute('entity.crowd_server.enable_disable_form', ['crowd_server' => $entity->id()]),
      ];
    }
    return $operations;
  }

}

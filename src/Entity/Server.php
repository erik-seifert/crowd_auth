<?php

namespace Drupal\crowd_auth\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

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
}
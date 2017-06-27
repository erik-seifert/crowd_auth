<?php

namespace Drupal\crowd_auth\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\crowd_auth\Entity\Server;
use Drupal\crowd_auth\CrowdUserException;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

class MappingForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $server = $this->entity;
    try {
        $server->ping();
    } catch (CrowdUserException $e) {
        $form['disabled'] = [
            '#markup' => t('Could not reach server')
        ];
        return $form;
    }
    try {
        $groups = $server->getGroups(true);
    } catch (CrowdUserException $e) {
        $form['disabled'] = [
            '#markup' => t('Could not retrieve groups')
        ];
        return $form;
    }

    $form['group_mapping'] = [
        '#type'  => 'fieldset',
        '#title' => $this->t('Group mapping'),
        '#tree'  => true
    ];
    $roles = Role::loadMultiple();
    unset($roles[RoleInterface::ANONYMOUS_ID]);
    unset($roles[RoleInterface::AUTHENTICATED_ID]);

    $roleOptions = [-1 => $this->t('No mapping')];
    foreach ($roles as $id => $role) {
        $roleOptions[$role->id()] = $role->label();
    }

    foreach ($groups['groups'] as $key => $group) {
        $role = $server->getMappingForGroup($group['name']);
        $form['group_mapping'][$key]['group_title'] = [
            '#markup' => '<h3>' . $group['name'] . '</h3>'
        ];
        $form['group_mapping'][$key]['group_remote'] = [
            '#type' => 'hidden',
            '#value' => $group['name']
        ];
        $form['group_mapping'][$key]['group_drupal'] = [
            '#type' => 'select',
            '#default_value' => ($role) ? $role->id() : -1,
            '#options' => $roleOptions
        ];
    }
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
      $values = $form_state->getValue('group_mapping');
      foreach ($values as $key => $value) {
          if ($value['group_drupal'] == -1) {
              unset($values[$key]);
          }
      }
      $form_state->setValue('group_mapping', $values);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    try {
       $this->entity->save();
    } catch (Exception $ex) {
      drupal_set_message($ex->getMessage());
    }
    $form_state->setRedirectUrl($this->entity->urlInfo('collection'));
  }

}
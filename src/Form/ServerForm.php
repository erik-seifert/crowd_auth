<?php

namespace Drupal\crowd_auth\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\crowd_auth\Entity\Server;

/**
 * Class ServerForm.
 *
 * @package Drupal\crowd_auth\Form
 */
class ServerForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $server = $this->entity;

    $form['server'] = [
      '#type' => 'details',
      '#title' => t('Server'),
      '#open' => TRUE,
    ];

    $form['server']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => ($server->label()) ? $server->label() : 'local',
      '#description' => $this->t("Choose a unique <strong><em>name</em></strong> for this server configuration."),
      '#required' => TRUE,
    ];

    $form['server']['id'] = [
      '#type' => 'machine_name',
      '#default_value' => ($server->id()) ? $server->id() : 'local',
      '#machine_name' => [
        'exists' => '\Drupal\crowd_auth\Entity\Server::load',
      ],
      '#disabled' => !$server->isNew(),
      '#default' => 'local'
    ];

    /* You will need additional form elements for your custom properties. */
    $form['server']['status'] = [
      '#title' => $this->t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => $server->get('status'),
      '#description' => $this->t('Disable in order to keep configuration without having it active.'),
    ];


    $form['server']['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Server address'),
      '#maxlength' => 255,
      '#default_value' => $server->get('address') ? $server->get('address') : '127.0.0.1',
      '#description' => $this->t("The domain name or IP address of your Crowd Server such as \"ad.unm.edu\".<br>For SSL use the form https://DOMAIN such as \"https://ad.unm.edu\""),
      '#required' => 'http://127.0.0.1:8095',
    ];

    $form['server']['app_login'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App login'),
      '#maxlength' => 255,
      '#default_value' => ($server->get('app_login')) ? $server->get('app_login') : 'shaque',
      '#required' => TRUE,
      '#default' => 'shaque',
    ];

    $form['server']['app_pass'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App password'),
      '#maxlength' => 255,
      '#default_value' => ($server->get('app_pass')) ? $server->get('app_pass') : 'shaque',
      '#required' => TRUE,
      '#default' => 'shaque',
    ];

    $form['server']['admin_login'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Administrator login'),
      '#maxlength' => 255,
      '#default_value' => ($server->get('admin_login')) ? $server->get('admin_login') : 'admin',
      '#required' => TRUE,
      '#default' => 'shaque',
    ];

    $form['server']['admin_pass'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Administrator password'),
      '#maxlength' => 255,
      '#default_value' => ($server->get('admin_pass')) ? $server->get('admin_pass') : 'admin',
      '#required' => TRUE,
      '#default' => 'shaque',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\crowd_auth\Entity\Server $this->entity */
    try {
      $status = $this->entity->save();
    } catch (Exception $ex) {
      drupal_set_message($ex->getMessage());
    }
    

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Server.', [
          '%label' => $this->entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Server.', [
          '%label' => $this->entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($this->entity->urlInfo('collection'));
  }

}

<?php

namespace Drupal\crowd_auth\Form;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\crowd_auth\Entity\Server;


/**
 * Use Drupal\Core\Form\FormBase;.
 */
class ServerTestForm extends EntityForm {

  /**  @var \Drupal\crowd_auth\Entity\Server */
  protected $crowdServer;

  protected $resultsTables = [];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'crowd_servers_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $crowd_server = NULL) {
    if ($crowd_server) {
      $this->crowdServer = $crowd_server;
    }

    $form['#title'] = t('Test Crowd Server Configuration: @server', ['@server' => $this->crowdServer->label()]);

    $properties = [];

    $settings = [
      '#theme' => 'item_list',
      '#items' => $properties,
      '#list_type' => 'ul',
    ];
    $form['server_variables'] = [
      '#markup' => drupal_render($settings),
    ];

    $form['id'] = [
      '#type' => 'hidden',
      '#title' => t('Machine name for this server'),
      '#default_value' => $this->crowdServer->id(),
    ];


    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Test',
      '#weight' => 100,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $server = Server::load($values['id']);

    if (!$values['id']) {
      $form_state->setErrorByName(NULL, t('No server id found in form'));
    }
    elseif (!$server) {
      $form_state->setErrorByName(NULL, t('Failed to create server object for server with server id=%id', [
        '%id' => $values['id'],
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $has_errors = FALSE;

    // Pass data back to form builder.
    $form_state->setRebuild(TRUE);

    $values = $form_state->getValues();
    $id = $values['id'];
    $this->crowdServer = Server::load($id);

  }

}

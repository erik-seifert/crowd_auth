<?php

namespace Drupal\crowd_auth\Plugin\authorization\Provider;

use Drupal\authorization\AuthorizationSkipAuthorization;
use Drupal\authorization\Entity\AuthorizationProfile;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\authorization\Provider\ProviderPluginBase;

/**
 * @AuthorizationProvider(
 *   id = "crowd_auth_provider",
 *   label = @Translation("Crowd Provider"),
 *   description = @Translation("Provider for crowd authorization.")
 * )
 */
class CrowdAuthorizationProvider  extends ProviderPluginBase {
  public $providerType = 'crowd_auth';
  public $handlers = ['crowd_auth'];

  public $syncOnLogon = TRUE;

  public $revokeProviderProvisioned;
  public $regrantProviderProvisioned;

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\authorization\Entity\AuthorizationProfile $profile */
    $profile = $this->configuration['profile'];
    $tokens = $this->getTokens();
    $tokens += $profile->getTokens();
    if ($profile->hasValidConsumer() && method_exists($profile->getConsumer(), 'getTokens')) {
      $tokens += $profile->getConsumer()->getTokens();
    }
    $form['status'] = [
      '#type' => 'fieldset',
      '#title' => t('Base configuration'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $factory = \Drupal::service('crowd.servers');
    $servers = $factory->getEnabledServers();

    if (count($servers) == 0) {
      $form['status']['server'] = [
        '#type' => 'markup',
        '#markup' => t('<strong>Warning</strong>: You must create an LDAP Server first.'),
      ];
      drupal_set_message(t('You must create an LDAP Server first.'), 'warning');
    }
    else {
      $server_options = [];
      foreach ($servers as $id => $server) {
        /** @var \Drupal\ldap_servers\Entity\Server $server */
        $server_options[$id] = $server->label() . ' (' . $server->get('address') . ')';
      }
    }

    $provider_config = $profile->getProviderConfig();

    if (!empty($server_options)) {
      if (isset($provider_config['status'])) {
        $default_server = $provider_config['status']['server'];
      }
      elseif (count($server_options) == 1) {
        $default_server = key($server_options);
      }
      else {
        $default_server = '';
      }
      $form['status']['server'] = [
        '#type' => 'radios',
        '#title' => t('LDAP Server used in @profile_name configuration.', $tokens),
        '#required' => 1,
        '#default_value' => $default_server,
        '#options' => $server_options,
      ];
    }

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param int $index
   * @return array
   */
  public function buildRowForm(array $form, FormStateInterface $form_state, $index = 0) {
    $row = [];
    /** @var \Drupal\authorization\Entity\AuthorizationProfile $this->configuration['profile'] */
    $mappings = $this->configuration['profile']->getProviderMappings();
    $row['query'] = [
      '#type' => 'textfield',
      '#title' => t('LDAP query'),
      '#default_value' => isset($mappings[$index]) ? $mappings[$index]['query'] : NULL,
    ];
    $row['is_regex'] = [
      '#type' => 'checkbox',
      '#title' => t('Is this query a regular expression?'),
      '#default_value' => isset($mappings[$index]) ? $mappings[$index]['is_regex'] : NULL,
    ];

    return $row;
  }
}
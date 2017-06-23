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
    return $form;
  }
}
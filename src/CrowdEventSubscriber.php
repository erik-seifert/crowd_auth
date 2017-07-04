<?php

namespace Drupal\crowd_auth;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Subscribe to KernelEvents::REQUEST events and redirect if site is currently
 * in maintenance mode.
 */
class CrowdEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   * 
   * @return array;
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('checkForSsoToken');
    return $events;
  }

  /**
   * This method is called whenever the KernelEvents::REQUEST event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   */
  public function checkForSsoToken(GetResponseEvent $event) {
    // If system maintenance mode is enabled, redirect to a different domain.
    if (!(\Drupal::currentUser()->isAnonymous())) {
      return;
    }
    $request = \Drupal::service('request_stack')->getCurrentRequest();
    $cookies = $request->cookies->all();
  }
}
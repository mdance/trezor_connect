<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Routing\UserLoginRouteSubscriber.
 */

namespace Drupal\trezor_connect\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class UserLoginRouteSubscriber extends RouteSubscriberBase {

  /**
   * Constructs a new RouteSubscriber.
   */
  public function __construct() {
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $route = $collection->get('user.login');

    if ($route) {
      $route->setDefault('_form', '\Drupal\trezor_connect\Form\TrezorConnectUserLoginForm');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::ALTER] = array(
      'onAlterRoutes',
      0,
    );

    return $events;
  }

}

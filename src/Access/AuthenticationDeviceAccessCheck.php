<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Access\AuthenticationDeviceAccessCheck.
 */

namespace Drupal\trezor_connect\Access;

use Drupal\Core\Access\AccessCheckInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\Routing\Route;
use Drupal\trezor_connect\Enum\Permissions;
use Drupal\trezor_connect\Enum\Routes;

/**
 * Provides the Authentication Device access check.
 */
class AuthenticationDeviceAccessCheck implements AccessCheckInterface {

  /**
   * Constructs a new object.
   */
  public function __construct() {
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    $output = NULL;

    return $output;
  }

  /**
   * Performs an access check.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user being acted upon.
   *
   * $param RouteInterface $route
   *   The current route.
   *
   * @return \Drupal\Core\Access\AccessResult
   */
  public function access(AccountProxyInterface $current_user, AccountInterface $user, RouteMatchInterface $current_route_match) {
    $admin = $current_user->hasPermission(Permissions::ACCOUNTS);

    if ($admin) {
      return AccessResult::allowed();
    }

    $current_uid = $current_user->id();
    $uid = $user->id();

    if ($current_uid == $uid) {
      // User is managing their own account
      $map = array();

      $map[Routes::MANAGE] = array(
        Permissions::VIEW,
        Permissions::DISABLE,
        Permissions::REMOVE,
      );

      $route_name = $current_route_match->getRouteName();

      if (!isset($map[$route_name])) {
        return AccessResult::forbidden();
      }
      else {
        $permissions = $map[$route_name];

        foreach ($permissions as $permission) {
          $result = $current_user->hasPermission($permission);

          if ($result) {
            return AccessResult::allowed();
          }
        }
      }
    }

    return AccessResult::forbidden();
  }

}

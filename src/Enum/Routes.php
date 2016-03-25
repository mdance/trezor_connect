<?php
/**
 * @file
 * Contains \Drupal\trezor_connect\Enum\Routes.
 */

namespace Drupal\trezor_connect\Enum;

use CommerceGuys\Enum\AbstractEnum;

class Routes extends AbstractEnum {

  /**
   * Provides a string containing the administration menu route name.
   */
  const ADMIN = 'trezor_connect.admin';

  /**
   * Provides a string containing the user page route.
   */
  const USER = 'user.page';

  /**
   * Provides a string containing the TREZOR connect manage route.
   */
  const MANAGE = 'trezor_connect.user.manage';

}

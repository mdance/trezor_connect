<?php
/**
 * Contains Drupal\trezor_connect\Enum\Permissions.
 */

namespace Drupal\trezor_connect\Enum;

use CommerceGuys\Enum\AbstractEnum;

class Permissions extends AbstractEnum {

  /**
   * Provides a string containing the administration permission.
   */
  const ADMIN = 'administer authentication device settings';

  /**
   * Provides a string containing the account administration permission.
   */
  const ACCOUNTS = 'administer account authentication devices';

  /**
   * Provides a string containing the login permission.
   */
  const LOGIN = 'login with authentication device';

  /**
   * Provides a string containing the register permission.
   */
  const REGISTER = 'register with authentication device';

  /**
   * Provides a string containing the view permission.
   */
  const VIEW = 'view authentication device';

  /**
   * Provides a string containing the disable permission.
   */
  const DISABLE = 'disable authentication device';

  /**
   * Provides a string containing the remove permission.
   */
  const REMOVE = 'remove authentication device';

  /**
   * Provides a string containing the bypass permission.
   */
  const BYPASS = 'bypass password check';

}

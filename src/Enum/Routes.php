<?php
/**
 * Created by PhpStorm.
 * User: md
 * Date: 24/02/16
 * Time: 11:57 PM
 */

namespace Drupal\trezor_connect\Enum;

use CommerceGuys\Enum\AbstractEnum;

class Routes extends AbstractEnum {

  /**
   * Provides a string containing the administration menu route name.
   */
  const ADMIN = 'trezor_connect.admin';

  /**
   * Provides a string containing the TREZOR connect login callback route.
   */
  const LOGIN = 'trezor_connect.user.login';

  /**
   * Provides a string containing the TREZOR connect register callback route.
   */
  const REGISTER = 'trezor_connect.user.register';

  /**
   * Provides a string containing the TREZOR connect manage route.
   */
  const MANAGE = 'trezor_connect.user.manage';

  /**
   * Provides a string containing the TREZOR connect manage callback route.
   */
  const MANAGE_JS = 'trezor_connect.user.manage.js';

  /**
   * Provides a string containing the TREZOR connect disable authentication
   * device route.
   */
  const MANAGE_DISABLE = 'trezor_connect.user.manage.disable';

  /**
   * Provides a string containing the TREZOR connect remove authentication
   * device route.
   */
  const MANAGE_REMOVE = 'trezor_connect.user.manage.remove';

}

<?php
/**
 * Contains Drupal\trezor_connect\Enum\Modes.
 */

namespace Drupal\trezor_connect\Enum;

use CommerceGuys\Enum\AbstractEnum;

class Modes extends AbstractEnum {

  /**
   * Provides an integer representing the login mode.
   */
  const LOGIN = 0;

  /**
   * Provides an integer representing the register mode.
   */
  const REGISTER = 1;

  /**
   * Provides an integer representing the manage mode.
   */
  const MANAGE = 2;

}

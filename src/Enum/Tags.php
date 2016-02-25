<?php
/**
 * Contains Drupal\trezor_connect\Enum\Tags.
 */

namespace Drupal\trezor_connect\Enum;

use CommerceGuys\Enum\AbstractEnum;

class Tags extends AbstractEnum {

  /**
   * Provides a string containing the trezor:login tag.
   */
  const TREZORLOGIN = 'trezor:login';

  /**
   * Provides a string containing the button tag.
   */
  const BUTTON = 'button';

}

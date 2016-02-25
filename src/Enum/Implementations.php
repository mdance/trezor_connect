<?php
/**
 * Contains Drupal\trezor_connect\Enum\Implementations.
 */

namespace Drupal\trezor_connect\Enum;

use CommerceGuys\Enum\AbstractEnum;

class Implementations extends AbstractEnum {

  /**
   * Provides a string containing the button implementation type.
   */
  const BUTTON = 'button';

  /**
   * Provides a string containing the javascript api implementation type.
   */
  const JS = 'js';

}

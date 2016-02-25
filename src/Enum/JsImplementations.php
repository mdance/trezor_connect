<?php
/**
 * Contains Drupal\trezor_connect\Enum\JsImplementation.
 */

namespace Drupal\trezor_connect\Enum;

use CommerceGuys\Enum\AbstractEnum;

class JsImplementations extends AbstractEnum {

  /**
   * Provides a string representing when the javascript assets should be
   * loaded using the TREZOR Connect CDN.
   */
  const EXTERNAL = 'external';

  /**
   * Provides a string representing when the javascript assets should be
   * loaded locally.
   */
  const INTERNAL = 'internal';

}

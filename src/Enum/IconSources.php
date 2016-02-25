<?php
/**
 * Contains Drupal\trezor_connect\Enum\IconSources.
 */

namespace Drupal\trezor_connect\Enum;

use CommerceGuys\Enum\AbstractEnum;

class IconSources extends AbstractEnum {

  /**
   * Provides a string representing no icon source.
   *
   * TODO: Refactor default to none (reserved keyword issue from enum refactor)
   */
  const NONE = 'none';

  /**
   * Provides a string representing a theme logo icon source.
   */
  const THEME = 'theme';

  /**
   * Provides a string representing a custom icon source.
   */
  const CUSTOM = 'custom';

}

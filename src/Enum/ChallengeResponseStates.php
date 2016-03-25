<?php
/**
 * Contains Drupal\trezor_connect\Enum\ChallengeResponseStates.
 */

namespace Drupal\trezor_connect\Enum;

use CommerceGuys\Enum\AbstractEnum;

class ChallengeResponseStates extends AbstractEnum {

  /**
   * Provides an integer representing the challenge response not found state.
   */
  const NOT_FOUND = 0;

  /**
   * Provides an integer representing the invalid challenge response state.
   */
  const INVALID = 1;

  /**
   * Provides an integer representing the new challenge response state.
   */
  const CREATE = 2;

  /**
   * Provides an integer representing the challenge response exists state.
   */
  const EXISTS = 3;

  /**
   * Provides an integer representing the updated challenge response state.
   */
  const UPDATE = 4;

  /**
   * Provides an integer representing the other account challenge response state.
   */
  const OTHER_ACCOUNT = 5;

}

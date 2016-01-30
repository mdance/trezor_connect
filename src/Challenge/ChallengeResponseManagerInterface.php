<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Challenge\ChallengeResponseManagerInterface.
 */

namespace Drupal\trezor_connect\Challenge;


interface ChallengeResponseManagerInterface {

  /**
   * Returns a challenge response.
   *
   * @return ChallengeResponse|false
   *   The challenge response object or FALSE.
   */
  public function get();

}

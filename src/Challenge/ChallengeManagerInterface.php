<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Challenge\ChallengeManagerInterface.
 */

namespace Drupal\trezor_connect\Challenge;

interface ChallengeManagerInterface {

  /**
   * Returns a challenge.
   *
   * If a challenge is not stored on the session, a new challenge will be
   * generated.
   *
   * @return ChallengeInterface|false
   *   The challenge object or FALSE.
   */
  public function get();

  /**
   * Returns a challenge hash.
   *
   * @return mixed
   */
  public function hash();

  /**
   * Deletes a challenge.
   *
   * @return mixed
   */
  public function delete();

}

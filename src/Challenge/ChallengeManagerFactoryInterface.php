<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Challenge\ChallengeManagerFactoryInterface.
 */

namespace Drupal\trezor_connect\Challenge;

/**
 * Provides the ChallengeManagerFactoryInterface interface.
 */
interface ChallengeManagerFactoryInterface {

  /**
   * Gets a challenge backend class.
   *
   * @return \Drupal\trezor_connect\ChallengeBackendInterface
   *   The challenge backend object.
   */
  public function get();

}

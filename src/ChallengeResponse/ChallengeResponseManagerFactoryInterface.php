<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerFactoryInterface.
 */

namespace Drupal\trezor_connect\ChallengeResponse;

/**
 * Provides the ChallengeResponseManagerFactoryInterface interface.
 */
interface ChallengeResponseManagerFactoryInterface {

  /**
   * Gets a challenge response backend class.
   *
   * @return \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface
   *   The challenge response backend object.
   */
  public function get();

}

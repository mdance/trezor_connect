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

  /**
   * Returns a challenge response from the request POST.
   *
   * @return ChallengeResponse|false
   *   The challenge response object or FALSE.
   */
  public function getPost();

  /**
   * Returns a challenge response from the session.
   *
   * @return ChallengeResponse|false
   *   The challenge response object or FALSE.
   */
  public function getSession();

  /**
   * Saves a challenge response.
   *
   * @param ChallengeResponseInterface $challenge_response
   *   If specified, the passed in challenge response will be saved to the
   * session, otherwise the get method will be invoked to retrieve the
   * challenge response.
   */
  public function set(ChallengeResponseInterface $challenge_response = NULL);

  /**
   * Deletes a challenge response.
   */
  public function delete();

}

<?php
/**
 * Contains \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface.
 */

namespace Drupal\trezor_connect\ChallengeResponse;

interface ChallengeResponseBackendInterface {

  /**
   * Returns a challenge response associated with an id.
   *
   * @param string $id
   *   The challenge response id to retrieve.
   *
   * @return ChallengeResponse|false
   *   The ChallengeResponse object or FALSE.
   *
   * @see \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface::getMultiple()
   */
  public function get($id);

  /**
   * Returns the challenge responses associated with an array of ids.
   *
   * @param array $ids
   *   An array of challenge response ids.
   *
   * @return array
   *   An array of ChallengeResponse objects.
   *
   * @see \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface::get()
   */
  public function getMultiple(array $ids);

  /**
   * Returns challenge response associated with the public keys.
   *
   * @param array $public_keys
   *
   * @return mixed
   */
  public function getMultiplePublicKey(array $public_keys);

  /**
   * Stores a challenge response.
   *
   * @param ChallengeResponse $challenge_response
   *   The ChallengeResponse object to store.
   */
  public function set(ChallengeResponseInterface $challenge_response);

  /**
   * Deletes a challenge response.
   *
   * @param integer $id
   *   The challenge response id to be deleted.
   *
   * @see \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface::deleteAll()
   */
  public function delete($id);

  /**
   * Deletes all challenge responses.
   *
   * @see \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface::deleteAll()
   */
  public function deleteAll();

}

<?php
/**
 * Contains \Drupal\trezor_connect\Challenge\ChallengeBackendInterface.
 */

namespace Drupal\trezor_connect\Challenge;

interface ChallengeBackendInterface {

  /**
   * Returns a challenge associated with an id.
   *
   * @param string $id
   *   The hidden challenge of the challenge to retrieve.
   *
   * @return Challenge|false
   *   The challenge object or FALSE.
   *
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::getMultiple()
   */
  public function get($id);

  /**
   * Returns the challenges associated with an array of ids.
   *
   * @param array $ids
   *   An array of challenge ids.
   *
   * @return array
   *   An array of Challenge objects.
   *
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::get()
   */
  public function getMultiple(array $ids);

  /**
   * Stores a challenge.
   *
   * @param Challenge $challenge
   *   The challenge object to store.
   */
  public function set(ChallengeInterface $challenge);

  /**
   * Store multiple challenges.
   *
   * @param array $challenges
   *   An array of Challenge objects.
   */
  public function setMultiple(array $challenges);

  /**
   * Deletes a challenge.
   *
   * @param integer $id
   *   The challenge id to be deleted.
   *
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::deleteAll()
   */
  public function delete($id);

  /**
   * Deletes all challenges.
   *
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::deleteAll()
   */
  public function deleteAll();

}

<?php
/**
 * Contains \Drupal\trezor_connect\Challenge\ChallengeBackendInterface.
 */

namespace Drupal\trezor_connect\Challenge;

interface ChallengeBackendInterface {

  /**
   * Returns a challenge associated with an id.
   *
   * @param int|array|NULL $id
   *   The challenge id to retrieve.  If null, the current request will be
   * checked for a challenge, otherwise a new challenge will be generated and
   * returned.
   *
   * @param array $conditions
   *   An array of conditions.  The array should contain the following keys:
   *
   *     field - A string containing the name of the field.
   *     value - A string containing the value for the condition.
   *     operator - A string containing the condition operator.
   *
   * @return array
   *   An array of Challenge objects.
   *
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::getMultiple()
   */
  public function get($id, array $conditions = NULL);

  /**
   * Returns the challenges associated with an array of ids.
   *
   * @param int|array|NULL $id
   *   The challenge id to retrieve.  If null, the current request will be
   * checked for a challenge, otherwise a new challenge will be generated and
   * returned.
   *
   * @param array $conditions
   *   An array of conditions.  The array should contain the following keys:
   *
   *     field - A string containing the name of the field.
   *     value - A string containing the value for the condition.
   *     operator - A string containing the condition operator.
   *
   * @return array
   *   An array of Challenge objects.
   *
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::get()
   */
  public function getMultiple(array $ids, array $conditions = NULL);

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

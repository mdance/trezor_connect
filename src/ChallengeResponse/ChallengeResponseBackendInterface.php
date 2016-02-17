<?php
/**
 * Contains \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface.
 */

namespace Drupal\trezor_connect\ChallengeResponse;

interface ChallengeResponseBackendInterface {

  /**
   * Returns the mapping associated with an id.
   *
   * @param int|array|NULL $id
   *   The mapping id to retrieve.
   *
   * @param array $conditions
   *   An array of conditions.  The array should contain the following keys:
   *
   *     field - A string containing the name of the field.
   *     value - A string containing the value for the condition.
   *     operator - A string containing the condition operator.
   *
   * @return array
   *   An array of Mapping objects.
   *
   * @see \Drupal\trezor_connect\Mapping\MappingBackendInterface::getMultiple()
   */
  public function get($id, array $conditions = NULL);

  /**
   * Returns the mappings associated with an array of ids.
   *
   * @param array $ids
   *   The mapping ids to retrieve.
   *
   * @param array $conditions
   *   An array of conditions.  The array should contain the following keys:
   *
   *     field - A string containing the name of the field.
   *     value - A string containing the value for the condition.
   *     operator - A string containing the condition operator.
   *
   * @return array
   *   An array of Mapping objects.
   *
   * @see \Drupal\trezor_connect\Mapping\MappingBackendInterface::get()
   */
  public function getMultiple(array $ids, array $conditions = NULL);

  /**
   * Returns challenge response associated with the public keys.
   *
   * @param array $public_keys
   *
   * @return mixed
   */
  public function getMultipleFromPublicKey(array $public_keys);

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

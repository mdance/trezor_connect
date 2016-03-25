<?php
/**
 * @file
 * Contains \Drupal\trezor_connect\Mapping\MappingManagerInterface.
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface;
use Drupal\trezor_connect\Mapping\MappingBackendInterface;

interface MappingManagerInterface {

  /**
   * Sets the backend.
   *
   * @param \Drupal\trezor_connect\Mapping\MappingBackendInterface $backend
   *
   * @return mixed
   */
  public function setBackend(MappingBackendInterface $backend);

  /**
   * Returns the challenge backend.
   *
   * @return \Drupal\trezor_connect\Mapping\MappingBackendInterface
   */
  public function getBackend();

  /**
   * Sets the challenge manager.
   *
   * @param \Drupal\trezor_connect\Challenge\ChallengeManagerInterface $challenge_manager
   *
   * @return mixed
   */
  public function setChallengeManager(ChallengeManagerInterface $challenge_manager);

  /**
   * Gets the challenge manager.
   *
   * @return \Drupal\trezor_connect\Challenge\ChallengeManagerInterface $challenge_manager
   */
  public function getChallengeManager();

  /**
   * Sets the challenge response manager.
   *
   * @param \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface $challenge_response_manager
   *
   * @return mixed
   */
  public function setChallengeResponseManager(ChallengeResponseManagerInterface $challenge_response_manager);

  /**
   * Gets the challenge response manager.
   *
   * @return \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface $challenge_response_manager
   */
  public function getChallengeResponseManager();

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
   * @see \Drupal\trezor_connect\Mapping\MappingManagerInterface::getMultiple()
   */
  public function get($id = NULL, array $conditions = NULL);

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
   * @see \Drupal\trezor_connect\Mapping\MappingManagerInterface::get()
   */
  public function getMultiple(array $ids, array $conditions = NULL);

  /**
   * Returns a mapping associated with a public key.
   *
   * @param string $public_key
   *   The public key of the mapping to retrieve.
   *
   * @return Mapping|false
   *   The mapping object or FALSE.
   *
   * @see \Drupal\trezor_connect\MappingBackendInterface::getMultiple()
   */
  public function getFromPublicKey($public_key);

  /**
   * Returns the mappings associated with an array of public keys.
   *
   * @param array $public_keys
   *   The public keys to retrieve.
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
   * @see \Drupal\trezor_connect\Mapping\MappingManagerInterface::getFromPublicKey()
   */
  public function getMultipleFromPublicKeys(array $public_keys);

  /**
   * Returns a mapping associated with an account uid.
   *
   * @param $uid
   *
   * @return mixed
   */
  public function getFromUid($uid);

  /**
   * Stores a mapping.
   *
   * @param MappingInterface $mapping
   *   The mapping object to store.
   */
  public function set(MappingInterface $mapping);

  /**
   * Deletes a mapping.
   *
   * @param integer $uid
   *   The account id whose mappings should be deleted.
   *
   * @see \Drupal\trezor_connect\MappingBackendInterface::deleteMultiple()
   * @see \Drupal\trezor_connect\MappingBackendInterface::deleteAll()
   */
  public function delete($uid);

  /**
   * Maps a challenge response to an account.
   *
   * @param $uid
   *   Provides the account id.
   *
   * @param \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface $challenge_response
   *   Provides the challenge response to map to the account.
   *
   * @return mixed
   */
  public function mapChallengeResponse($uid, ChallengeResponseInterface $challenge_response);

  /**
   * Disables a mapping associated with an account.
   *
   * @param $uid
   *
   * @return mixed
   */
  public function disable($uid);

  /**
   * Enables a mapping associated with an account.
   *
   * @param $uid
   *
   * @return mixed
   */
  public function enable($uid);

}

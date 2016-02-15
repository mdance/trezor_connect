<?php
/**
 * @file
 * Contains \Drupal\trezor_connect\Mapping\MappingManagerInterface.
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
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
  public function get($public_key);

  /**
   * Returns a mapping associated with an account uid.
   *
   * @param $uid
   *
   * @return mixed
   */
  public function getFromUid($uid);

  /**
   * Returns the mappings associated with an array of public keys.
   *
   * @param array $public_keys
   *   An array of mapping public keys.
   *
   * @return array
   *   An array of Mapping objects.
   *
   * @see \Drupal\trezor_connect\MappingBackendInterface::get()
   */
  public function getMultiple(array $public_keys);

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
   *
   * @return mixed
   */
  public function mapChallengeResponse($uid);

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

<?php
/**
 * Contains \Drupal\trezor_connect\MappingBackendInterface.
 */

namespace Drupal\trezor_connect\Mapping;

interface MappingBackendInterface {

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
   * Returns a mapping associated with a user id.
   *
   * @param integer $uid
   *   The user id of the mapping to retrieve.
   *
   * @return Mapping|false
   *   The mapping object or FALSE.
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
   * @param string $public_key
   *   The mapping public key.
   *
   * @param MappingInterface $mapping
   *   The mapping object to store.
   */
  public function set(MappingInterface $mapping);

  /**
   * Store multiple mappings.
   *
   * @param array $mappings
   *   An array of Mapping objects.
   */
  public function setMultiple(array $mappings);

  /**
   * Deletes a mapping.
   *
   * @param integer $uid
   *   The account id whose mapping should be deleted.
   *
   * @see \Drupal\trezor_connect\MappingBackendInterface::deleteAll()
   */
  public function delete($uid);

  /**
   * Deletes all mappings.
   *
   * @see \Drupal\trezor_connect\MappingBackendInterface::deleteAll()
   */
  public function deleteAll();

}

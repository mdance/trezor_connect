<?php
/**
 * Contains \Drupal\trezor_connect\MappingBackendInterface.
 */

namespace Drupal\trezor_connect\Mapping;

interface MappingBackendInterface {

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
   * @return Mapping
   *   A Mapping object.
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
   * Returns a mapping associated with a public key.
   *
   * @param string $public_key
   *   The public key of the mapping to retrieve.
   *
   * @return Mapping|false
   *   The mapping object or FALSE.
   */
  public function getFromPublicKey($public_key);

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
  public function getMultipleFromPublicKeys(array $public_keys);

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

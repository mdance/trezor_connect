<?php
/**
 * Contains \Drupal\trezor_connect\MappingBackendDatabase.
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\Core\Database\Connection;

class MappingBackendDatabase implements MappingBackendInterface {
  const TABLE = 'trezor_connect_mappings';

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Construct the MappingBackendDatabase.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * @inheritDoc
   */
  public function get($public_key) {
    if (!is_array($public_key)) {
      $public_key = array($public_key);
    }

    $output = $this->getMultiple($public_key);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getFromUid(integer $uid) {
    $output = array();

    $query = $this->connection->select(self::TABLE, 'm');

    $query->fields('m');
    $query->condition('uid', $uid);

    $results = $query->execute()->fetchAssoc();

    foreach ($results as $key => $value) {
      // TODO: Test this works
      $output[$key] = $value::fromArray($value);
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultiple(array $public_keys) {
    $output = array();

    $query = $this->connection->select(self::TABLE, 'm');

    $query->fields('m');
    $query->condition('public_key', $public_keys, 'IN');

    $results = $query->execute()->fetchAssoc();

    foreach ($results as $key => $value) {
      // TODO: Test this works
      $output[$key] = $value::fromArray($value);
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function set($public_key, Mapping $mapping) {
    $fields = $mapping::toArray($mapping);

    $this->connection->insert(self::TABLE)
      ->fields($fields)
      ->execute();

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setMultiple(array $mappings) {
    foreach ($mappings as $key => $mapping) {
      $this->set($key, $mapping);
    }

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function delete($uid) {
    $this->connection->delete(self::TABLE)
      ->condition('uid', $uid)
      ->execute();

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function deleteAll() {
    $this->connection->delete(self::TABLE)
      ->execute();

    return $this;
  }
}

<?php
/**
 * Contains \Drupal\trezor_connect\MappingBackendDatabase.
 *
 * TODO: Use doctrine
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\Core\Database\Connection;
use Drupal\trezor_connect\Challenge\Challenge;
use Drupal\trezor_connect\Challenge\ChallengeResponse;

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

    $results = $query->execute();

    foreach ($results as $key => $value) {
      $challenge = new Challenge(FALSE);

      $challenge->setCreated($value->challenge_created);
      $challenge->setChallengeHidden($value->challenge_hidden);
      $challenge->setChallengeVisual($value->challenge_visual);

      $challenge_response = new ChallengeResponse();

      $challenge_response->setSuccess($value->success);
      $challenge_response->setPublicKey($value->public_key);
      $challenge_response->setSignature($value->signature);
      $challenge_response->setVersion($value->version);

      $mapping = new Mapping();

      $mapping->setId($value->id);
      $mapping->setCreated($value->created);
      $mapping->setUid($value->uid);
      $mapping->setChallenge($challenge);
      $mapping->setChallengeResponse($challenge_response);

      $output[$key] = $mapping;
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function set($public_key, Mapping $mapping) {
    $result = $mapping->toArray();

    $challenge = $result['challenge'];
    $challenge_response = $result['challenge_response'];

    $fields = array();

    $fields['created'] = time();
    $fields['uid'] = $result['uid'];
    $fields['challenge_created'] = $challenge['created'];
    $fields['challenge_hidden'] = $challenge['challenge_hidden'];
    $fields['challenge_visual'] = $challenge['challenge_visual'];
    $fields['success'] = (bool)$challenge_response['success'];
    $fields['public_key'] = $challenge_response['public_key'];
    $fields['signature'] = $challenge_response['signature'];
    $fields['version'] = $challenge_response['version'];

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

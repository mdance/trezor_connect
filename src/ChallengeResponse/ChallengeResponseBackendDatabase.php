<?php
/**
 * Contains \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendDatabase.
 */

namespace Drupal\trezor_connect\ChallengeResponse;

use Drupal\Core\Database\Connection;
use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;

class ChallengeResponseBackendDatabase implements ChallengeResponseBackendInterface {

  /**
   * Provides the database table used to store the challenge responses.
   */
  const TABLE = 'trezor_connect_challenge_responses';

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Provides the challenge manager service.
   *
   * @var \Drupal\trezor_connect\Challenge\ChallengeManagerInterface
   */
  protected $challenge_manager;

  /**
   * Construct the ChallengeResponseBackendDatabase.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection, ChallengeManagerInterface $challenge_manager) {
    $this->connection = $connection;
    $this->challenge_manager = $challenge_manager;
  }

  /**
   * @inheritDoc
   */
  public function get($id) {
    if (!is_array($id)) {
      $id = array($id);
    }

    $output = $this->getMultiple($id);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultiple(array $ids) {
    $output = array();

    $query = $this->connection->select(self::TABLE, 'm');

    $query->fields('m');
    $query->condition('id', $ids, 'IN');

    $results = $query->execute();

    foreach ($results as $key => $value) {
      $challenge = $this->challenge_manager->get($value->challenge_id);

      $challenge_response = new ChallengeResponse();

      $challenge_response->setId($value->id);
      $challenge_response->setCreated($value->created);
      $challenge_response->setChallenge($challenge);
      $challenge_response->setPublicKey($value->public_key);
      $challenge_response->setSignature($value->signature);
      $challenge_response->setVersion($value->version);

      $output[$key] = $challenge_response;
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultiplePublicKey(array $public_keys) {
    $output = array();

    $query = $this->connection->select(self::TABLE, 'm');

    $query->fields('m');
    $query->condition('public_key', $public_keys, 'IN');

    $results = $query->execute();

    foreach ($results as $key => $value) {
      $challenge = $this->challenge_manager->get($value->challenge_id);
      $challenge = array_shift($challenge);

      if ($challenge) {
        $challenge_response = new ChallengeResponse();

        $challenge_response->setId($value->id);
        $challenge_response->setCreated($value->created);
        $challenge_response->setChallenge($challenge);
        $challenge_response->setPublicKey($value->public_key);
        $challenge_response->setSignature($value->signature);
        $challenge_response->setVersion($value->version);

        $output[$key] = $challenge_response;
      }
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function set(ChallengeResponseInterface $challenge_response) {
    $map = $challenge_response->toArray();

    $fields = array();

    $fields['created'] = $map['created'];
    $fields['challenge_id'] = $map['challenge']['id'];
    $fields['public_key'] = $map['public_key'];
    $fields['signature'] = $map['signature'];
    $fields['version'] = $map['version'];

    if (isset($map['id']) && !is_null($map['id'])) {
      $this->connection->merge(self::TABLE)
        ->key('id', $map['id'])
        ->fields($fields)
        ->execute();
    }
    else {
      $id = $this->connection->insert(self::TABLE)
        ->fields($fields)
        ->execute();

      $challenge_response->setId($id);
    }

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function delete($id) {
    $this->connection->delete(self::TABLE)
      ->condition('id', $id)
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

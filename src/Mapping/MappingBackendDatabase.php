<?php
/**
 * Contains \Drupal\trezor_connect\MappingBackendDatabase.
 *
 * TODO: Use doctrine
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\Core\Database\Connection;
use Drupal\trezor_connect\Challenge\ChallengeResponse;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface;

class MappingBackendDatabase implements MappingBackendInterface {

  const TABLE = 'trezor_connect_mappings';

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * @inheritdoc
   *
   * @var \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface
   */
  protected $challenge_response_manager;

  /**
   * Construct the MappingBackendDatabase.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection, ChallengeResponseManagerInterface $challenge_response_manager) {
    $this->connection = $connection;
    $this->challenge_response_manager = $challenge_response_manager;
  }

  /**
   * @inheritDoc
   */
  public function get($id, array $conditions = NULL) {
    if (is_null($id)) {
      $id = array();
    }
    else if (!is_array($id)) {
      $id = array($id);
    }

    $output = $this->getMultiple($id, $conditions);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultiple(array $ids, array $conditions = NULL) {
    $query = $this->connection->select(self::TABLE, 'm');

    $query->fields('m');

    $total = count($ids);

    if ($total) {
      $query->condition('id', $ids, 'IN');
    }

    if (!is_null($conditions)) {
      $defaults = array(
        'field' => NULL,
        'value' => NULL,
        'operator' => '=',
      );

      foreach ($conditions as $key => $condition) {
        $condition = array_merge($defaults, $condition);

        $query->condition($condition['field'], $condition['value'], $condition['operator']);
      }
    }

    $results = $query->execute();

    $output = $this->results($results);

    return $output;
  }
    if (!is_array($public_key)) {
      $public_key = array($public_key);
    }

    $output = $this->getMultiple($public_key);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getFromUid($uid) {
    $query = $this->connection->select(self::TABLE, 'm');

    $query->fields('m');
    $query->condition('uid', $uid);

    $results = $query->execute();

    $output = $this->results($results);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultiple(array $public_keys) {
    $output = array();

    $challenge_responses = $this->challenge_response_manager->getMultipleFromPublicKey($public_keys);

    $ids = array();

    foreach ($challenge_responses as $challenge_response) {
      $ids[] = $challenge_response->getId();
    }

    $total = count($ids);

    if ($total) {
      $query = $this->connection->select(self::TABLE, 'm');

      $query->fields('m');

      $query->condition('id', $ids, 'IN');

      $results = $query->execute();

      $output = $this->results($results, $challenge_responses);
    }

    return $output;
  }

  private function results($results, array $challenge_responses = NULL) {
    $output = array();

    foreach ($results as $key => $value) {
      $found = FALSE;

      $challenge_response = NULL;

      if (!is_null($challenge_responses)) {
        foreach ($challenge_responses as $challenge_response) {
          $id = $challenge_response->getId();

          if ($id == $value->challenge_response_id) {
            $found = TRUE;

            break;
          }
        }
      }

      if (!$found) {
        $challenge_response = $this->challenge_response_manager->get($value->challenge_response_id);
        $challenge_response = array_shift($challenge_response);
      }

      if ($challenge_response) {
        $challenge = $challenge_response->getChallenge();

        $mapping = new Mapping();

        $mapping->setId($value->id);
        $mapping->setCreated($value->created);
        $mapping->setUid($value->uid);
        $mapping->setChallenge($challenge);
        $mapping->setChallengeResponse($challenge_response);
        $mapping->setStatus($value->status);

        $output[$key] = $mapping;
      }
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function set(MappingInterface $mapping) {
    $map = $mapping->toArray();

    $challenge_response = $map['challenge_response'];

    $fields = array();

    $fields['created'] = $map['created'];
    $fields['uid'] = $map['uid'];
    $fields['challenge_response_id'] = $challenge_response['id'];
    $fields['status'] = $map['status'];

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

      $mapping->setId($id);
    }

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setMultiple(array $mappings) {
    foreach ($mappings as $key => $mapping) {
      $this->set($mapping);
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

  /**
   * @inheritDoc
   */
  public function disable($uid) {
    $this->status($uid, MappingInterface::STATUS_DISABLED);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function enable($uid) {
    $this->status($uid, MappingInterface::STATUS_ACTIVE);

    return $this;
  }

  /**
   * Sets the mapping status.
   *
   * @param $uid
   * @param int $status
   *
   * @return $this
   * @throws \Exception
   */
  private function status($uid, $status = MappingInterface::STATUS_ACTIVE) {
    $fields = array(
      'status' => $status,
    );

    $this->connection->merge(self::TABLE)
      ->key('uid', $uid)
      ->fields($fields)
      ->execute();

    return $this;
  }

}

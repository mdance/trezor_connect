<?php
/**
 * Contains \Drupal\trezor_connect\Challenge\ChallengeBackendDatabase.
 *
 * TODO: Implement additional backends
 */

namespace Drupal\trezor_connect\Challenge;

use Drupal\Core\Database\Connection;

class ChallengeBackendDatabase implements ChallengeBackendInterface {

  const TABLE = 'trezor_connect_challenges';

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Construct the ChallengeBackendDatabase.
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
  public function get($id, array $conditions = NULL) {
    if (is_null($id)) {
      $id = array();
    }
    else if (!is_array($id)) {
      $id = array($id);
    }

    $output = $this->getMultiple($id, $conditions);
    $output = array_shift($output);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultiple(array $ids, array $conditions = NULL) {
    $output = array();

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

    foreach ($results as $key => $value) {
      $challenge = new Challenge();

      $challenge->setId($value->id);
      $challenge->setCreated($value->created);
      $challenge->setChallengeHidden($value->challenge_hidden);
      $challenge->setChallengeVisual($value->challenge_visual);

      $output[$key] = $challenge;
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function set(ChallengeInterface $challenge) {
    $map = $challenge->toArray();

    $fields = array();

    $fields['created'] = $map['created'];
    $fields['challenge_hidden'] = $map['challenge_hidden'];
    $fields['challenge_visual'] = $map['challenge_visual'];

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

      $challenge->setId($id);
    }

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setMultiple(array $challenges) {
    foreach ($challenges as $key => $challenge) {
      $this->set($challenge);
    }

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function delete($id) {
    if (!is_array($id)) {
      $id = array($id);
    }

    $this->deleteMultiple($id);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function deleteMultiple(array $ids) {
    $this->connection->delete(self::TABLE)
      ->condition('id', $ids, 'IN')
      ->execute();
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

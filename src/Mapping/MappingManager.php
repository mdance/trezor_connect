<?php
/**
 * Contains \Drupal\trezor_connect\MappingManager
 *
 * TODO: Convert this to an exportable configuration entity
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface;

class MappingManager implements MappingManagerInterface {
  /**
   * @inheritdoc
   */
  protected $backend;

  protected $challenge_manager;
  protected $challenge_response_manager;

  public function __construct() {
  }

  /**
   * @inheritDoc
   */
  public function setBackend(MappingBackendInterface $backend) {
    $this->backend = $backend;
  }

  /**
   * @inheritDoc
   */
  public function getBackend() {
    return $this->backend;
  }

  /**
   * @return mixed
   */
  public function getChallengeManager() {
    return $this->challenge_manager;
  }

  /**
   * @param mixed $challenge_manager
   */
  public function setChallengeManager(ChallengeManagerInterface $challenge_manager) {
    $this->challenge_manager = $challenge_manager;
  }

  /**
   * @return mixed
   */
  public function getChallengeResponseManager() {
    return $this->challenge_response_manager;
  }

  /**
   * @param mixed $challenge_response_manager
   */
  public function setChallengeResponseManager(ChallengeResponseManagerInterface $challenge_response_manager) {
    $this->challenge_response_manager = $challenge_response_manager;
  }

  /**
   * @inheritDoc
   */
  public function get($id = NULL, array $conditions = NULL) {
    $output = $this->backend->get($id, $conditions);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultiple(array $ids, array $conditions = NULL) {
    $output = $this->backend->getMultiple($ids, $conditions);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getFromPublicKey($public_key) {
    $output = $this->backend->getFromPublicKey($public_key);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultipleFromPublicKeys(array $public_keys) {
    $output = $this->backend->getMultipleFromPublicKeys($public_keys);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getFromUid($uid) {
    $output = $this->backend->getFromUid($uid);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function set(MappingInterface $mapping) {
    $created = $mapping->getCreated();

    if (!$created) {
      $created = time();

      $mapping->setCreated($created);
    }

    $this->backend->set($mapping);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function delete($uid) {
    $this->backend->delete($uid);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function deleteAll() {
    $this->backend->deleteAll();

    return $this;
  }

  public function mapChallengeResponse($uid) {
    $challenge_response = $this->challenge_response_manager->getSessionChallengeResponse();

    if ($challenge_response) {
      // TODO: Check for existing mappings
      $mapping = new Mapping();

      $mapping->setUid($uid);
      $mapping->setChallengeResponse($challenge_response);

      $challenge = $challenge_response->getChallenge();

      $mapping->setChallenge($challenge);

      $mapping->setStatus(MappingInterface::STATUS_ACTIVE);

      $this->set($mapping);

      $id = $mapping->getId();

      if ($id) {
        $this->challenge_response_manager->deleteSessionChallengeResponse();
      }
    }
  }

  /**
   * @inheritDoc
   */
  public function disable($uid) {
    $this->backend->disable($uid);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function enable($uid) {
    $this->backend->enable($uid);

    return $this;
  }

}

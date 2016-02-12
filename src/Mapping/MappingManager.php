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
  public function get($public_key) {
    $output = $this->backend->get($public_key);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getFromUid(integer $uid) {
    $output = $this->backend->getFromUid($uid);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultiple(array $public_keys) {
    $output = $this->backend->getMultiple($public_keys);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function set(Mapping $mapping) {
    $this->backend->set($mapping);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setMultiple(array $mappings) {
    $this->backend->setMultiple($mappings);

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
      $mapping = new Mapping();

      $mapping->setUid($uid);
      $mapping->setChallengeResponse($challenge_response);

      $challenge = $challenge_response->getChallenge();

      $mapping->setChallenge($challenge);

      $this->backend->set($mapping);

      $id = $mapping->getId();

      if ($id) {
        $this->challenge_manager->forget();
        $this->challenge_response_manager->forget();
      }
    }
  }
}

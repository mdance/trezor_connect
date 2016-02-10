<?php
/**
 * Contains \Drupal\trezor_connect\MappingManager
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
use Drupal\trezor_connect\Challenge\ChallengeResponseManagerInterface;
use Drupal\trezor_connect\Mapping\Mapping;

class MappingManager implements MappingManagerInterface {
  /**
   * @inheritdoc
   */
  protected $backend;

  protected $challenge_manager;
  protected $challenge_response_manager;
  protected $cache_tags_invalidator;

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
   * @return mixed
   */
  public function getCacheTagsInvalidator() {
    return $this->cache_tags_invalidator;
  }

  /**
   * @param mixed $cache_tags_invalidator
   */
  public function setCacheTagsInvalidator($cache_tags_invalidator) {
    $this->cache_tags_invalidator = $cache_tags_invalidator;
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
    $challenge = $this->challenge_manager->get();
    $challenge_response = $this->challenge_response_manager->getSession();

    if ($challenge && $challenge_response) {
      $public_key = $challenge_response->getPublicKey();

      $mapping = new Mapping();

      $mapping->setUid($uid);
      $mapping->setChallenge($challenge);
      $mapping->setChallengeResponse($challenge_response);

      $this->backend->set($public_key, $mapping);

      // TODO: Test cache tags invalidation
      $tags = array();

      $hash = $challenge->getHash();
      $tags[] = 'trezor_connect_challenge:' . $hash;

      $this->cache_tags_invalidator->invalidateTags($tags);

      $this->challenge_manager->delete();
      $this->challenge_response_manager->delete();
    }
  }
}

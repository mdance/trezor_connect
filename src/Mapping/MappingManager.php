<?php
/**
 * Contains \Drupal\trezor_connect\MappingManager
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\trezor_connect\Mapping\Mapping;

class MappingManager implements MappingManagerInterface {
  /**
   * @inheritdoc
   */
  protected $backend;

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

}

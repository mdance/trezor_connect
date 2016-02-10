<?php
/**
 * Contains \Drupal\trezor_connect\ChallengeResponse.
 */

namespace Drupal\trezor_connect\Challenge;

class ChallengeResponse implements ChallengeResponseInterface {

  protected $success;
  protected $public_key;
  protected $signature;
  protected $version;

  /**
   * @return mixed
   */
  public function getSuccess() {
    return $this->success;
  }

  /**
   * @param mixed $success
   */
  public function setSuccess($success) {
    $this->success = $success;
  }

  /**
   * @return mixed
   */
  public function getVersion() {
    return $this->version;
  }

  /**
   * @param mixed $version
   */
  public function setVersion($version) {
    $this->version = $version;
  }

  /**
   * @return mixed
   */
  public function getSignature() {
    return $this->signature;
  }

  /**
   * @param mixed $signature
   */
  public function setSignature($signature) {
    $this->signature = $signature;
  }

  /**
   * @return mixed
   */
  public function getPublicKey() {
    return $this->public_key;
  }

  /**
   * @param mixed $public_key
   */
  public function setPublicKey($public_key) {
    $this->public_key = $public_key;
  }

  /**
   * @inheritdoc
   */
  public function toArray() {
    $output = array(
      'success' => $this->getSuccess(),
      'public_key' => $this->getPublicKey(),
      'signature' => $this->getSignature(),
      'version' => $this->getVersion(),
    );

    return $output;
  }

}

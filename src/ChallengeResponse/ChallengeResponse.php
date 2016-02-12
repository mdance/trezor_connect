<?php
/**
 * Contains \Drupal\trezor_connect\ChallengeResponse\ChallengeResponse.
 */

namespace Drupal\trezor_connect\ChallengeResponse;

use Drupal\trezor_connect\Challenge\ChallengeInterface;

class ChallengeResponse implements ChallengeResponseInterface {

  protected $id;
  protected $created;
  protected $challenge;
  protected $public_key;
  protected $signature;
  protected $version;

  function __construct() {
  }

  /**
   * @inheritDoc
   */
  function __toString() {
    $output = $this->toArray();
    $output = serialize($output);

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function toArray() {
    $challenge = $this->getChallenge();

    if ($challenge) {
      $challenge = $challenge->toArray();
    }
    else {
      $challenge = array();
    }

    $output = array(
      'id' => $this->getId(),
      'created' => $this->getCreated(),
      'challenge' => $challenge,
      'public_key' => $this->getPublicKey(),
      'signature' => $this->getSignature(),
      'version' => $this->getVersion(),
    );

    return $output;
  }

  /**
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param int $id
   */
  public function setId($id) {
    $this->id = $id;
  }

  /**
   * @return mixed
   */
  public function getCreated() {
    return $this->created;
  }

  /**
   * @param mixed $created
   */
  public function setCreated($created) {
    $this->created = $created;
  }

  /**
   * @inheritDoc
   */
  public function getChallenge() {
    return $this->challenge;
  }

  /**
   * @inheritDoc
   */
  public function setChallenge(ChallengeInterface $challenge) {
    $this->challenge = $challenge;
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

}

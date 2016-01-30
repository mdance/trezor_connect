<?php
/**
 * Contains \Drupal\trezor_connect\Mapping
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\trezor_connect\Challenge\ChallengeResponseInterface;

class Mapping implements MappingInterface {

  protected $id;
  protected $created;
  protected $uid;
  protected $challenge_hidden;
  protected $challenge_visual;
  protected $address;
  protected $public_key;
  protected $signature;

  /**
   * @return mixed
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param mixed $id
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
   * @return mixed
   */
  public function getUid() {
    return $this->uid;
  }

  /**
   * @param mixed $uid
   */
  public function setUid($uid) {
    $this->uid = $uid;
  }

  /**
   * @return mixed
   */
  public function getChallengeHidden() {
    return $this->challenge_hidden;
  }

  /**
   * @param mixed $challenge_hidden
   */
  public function setChallengeHidden($challenge_hidden) {
    $this->challenge_hidden = $challenge_hidden;
  }

  /**
   * @return mixed
   */
  public function getChallengeVisual() {
    return $this->challenge_visual;
  }

  /**
   * @param mixed $challenge_visual
   */
  public function setChallengeVisual($challenge_visual) {
    $this->challenge_visual = $challenge_visual;
  }

  /**
   * @return mixed
   */
  public function getAddress() {
    return $this->address;
  }

  /**
   * @param mixed $address
   */
  public function setAddress($address) {
    $this->address = $address;
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
   * @inheritdoc
   */
  public static function keys() {
    $output = array(
      'id',
      'created',
      'uid',
      'challenge_hidden',
      'challenge_visual',
      'address',
      'public_key',
      'signature',
    );

    return $output;
  }

  /**
   * @inheritdoc
   */
  public static function toArray(MappingInterface $mapping) {
    $output = (array)$mapping;

    return $output;
  }

  /**
   * @inheritdoc
   */
  public static function fromArray(array $mapping) {
    $output = new Mapping();

    $keys = static::keys();

    foreach ($keys as $key) {
      if (!isset($mapping[$key])) {
        $message = sprintf('The array must contain the key %s', $key);

        throw new \Exception($message);
      }
      else {
        $output->$key = $mapping[$key];
      }
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public static function fromChallengeResponse(ChallengeResponseInterface $response) {
    // TODO: Test this
    $output = static::fromArray((array)$response);

    return $output;
  }

}

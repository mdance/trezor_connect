<?php
/**
 * Contains \Drupal\trezor_connect\ChallengeResponse.
 */

namespace Drupal\trezor_connect\Challenge;

class ChallengeResponse implements ChallengeResponseInterface {

  protected $challenge_hidden;
  protected $challenge_visual;
  protected $signature;
  protected $public_key;
  protected $address;

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
  public function getAddress() {
    return $this->address;
  }

  /**
   * @param mixed $address
   */
  public function setAddress($address) {
    $this->address = $address;
  }

}

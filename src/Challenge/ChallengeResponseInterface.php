<?php
/**
 * Contains \Drupal\trezor_connect\ChallengeResponseInterface.
 */

namespace Drupal\trezor_connect\Challenge;

interface ChallengeResponseInterface {

  /**
   * @return mixed
   */
  public function getChallengeHidden();

  /**
   * @param mixed $challenge_hidden
   */
  public function setChallengeHidden($challenge_hidden);

  /**
   * @return mixed
   */
  public function getChallengeVisual();

  /**
   * @param mixed $challenge_visual
   */
  public function setChallengeVisual($challenge_visual);

  /**
   * @return mixed
   */
  public function getSignature();

  /**
   * @param mixed $signature
   */
  public function setSignature($signature);

  /**
   * @return mixed
   */
  public function getPublicKey();

  /**
   * @param mixed $public_key
   */
  public function setPublicKey($public_key);

  /**
   * @return mixed
   */
  public function getAddress();

  /**
   * @param mixed $address
   */
  public function setAddress($address);

}

<?php
/**
 * Contains \Drupal\trezor_connect\ChallengeResponseInterface.
 */

namespace Drupal\trezor_connect\Challenge;

interface ChallengeResponseInterface {

  /**
   * @return mixed
   */
  public function getSuccess();

  /**
   * @param mixed $success
   */
  public function setSuccess($success);

  /**
   * @return mixed
   */
  public function getVersion();

  /**
   * @param mixed $version
   */
  public function setVersion($version);

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

}

<?php
/**
 * Contains \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface.
 */

namespace Drupal\trezor_connect\ChallengeResponse;

use Drupal\trezor_connect\Challenge\ChallengeInterface;

interface ChallengeResponseInterface {

  /**
   * Returns a challenge response as an array.
   *
   * @return array
   */
  public function toArray();

  /**
   * @return mixed
   */
  public function getId();

  /**
   * @param mixed $id
   */
  public function setId($id);

  /**
   * @return mixed
   */
  public function getCreated();

  /**
   * @param mixed $created
   */
  public function setCreated($created);

  /**
   * @return mixed
   */
  public function getChallenge();

  /**
   * @param mixed $created
   */
  public function setChallenge(ChallengeInterface $challenge);

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
  public function getVersion();

  /**
   * @param mixed $version
   */
  public function setVersion($version);

}

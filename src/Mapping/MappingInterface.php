<?php
/**
 * Contains \Drupal\trezor_connect\MappingInterface.
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\trezor_connect\Challenge\ChallengeInterface;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface;

interface MappingInterface {

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
  public function getUid();

  /**
   * @param mixed $uid
   */
  public function setUid($uid);

  /**
   * @return mixed
   */
  public function getChallenge();

  /**
   * @param mixed $challenge
   */
  public function setChallenge(ChallengeInterface $challenge);

  /**
   * @return mixed
   */
  public function getChallengeResponse();

  /**
   * @param mixed $challenge_response
   */
  public function setChallengeResponse(ChallengeResponseInterface $challenge_response);

  /**
   * Returns a mapping in an array format.
   *
   * @return mixed
   */
  public function toArray();

}

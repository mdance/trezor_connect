<?php
/**
 * Contains \Drupal\trezor_connect\Mapping
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\trezor_connect\Challenge\ChallengeInterface;
use Drupal\trezor_connect\Challenge\ChallengeResponseInterface;

class Mapping implements MappingInterface {

  protected $id;
  protected $created;
  protected $uid;
  protected $challenge;
  protected $challenge_response;

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
  public function getChallenge() {
    return $this->challenge;
  }

  /**
   * @param mixed $challenge
   */
  public function setChallenge(ChallengeInterface $challenge) {
    $this->challenge = $challenge;
  }

  /**
   * @return mixed
   */
  public function getChallengeResponse() {
    return $this->challenge_response;
  }

  /**
   * @param mixed $challenge_response
   */
  public function setChallengeResponse(ChallengeResponseInterface $challenge_response) {
    $this->challenge_response = $challenge_response;
  }

  /**
   * @inheritdoc
   */
  public function toArray() {
    $uid = $this->getUid();

    $challenge = $this->getChallenge();
    $challenge = $challenge->toArray();

    $challenge_response = $this->getChallengeResponse();
    $challenge_response = $challenge_response->toArray();

    $output = array(
      'uid' => $uid,
      'challenge' => $challenge,
      'challenge_response' => $challenge_response,
    );

    return $output;
  }

}

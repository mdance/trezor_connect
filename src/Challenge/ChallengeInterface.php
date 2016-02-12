<?php
/**
 * Contains \Drupal\trezor_connect\Challenge\ChallengeInterface.
 */

namespace Drupal\trezor_connect\Challenge;

interface ChallengeInterface {

  /**
   * Returns a Challenge as an array.
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
   * Generates a new challenge.
   */
  public function generate();

  /**
   * Returns the challenge hash.
   *
   * @return mixed
   */
  public function hash();

}

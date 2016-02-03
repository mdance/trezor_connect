<?php
/**
 * Contains \Drupal\trezor_connect\ChallengeInterface.
 */

namespace Drupal\trezor_connect\Challenge;

interface ChallengeInterface {

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
   * Returns an array of property keys.
   *
   * @return array
   */
  public static function keys();

  /**
   * Returns a Challenge as an array.
   *
   * @param \Drupal\trezor_connect\ChallengeInterface $challenge
   *
   * @return array
   */
  public static function toArray(ChallengeInterface $challenge);

  /**
   * Returns a Challenge from an array.
   *
   * @param array $challenge
   *
   * @return \Drupal\trezor_connect\Challenge
   */
  public static function fromArray(array $challenge);

  /**
   * Generates a new challenge.
   */
  public function generate();
}

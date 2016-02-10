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
   * @return array
   */
  public function toArray();

  /**
   * Generates a new challenge.
   */
  public function generate();
}

<?php
/**
 * Contains \Drupal\trezor_connect\Challenge\Challenge
 */

namespace Drupal\trezor_connect\Challenge;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;

class Challenge implements ChallengeInterface, CacheableDependencyInterface {

  protected $id;
  protected $created;
  protected $challenge_hidden;
  protected $challenge_visual;

  function __construct() {
  }

  /**
   * @inheritDoc
   */
  function __toString() {
    $output = $this->toArray();
    $output = serialize($output);

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function toArray() {
    $output = array(
      'id' => $this->getId(),
      'created' => $this->getCreated(),
      'challenge_hidden' => $this->getChallengeHidden(),
      'challenge_visual' => $this->getChallengeVisual(),
    );

    return $output;
  }

  /**
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param int $id
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
   * @inheritDoc
   */
  public function generate() {
    $this->setId(null);

    $created = time();

    $this->setCreated($created);

    $challenge_hidden = $this->random(64);
    $challenge_hidden = implode('', $challenge_hidden);

    $this->setChallengeHidden($challenge_hidden);

    $challenge_visual = date('Y-m-d H:i:s', $created);

    $this->setChallengeVisual($challenge_visual);

    return $this;
  }

  /**
   * Responsible for returning random data.
   *
   * @param $length
   *
   * @return array
   */
  private function random($length) {
    $output = array();

    if ($length<2) {
      $length = 2;
    }

    $exists = function_exists('openssl_random_pseudo_bytes');

    if (!$exists) {
      $message = <<<EOF
A cryptographically secure random number can not be generated without the PHP
5.3.0 and the OpenSSL extensions openssl_random_pseudo_bytes function.
EOF;

      throw new \Exception($message);
    }
    else {
      $length = $length / 2;

      $crypto_strong = FALSE;

      while (!$crypto_strong) {
        $random = openssl_random_pseudo_bytes($length, $crypto_strong);
      }

      $random = bin2hex($random);

      $output = str_split($random);
    }

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function hash() {
    $output = (string)$this;
    $output = hash('sha256', $output);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getCacheContexts() {
    $output = array();

    //$output[] = 'trezor_connect_challenge';

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getCacheTags() {
    $output = array();

    $output[] = 'trezor_connect_challenge:' . $this->getId();

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getCacheMaxAge() {
    return 0;
  }

}

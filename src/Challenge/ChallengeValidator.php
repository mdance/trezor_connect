<?php

/**
 * @file
 *
 * Contains \Drupal\trezor_connect\ChallengeValidator
 */

namespace Drupal\trezor_connect\Challenge;

use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;
use Symfony\Component\Process\Exception\LogicException;

class ChallengeValidator implements ChallengeValidatorInterface {

  protected $challenge;

  protected $challenge_response;

  /**
   * @inheritDoc
   */
  public function setChallenge(ChallengeInterface $challenge) {
    $this->challenge = $challenge;
  }

  /**
   * @inheritDoc
   */
  public function getChallenge() {
    return $this->challenge;
  }

  /**
   * @inheritDoc
   */
  public function setChallengeResponse(ChallengeResponseInterface $challenge_response) {
    $this->challenge_response = $challenge_response;
  }

  /**
   * @inheritDoc
   */
  public function getChallengeResponse() {
    return $this->challenge_response;
  }

  /**
   * @inheritDoc
   *
   * Adapted from:
   * https://github.com/trezor/connect/blob/gh-pages/examples/server.php
   */
  public function validate() {
    $output = FALSE;

    $challenge = $this->getChallenge();

    $response = $this->getChallengeResponse();

    $version = $response->getVersion();

    $challenge_hidden = $challenge->getChallengeHidden();
    $challenge_visual = $challenge->getChallengeVisual();

    $signature = $response->getSignature();

    if ($version == 1) {
      $challenge_hidden = hex2bin($challenge_hidden);
    }
    else if ($version == 2) {
      $challenge_hidden = hex2bin($challenge_hidden);
      $challenge_hidden = hash('sha256', $challenge_hidden, TRUE);

      $challenge_visual = hash('sha256', $challenge_visual, TRUE);
    }
    else {
      // TODO: Document thrown exception in docblock
      throw new LogicException('The challenge response version is unknown.');
    }

    $message = $challenge_hidden . $challenge_visual;

    $R = substr($signature, 2, 64);
    $S = substr($signature, 66, 64);

    $ecdsa = new BitcoinECDSA();

    $prefix = "\x18Bitcoin Signed Message:\n";

    $len = strlen($message);
    $len = $ecdsa->numToVarIntString($len);

    $data = $prefix . $len . $message;

    $hash = $ecdsa->hash256($data);

    $public_key = $response->getPublicKey();

    $output = $ecdsa->checkSignaturePoints($public_key, $R, $S, $hash);

    return $output;
  }

}

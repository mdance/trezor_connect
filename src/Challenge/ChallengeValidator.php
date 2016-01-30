<?php

/**
 * @file
 *
 * Contains \Drupal\trezor_connect\ChallengeValidator
 */

namespace Drupal\trezor_connect\Challenge;

use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;

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
   */
  public function validate() {
    $output = FALSE;

    // Check challenge response matches the challenge
    // Check challenge response

    //$challenge = $this->getChallenge();
    $response = $this->getChallengeResponse();

    $challenge_hidden = $response->getChallengeHidden();
    $challenge_hidden = hex2bin($challenge_hidden);

    $challenge_visual = $response->getChallengeVisual();

    $signature = $response->getSignature();

    $message = $challenge_hidden . $challenge_visual;

    $R = substr($signature, 2, 64);
    $S = substr($signature, 66, 64);

    $ecdsa = new BitcoinECDSA();

    $prefix = "\x18Bitcoin Signed Message:\n";

    $len = strlen($message);
    $len = $ecdsa->numToVarIntString($len);

    $data = $prefix . $len . $message;

    $hash = $ecdsa->hash256($data);

    $result = $ecdsa->checkSignaturePoints($this->public_key, $R, $S, $hash);

    if ($result) {
      $result = $ecdsa->getAddress($this->public_key);

      if ($result == $this->address) {
        $output = TRUE;
      }
    }

    return $output;
  }
}

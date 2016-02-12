<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\ChallengeValidator\ChallengeValidatorInterface.
 */

namespace Drupal\trezor_connect\ChallengeValidator;


use Drupal\trezor_connect\Challenge\ChallengeInterface;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface;

interface ChallengeValidatorInterface {

  /**
   * Sets the challenge to validate.
   *
   * @param \Drupal\trezor_connect\Challenge\ChallengeInterface $challenge
   *
   * @return mixed
   */
  public function setChallenge(ChallengeInterface $challenge);

  /**
   * Returns the challenge to validate.
   *
   * @return mixed
   */
  public function getChallenge();

  /**
   * Sets the challenge response to validate.
   *
   * @param \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface $challenge_response
   *
   * @return mixed
   */
  public function setChallengeResponse(ChallengeResponseInterface $challenge_response);

  /**
   * Returns the challenge response to validate.
   *
   * @return
   */
  public function getChallengeResponse();

  /**
   * Validates a challenge response.
   *
   * @return mixed
   */
  public function validate();

}
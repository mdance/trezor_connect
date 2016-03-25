<?php
/**
 * @file
 * Contains Drupal\trezor_connect\TrezorConnect
 */
namespace Drupal\trezor_connect;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\trezor_connect\Challenge\Challenge;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface;

interface TrezorConnectInterface {

  /**
   * Provides a string containing the TREZOR connect external javascript url.
   */
  const URL = 'https://trezor.github.io/connect/login.js';

  /**
   * Provides a string containing the TREZOR connect callback function.
   */
  const JS_CALLBACK = 'trezorLogin';

  /**
   * Returns a string containing the display text.
   *
   * @return boolean
   */
  public function getText($mode = MODE_LOGIN, AccountInterface $account = NULL);

  /**
   * Returns a string containing the login text.
   */
  public function getLoginText();

  /**
   * Returns a string containing the registration text.
   */
  public function getRegistrationText();

  /**
   * Returns a string containing the authentication device text.
   */
  public function getManageText(AccountInterface $account = NULL);

  /**
   * Returns a string containing the administrator authentication device text.
   */
  public function getAdminManageText(AccountInterface $account = NULL);

  /**
   * Returns a string containing the icon path.
   *
   * @return mixed
   */
  public function getIcon();

  /**
   * Returns a string indicating if the TREZOR connect javascript should be
   * loaded externally.
   *
   * @return string
   */
  public function getLibraryType();

  /**
   * Returns a string containing the TREZOR connect external javascript url.
   *
   * @return string
   */
  public function getUrl();

  /**
   * Returns a string containing the TREZOR connect implementation.
   *
   * @return string
   */
  public function getImplementation();

  /**
   * Returns a string containing the TREZOR connect callback function.
   *
   * @return string
   */
  public function getCallback();

  /**
   * Returns a string containing the TREZOR connect tag.
   *
   * @return string
   */
  public function getTag();

  /**
   * Returns an integer containing the number of invalid password attempts allowed.
   *
   * @return int
   */
  public function getFloodThreshold();

  /**
   * Returns an integer containing the number of seconds an invalid password attempt is remembered for.
   *
   * @return int
   */
  public function getFloodWindow();

  /**
   * Returns an integer containing the number of seconds before a challenge expires.
   *
   * @return int
   */
  public function getChallengeOffset();

  /**
   * Returns an integer containing the number of seconds before a challenge
   * response expires.
   *
   * @return int
   */
  public function getChallengeResponseOffset();

  /**
   * Returns an array of challenge backends suitable for a form api #options.
   *
   * @return mixed
   */
  public function challengeBackendOptions();

  /**
   * Returns a string containing the challenge backend.
   *
   * @return string
   */
  public function getChallengeBackend();

  /**
   * Returns an array of challenge response backends suitable for a form api #options.
   *
   * @return mixed
   */
  public function challengeResponseBackendOptions();

  /**
   * Returns a string containing the challenge response backend.
   *
   * @return string
   */
  public function getChallengeResponseBackend();

  /**
   * Returns an array of mapping backends suitable for a form api #options.
   *
   * @return mixed
   */
  public function mappingBackendOptions();

  /**
   * Returns a string containing the mapping backend.
   *
   * @return string
   */
  public function getMappingBackend();

  /**
   * Responsible for mapping the challenge response to an account.
   *
   * @param $uid
   *
   * @param ChallengeResponseInterface $challenge_response
   *
   * @return mixed
   */
  public function mapChallengeResponse($uid, ChallengeResponseInterface $challenge_response = NULL);

  /**
   * Returns the challenge response state.
   *
   * @param $uid
   *
   * @return mixed
   */
  public function checkChallengeResponseState($uid);

  /**
   * Deletes mappings associated with an account.
   *
   * @param $uid
   *   The account id whose mappings should be deleted.
   *
   * @return mixed
   */
  public function deleteMapping($uid);

  /**
   * Deletes expired challenge responses.
   *
   * @return mixed
   */
  public function deleteExpiredChallengeResponses();

  /**
   * Deletes expired challenges.
   *
   * @return mixed
   */
  public function deleteExpiredChallenges();

}

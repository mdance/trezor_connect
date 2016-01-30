<?php
/**
 * @file
 * Contains Drupal\trezor_connect\TrezorConnect
 */
namespace Drupal\trezor_connect;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\trezor_connect\Challenge\Challenge;

interface TrezorConnectInterface {

  /**
   * Provides a string containing the administration menu route name.
   */
  const ROUTE_ADMIN = 'trezor_connect.admin';

  /**
   * Provides a string containing the TREZOR connect login callback route.
   */
  const ROUTE_LOGIN = 'trezor_connect.user.login';

  /**
   * Provides a string containing the TREZOR connect register callback url.
   */
  const ROUTE_REGISTER = 'trezor_connect.user.register';

  /**
   * Provides a string containing the TREZOR connect manage url.
   */
  const ROUTE_MANAGE = 'trezor_connect.user.manage';

  /**
   * Provides a string containing the TREZOR connect manage callback url.
   */
  const ROUTE_MANAGE_JS = 'trezor_connect.user.manage.js';

  /**
   * Provides a string containing the administration permission.
   */
  const PERMISSION_ADMIN = 'administer TREZOR connect';

  /**
   * Provides a string containing the use permission.
   */
  const PERMISSION_USE = 'use TREZOR connect';

  /**
   * Provides an integer representing when the javascript assets should be
   * loaded using the TREZOR Connect CDN.
   */
  const EXTERNAL_YES = 1;

  /**
   * Provides an integer representing when the javascript assets should be
   * loaded locally.
   */
  const EXTERNAL_NO = 0;

  /**
   * Provides a boolean indicating if the TREZOR connect javascript should be
   * loaded externally.
   */
  const EXTERNAL = TRUE;

  /**
   * Provides a string containing the TREZOR connect external javascript url.
   */
  const URL = 'https://trezor.github.io/connect/login.js';

  /**
   * Provides a string containing the TREZOR connect callback function.
   */
  const JS_CALLBACK = 'trezorLogin';

  // TODO: Document constants
  const STATE_CHALLENGE_RESPONSE_NOT_FOUND = 0;
  const STATE_CHALLENGE_RESPONSE_INVALID = 1;
  const STATE_CHALLENGE_RESPONSE_NEW = 2;
  const STATE_CHALLENGE_RESPONSE_EXISTS = 3;
  const STATE_CHALLENGE_RESPONSE_UPDATE = 4;
  const STATE_CHALLENGE_RESPONSE_OTHER_ACCOUNT = 5;

  const MODE_LOGIN = 0;
  const MODE_REGISTER = 1;

  /**
   * Returns a string containing the display text.
   *
   * @return boolean
   */
  public function getText();

  /**
   * Returns a boolean indicating if the TREZOR connect javascript should be
   * loaded externally.
   *
   * @return boolean
   */
  public function getExternal();

  /**
   * Returns a string containing the TREZOR connect external javascript url.
   *
   * @return string
   */
  public function getUrl();

  /**
   * Returns a string containing the TREZOR connect callback function.
   *
   * @return string
   */
  public function getCallback();

  /**
   * Creates a challenge.
   *
   * @return Challenge
   */
  public function newChallenge();

  /**
   * Returns a challenge response from the session.
   *
   * @param boolean $validate
   *   A boolean indicating whether to validate the challenge response.
   *
   * @return ChallengeResponse
   *   The challenge response stored on the session.
   */
  public function challengeResponse($validate = TRUE);

  /**
   * Returns a challenge response and mapping state constant.
   *
   * @param integer $uid
   *   The user id to check for mappings.
   *
   * @return integer
   */
  public function checkChallengeResponseState($uid);

  /**
   * Returns a mapping associated with a user id.
   *
   * @param integer $uid
   *   An integer containing the user id whose mapping should be retrieved.
   *
   * @return mixed
   */
  public function getMappingsFromUid($uid);

  /**
   * Returns a mapping associated with a public key.
   *
   * @param string $public_key
   *   A string containing the public key whose mapping should be retrieved.
   *
   * @return mixed
   */
  public function getMappingsFromPublicKey($public_key);

  /**
   * Deletes a mapping.
   *
   * @param integer $uid
   *
   * @return mixed
   */
  public function deleteMapping($uid);
}

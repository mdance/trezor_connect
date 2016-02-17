<?php
/**
 * @file
 * Contains Drupal\trezor_connect\TrezorConnect
 */
namespace Drupal\trezor_connect;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\trezor_connect\Challenge\Challenge;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface;

interface TrezorConnectInterface {

  /**
   * Provides a string containing the config namespace.
   */
  const CONFIG_NS = 'trezor_connect.settings';

  /**
   * Provides a string containing the administration menu route name.
   */
  const ROUTE_ADMIN = 'trezor_connect.admin';

  /**
   * Provides a string containing the TREZOR connect login callback route.
   */
  const ROUTE_LOGIN = 'trezor_connect.user.login';

  /**
   * Provides a string containing the TREZOR connect register callback route.
   */
  const ROUTE_REGISTER = 'trezor_connect.user.register';

  /**
   * Provides a string containing the TREZOR connect manage route.
   */
  const ROUTE_MANAGE = 'trezor_connect.user.manage';

  /**
   * Provides a string containing the TREZOR connect manage callback route.
   */
  const ROUTE_MANAGE_JS = 'trezor_connect.user.manage.js';

  /**
   * Provides a string containing the TREZOR connect disable authentication
   * device route.
   */
  const ROUTE_MANAGE_DISABLE = 'trezor_connect.user.manage.disable';

  /**
   * Provides a string containing the TREZOR connect remove authentication
   * device route.
   */
  const ROUTE_MANAGE_REMOVE = 'trezor_connect.user.manage.remove';

  /**
   * Provides a string containing the user page route.
   */
  const ROUTE_USER = 'user.page';

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
  const MODE_MANAGE = 2;

  const ICON_SOURCE_DEFAULT = 'default';
  const ICON_SOURCE_THEME = 'theme';
  const ICON_SOURCE_CUSTOM = 'custom';

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
   * @return mixed
   */
  public function mapChallengeResponse($uid);

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

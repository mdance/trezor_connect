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
   * Returns an array of mapping backends suitable for a form api #options.
   *
   * @return mixed
   */
  public function mappingBackendOptions();

  /**
   * Responsible for mapping the challenge response to an account.
   *
   * @param $uid
   *
   * @return mixed
   */
  public function mapChallengeResponse($uid);

}

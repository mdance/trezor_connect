<?php
/**
 * @file
 * Provides user hooks.
 */

use Drupal\user\Entity\User;

/**
 * Implements hook_user_insert().
 *
 * Responsible for mapping a challenge response to an account.
 *
 * @param $edit
 * @param $account
 * @param $category
 *
 * @throws \Exception
 */
function trezor_connect_user_insert(User $account) {
  \Drupal::service('trezor_connect')->mapChallengeResponse($account->id());
}

/**
 * Implements hook_user_delete().
 *
 * Responsible for delete any TREZOR connect mappings associated with an
 * account.
 *
 * @param $account
 */
function trezor_connect_user_delete(User $account) {
  \Drupal::service('trezor_connect')->deleteMapping($account->id());
}

/**
 * Implements hook_user_login().
 *
 * Responsible for prompting a user to map their TREZOR device to their account.
 */
function trezor_connect_user_login($account) {
  /*
  $result = \Drupal::service('trezor_connect')->checkChallengeResponseState($account->id());

  if ($result == TrezorConnect::STATE_CHALLENGE_RESPONSE_NEW || $result == TrezorConnect::STATE_CHALLENGE_RESPONSE_UPDATE) {
    $text = t('Click here to map your TREZOR device to your account.');

    $route_parameters = array();

    $route_parameters['user'] = $account;

    $url = Url::fromRoute(TrezorConnect::ROUTE_MANAGE, $route_parameters);

    $link = new Link($text, $url);

    $args = array(
      '!link' => $link,
    );

    $message = t('A TREZOR device has been authenticated to saved to your session, but is not mapped to your account.  @link', $args);

    drupal_set_message($message);
  }
  */
}

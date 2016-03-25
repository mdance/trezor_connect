<?php
/**
 * @file
 * Provides user hooks.
 */

use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\trezor_connect\Enum\ChallengeResponseStates;
use Drupal\trezor_connect\Enum\Routes;

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
 *
 * TODO: Check if this is still necessary
 */
function trezor_connect_user_login($account) {
  $uid = $account->id();

  $result = \Drupal::service('trezor_connect')->checkChallengeResponseState($uid);

  if ($result == ChallengeResponseStates::CREATE || $result == ChallengeResponseStates::UPDATE) {
    $text = t('Click here to manage your authentication devices.');

    $route_parameters = array();

    $route_parameters['user'] = $uid;

    $url = Url::fromRoute(Routes::MANAGE, $route_parameters);

    $link = Link::fromTextAndUrl($text, $url)->toString();

    $args = array(
      '@link' => $link,
    );

    if ($result == ChallengeResponseStates::CREATE) {
      $message = t('A TREZOR device has been authenticated, but is not linked to your account.  @link', $args);
    }
    else if ($result == ChallengeResponseStates::UPDATE) {
      $message = t('A new TREZOR device has been authenticated, but is not linked to your account.  @link', $args);
    }

    drupal_set_message($message);
  }
}

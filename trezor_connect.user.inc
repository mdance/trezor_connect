<?php
/**
 * @file
 * Provides user hooks.
 */

use Drupal\user\Entity\User;

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

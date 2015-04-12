<?php
/**
 * @file
 * Provides cron functionality.
 */

/**
 * Implements hook_cron().
 *
 * Responsible for removing expired challenges.
 */
function trezor_connect_cron() {
  $now = time();
  $offset = trezor_connect_offset();

  $value = $now - $offset;

  $query = db_delete('trezor_connect_challenges');

  $query->condition('created', $value, '<=');

  $query->execute();
}

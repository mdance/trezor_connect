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
  $config = \Drupal::config(TREZOR_CONNECT_NS);

  $now = time();
  $offset = $config->get('offset');

  $value = $now - $offset;

  $query = db_delete(TREZOR_CONNECT_TABLE_CHALLENGES);

  $query->condition('created', $value, '<=');

  $query->execute();
}

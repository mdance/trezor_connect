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
  \Drupal::service('trezor_connect')->deleteExpiredChallengeResponses();
  \Drupal::service('trezor_connect')->deleteExpiredChallenges();
}

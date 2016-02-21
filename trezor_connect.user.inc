<?php
/**
 * @file
 * Provides user hooks.
 */

/**
 * Implements hook_user_insert().
 *
 * Responsible for mapping a TREZOR connect session to an account.
 *
 * @param $edit
 * @param $account
 * @param $category
 *
 * @throws \Exception
 */
function trezor_connect_user_insert(&$edit, $account, $category) {
  if (isset($_SESSION['trezor_connect_response'])) {
    $record = $_SESSION['trezor_connect_response'];
    $challenge = $_SESSION['trezor_connect_challenge'];

    $keys = array(
      'challenge_hidden',
      'challenge_visual',
    );

    foreach ($keys as $key) {
      $record[$key] = $challenge[$key];
    }

    $record['uid'] = $account->uid;

    trezor_connect_write_map($record);
  }
}

/**
 * Implements hook_user_delete().
 *
 * Responsible for delete any TREZOR connect mappings associated with an
 * account.
 *
 * @param $account
 */
function trezor_connect_user_delete($account) {
  trezor_connect_delete_mapping($account);
}

/**
 * Implements hook_user_login().
 *
 * Responsible for prompting a user to map their TREZOR device to their account.
 */
function trezor_connect_user_login(&$edit, $account) {
  if (isset($_SESSION['trezor_connect_response'])) {
    $response = $_SESSION['trezor_connect_response'];
    $challenge = $_SESSION['trezor_connect_challenge'];

    $result = trezor_connect_response_valid($response, $challenge);

    if ($result) {
      $result = trezor_connect_mapping($response);

      if (!$result) {
        $text = t('Click here to map your TREZOR device to your account.');
        $path = 'user/' . $account->uid . '/trezor-connect';

        $link = l($text, $path);

        $args = array(
          '!link' => $link,
        );

        $message = t('A TREZOR device has been authenticated to saved to your session, but is not mapped to your account.  !link', $args);

        drupal_set_message($message);
      }
    }
  }
}

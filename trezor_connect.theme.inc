<?php
/**
 * @file
 * Provides theme functions.
 */

/**
 * Implements hook_theme().
 */
function trezor_connect_theme() {
  $output = array();

  $output['trezor_connect_message'] = array(
    'variables' => array(
      'mode' => NULL,
    ),
  );

  return $output;
}

/**
 * Provides the trezor connect registration message.
 */
function theme_trezor_connect_message(&$variables) {
  $mode = $variables['mode'];

  $attributes = array(
    'class' => array(
      'trezor-connect-message',
      'trezor-connect-message-' . $mode,
    ),
  );

  $attributes = drupal_attributes($attributes);

  $message = trezor_connect_message($mode);
  $message = t($message);

  $output = <<<EOF
<div {$attributes}>
  {$message}
</div>
EOF;

  return $output;
}

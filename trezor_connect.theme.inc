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
      'message' => NULL,
    ),
  );

  return $output;
}

/**
 * Provides the trezor connect registration message.
 */
function theme_trezor_connect_message(&$variables) {
  $message = $variables['message'];

  $attributes = array(
    'class' => array(
      'trezor-connect-message',
    ),
  );

  $attributes = drupal_attributes($attributes);

  $output = <<<EOF
<div {$attributes}>
  {$message}
</div>
EOF;

  return $output;
}

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
 * Provides the TREZOR connect registration message.
 */
function theme_trezor_connect_message(&$variables) {
  $type = 'info';

  if (isset($variables['type'])) {
    $type = $variables['type'];
  }

  $message = $variables['message'];

  $attributes = array(
    'class' => array(
      'trezor-connect-message',
      'trezor-connect-message-' . $type,
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

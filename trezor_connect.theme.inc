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

  $output['trezor_connect'] = array(
    'render element' => 'element',
  );

  $output['trezor_connect_message'] = array(
    'variables' => array(
      'type' => NULL,
      'message' => NULL,
    ),
  );

  return $output;
}

/**
 * Provides the trezor_connect preprocess function.
 *
 * @param $variables
 */
function template_preprocess_trezor_connect(&$variables) {
  $element = &$variables['element'];

  $keys = array(
    'tag',
    'text',
    'icon',
    'callback',
    'challenge_hidden',
    'challenge_visual',
  );

  foreach ($keys as $key) {
    if (isset($element['#' . $key])) {
      $variables[$key] = $element['#' . $key];
    }
  }

  // TODO: Check if this is the right thing to do
  $variables['children'] = \Drupal::service('renderer')->render($element);
}

/**
 * Provides the trezor_connect_message preprocess function.
 *
 * @param $variables
 */
function template_preprocess_trezor_connect_message(&$variables) {
  $type = 'info';

  if (isset($variables['type'])) {
    $type = $variables['type'];
  }

  $attributes = &$variables['attributes'];

  $attributes['class'][] = 'trezor-connect-message';
  $attributes['class'][] = 'trezor-connect-message-type-' . $type;
}

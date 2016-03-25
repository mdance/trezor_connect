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

  // TODO: Check if this is still necessary
  $output['trezor_connect'] = array(
    'render element' => 'element',
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

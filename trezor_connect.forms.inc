<?php
/**
 * @file
 * Provides common form functions.
 */

/**
 * Implements hook_form_alter().
 *
 * Responsible for adding the Sign In button to the user registration, and
 * login forms.
 */
function trezor_connect_form_alter(&$form, &$form_state, $form_id) {
  $ids = array(
    'user_login',
    'user_login_block',
    'user_register_form',
  );

  $found = in_array($form_id, $ids);

  if ($found) {
    trezor_connect_alter_form($form, $form_state, $form_id);
  }
}

/**
 * Responsible for adding the TREZOR connect functionality to a form.
 *
 * @param $form_id
 * @param $form
 * @param $form_state
 */
function trezor_connect_alter_form(&$form, &$form_state, $form_id) {
  $access = user_access(TREZOR_CONNECT_PERMISSION_USE);

  if ($access) {
    $path = drupal_get_path('module', 'trezor_connect');

    $data = $path . '/css/trezor_connect.css';

    $form['#attached']['css'][$data] = array();

    $form['#attached']['library'][] = array(
      'system',
      'drupal.ajax',
    );

    $options = array();

    $options['scope'] = 'footer';

    $external = trezor_connect_external();

    if ($external) {
      $options['type'] = 'external';
      $data = trezor_connect_url();
    }
    else {
      $data = $path . '/js/login.js';
    }

    $form['#attached']['js'][$data] = $options;

    $data = $path . '/js/trezor_connect.js';

    $options = array();

    $options['scope'] = 'footer';

    $form['#attached']['js'][$data] = $options;

    $options = array(
      //'absolute' => TRUE,
    );

    $ids = array(
      'user_login',
      'user_login_block',
    );

    $result = in_array($form_id, $ids);

    if ($result) {
      $mode = 'login';

      $url = url(TREZOR_CONNECT_URL_LOGIN, $options);
    }
    else {
      $mode = 'register';

      $url = url(TREZOR_CONNECT_URL_REGISTER, $options);
    }

    $items = array();

    $attributes = array();

    $callback = trezor_connect_callback();

    $attributes['callback'] = $callback;

    if (isset($form_state['storage']['trezor_connect_challenge'])) {
      $challenge = $form_state['storage']['trezor_connect_challenge'];
    }
    else {
      $challenge = trezor_connect_challenge();

      trezor_connect_write_challenge($challenge);
    }

    $attributes['challenge_hidden'] = $challenge['challenge_hidden'];
    $attributes['challenge_visual'] = $challenge['challenge_visual'];

    /*
    $attributes = drupal_attributes($attributes);

    $data = <<<EOF
  <trezor:login $attributes />
  EOF;
    */

    $form['trezor_connect'] = array(
      '#type' => 'container',
    );

    $form['trezor_connect']['button'] = array(
      '#theme' => 'html_tag',
      '#tag' => 'trezor:login',
      '#attributes' => $attributes,
      '#weight' => 2,
    );

    $form['#attached']['js'][] = array(
      'data' => array(
        'trezor_connect' => array(
          'mode' => $mode,
          'url' => $url,
          'form_id' => $form_id,
          'challenge' => $challenge,
        ),
      ),
      'type' => 'setting',
    );

  }
}

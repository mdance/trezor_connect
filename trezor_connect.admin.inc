<?php
/**
 * @file
 * Provides administration functionality.
 */

/**
 * Provides the admin settings form.
 */
function trezor_connect_admin_form() {
  $form = array();

  $description = t('Please specify the message to display when a user has authenticated from the registration form.');
  $default_value = trezor_connect_message('register');

  $form['trezor_connect_message_register'] = array(
    '#type' => 'textarea',
    '#title' => t('Registration Message'),
    '#description' => $description,
    '#default_value' => $default_value,
  );

  $description = t('Please specify the message to display when a user has authenticated from the login form.');
  $default_value = trezor_connect_message('login');

  $form['trezor_connect_message_login'] = array(
    '#type' => 'textarea',
    '#title' => t('Login Message'),
    '#description' => $description,
    '#default_value' => $default_value,
  );

  $description = t('Please specify whether to load the trezor connect javascript externally.');
  $options = array(
    TRUE => t('Yes'),
    FALSE => t('No'),
  );

  $default_value = trezor_connect_external();

  $form['trezor_connect_external'] = array(
    '#type' => 'radios',
    '#title' => t('Use CDN'),
    '#description' => $description,
    '#options' => $options,
    '#default_value' => $default_value,
  );

  $description = t('Please specify the external trezor connect javascript url.');
  $default_value = trezor_connect_url();

  $form['trezor_connect_url'] = array(
    '#type' => 'textfield',
    '#title' => t('External Trezor Connect Javascript URL'),
    '#description' => $description,
    '#default_value' => $default_value,
  );

  $description = t('Please specify the trezor connect callback function.');
  $default_value = trezor_connect_callback();

  $form['trezor_connect_callback'] = array(
    '#type' => 'textfield',
    '#title' => t('Trezor Connect Callback'),
    '#description' => $description,
    '#default_value' => $default_value,
  );

  $description = t('Please specify the challenge expiration time.');
  $default_value = trezor_connect_offset();

  $map = array(
    TREZOR_CONNECT_OFFSET,
    TREZOR_CONNECT_OFFSET * 2,
    TREZOR_CONNECT_OFFSET * 3,
  );

  $options = drupal_map_assoc($map, 'format_interval');

  $form['trezor_connect_offset'] = array(
    '#type' => 'select',
    '#title' => t('Challenge Expiration Period'),
    '#options' => $options,
    '#empty_value' => '60',
    '#description' => $description,
    '#default_value' => $default_value,
  );

  return system_settings_form($form);
}

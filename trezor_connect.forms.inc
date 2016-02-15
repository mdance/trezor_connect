<?php
/**
 * @file
 * Provides common form functions.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_alter().
 *
 * Responsible for adding the Sign In button to the user registration, and
 * login forms.
 */
function trezor_connect_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  $ids = array(
    'user_login_form',
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
function trezor_connect_alter_form(&$form, FormStateInterface $form_state, $form_id) {
  // TODO: Implement password authentication for manage mode
  // TODO: Allow administrators to assign a TREZOR device user accounts
  $form['trezor_connect'] = array(
    '#type' => 'trezor_connect',
    '#form_id' => $form_id,
    '#weight' => 1,
  );
}

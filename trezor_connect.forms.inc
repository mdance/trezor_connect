<?php
/**
 * @file
 * Provides common form functions.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\trezor_connect\Enum\Permissions;
use Drupal\trezor_connect\Enum\Modes;
use Drupal\trezor_connect\Enum\Routes;

use Drupal\Core\Url;

/**
 * Implements hook_form_alter().
 *
 * Responsible for adding the Sign In button to the user registration, and
 * login forms.
 */
function trezor_connect_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  $account = \Drupal::currentUser();

  $access = FALSE;

  $register = FALSE;

  if ($form_id == 'user_register_form') {
    $register = TRUE;
    $access = $account->hasPermission(Permissions::REGISTER);
  }

  $ids = array(
    'user_login_form',
    'user_login_block',
  );

  $login = in_array($form_id, $ids);

  if ($login) {
    $access = $account->hasPermission(Permissions::LOGIN);
  }

  if ($access && ($register || $login)) {
    $tc = \Drupal::service('trezor_connect');

    $route_parameters = array(
      'js' => 'nojs',
    );

    $options = array(
      'absolute' => TRUE,
    );

    if ($register) {
      $mode = Modes::REGISTER;

      $url = Url::fromRoute(Routes::REGISTER, $route_parameters, $options);
    }
    else {
      $mode = Modes::LOGIN;

      $url = Url::fromRoute(Routes::LOGIN, $route_parameters, $options);
    }

    $text = $tc->getText($mode, $account);

    $url = $url->toString();

    $form['trezor_connect'] = array(
      '#type' => 'trezor_connect',
      '#weight' => 1,
      '#text' => $text,
      '#url' => $url,
    );
  }
}

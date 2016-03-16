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
use Drupal\Component\Utility\NestedArray;
use Drupal\trezor_connect\Enum\MappingStatus;
use Drupal\user\Entity\User;
use Drupal\Core\Link;

/**
 * Implements hook_form_alter().
 *
 * Responsible for adding the Sign In button to the user registration.
 */
function trezor_connect_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  $account = \Drupal::currentUser();

  if ($form_id == 'user_register_form') {
    $access = $account->hasPermission(Permissions::REGISTER);

    if ($access) {
      $form['#trezor_connect_validate'] = $form['#validate'];

      $form['#validate'] = array();

      $form['#validate'][] = 'trezor_connect_user_register_form_validate';

      $wrapper_id = 'wrapper-trezor-connect';

      $form['#prefix'] = '<div id="' . $wrapper_id . '">';
      $form['#suffix'] = '</div>';

      /**
       * @var Drupal\trezor_connect\TrezorConnectInterface $tc
       */
      $tc = \Drupal::service('trezor_connect');

      $mode = Modes::REGISTER;

      $text = $tc->getText($mode, $account);

      $key = 'trezor_connect';

      $form[$key] = array(
        '#type' => 'trezor_connect',
        '#weight' => 1,
        '#text' => $text,
        '#ajax_wrapper_id' => $wrapper_id,
      );
    }
  }
}

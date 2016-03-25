<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Element\TrezorConnectElement.
 */

namespace Drupal\trezor_connect\Element;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Html;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponse;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface;
use Symfony\Component\HttpFoundation\Request;

use Drupal\trezor_connect\Challenge\ChallengeInterface;
use Drupal\trezor_connect\Enum\Implementations;
use Drupal\trezor_connect\Enum\Permissions;
use Drupal\trezor_connect\Enum\Modes;
use Drupal\trezor_connect\Enum\Routes;
use Drupal\trezor_connect\Enum\Tags;
use Drupal\trezor_connect\Enum\Messages;
use Drupal\trezor_connect\Enum\LibraryType;

use Drupal\trezor_connect\Form\ManageForm;

/**
 * Provides a TREZOR Connect form element.
 *
 * Usage example:
 *
 * @code
 * $form['trezor_connect'] = array(
 *   '#type' => 'trezor_connect',
 *   '#text' => t('Sign in with TREZOR'),
 * );
 * @endcode
 *
 * @RenderElement("trezor_connect")
 */
class TrezorConnectElement extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    $tc = \Drupal::service('trezor_connect');

    $library_type = $tc->getLibraryType();
    $icon = $tc->getIcon();

    $output = array(
      '#input' => TRUE,
      '#tree' => TRUE,
      '#value_callback' => array(
        $class,
        'valueCallback',
      ),
      '#process' => array(
        array(
          $class,
          'process',
        ),
        array(
          $class,
          'processAjaxForm',
        ),
        array(
          $class,
          'processGroup',
        ),
      ),
      '#pre_render' => array(
        //array($class, 'preRender'),
        array(
          $class,
          'preRenderGroup',
        ),
      ),
      '#element_validate' => array(
        array(
          $class,
          'validatePassword',
        ),
        array(
          $class,
          'validateChallengeResponse',
        ),
        array(
          $class,
          'validateChallengeResponseMapping',
        ),
      ),
      '#theme_wrappers' => array(
        'container',
      ),
      // Provides the account to map a success challenge response to, or
      // password check against (if the default password check validation
      // callable is used)
      '#account' => NULL,
      // Provides a string containing the form api key name for the trezor
      // connect button
      '#key' => 'button',
      // Provides a string containing the text to display for the challenge button
      '#text' => NULL,
      // Provides a boolean indicating whether to display the password check
      // field
      '#password' => FALSE,
      // Provides a string containing the form api key name for the password
      // field
      '#password_key' => 'password',
      // Provides a string containing a LibraryType constant indicating whether
      // to use the external or internal TREZOR Connect javascript library.
      '#library_type' => $library_type,
      // Provides a IconSource constant indicating which source to use for the
      // icon.
      '#icon' => $icon,
      // Provides a Challenge object containing the challenge which should be
      // issued to the user.
      '#challenge' => NULL,
      // Provides a string containing the challenge key
      '#challenge_key' => 'challenge',
      // Provides a string containing the challenge manager service.
      '#challenge_manager_service' => 'trezor_connect.challenge_manager',
      // Provides a string containing the challenge token key
      '#challenge_token_key' => 'challenge_token',
      // Provides a string containing the challenge response key
      '#challenge_response_key' => 'challenge_response',
      // Provides a string containing the event name to trigger to perform the
      // challenge response authentication
      '#event' => 'authenticate.trezor_connect',
      // Provides a string containing the password service
      '#password_service' => 'password',
      // Provides a boolean indicating if error messages should be set
      //'#set_error_messages' => TRUE,
      // Provides a string containing the empty password error message
      '#message_password_empty' => Messages::PASSWORD_EMPTY,
      // Provides a string containing the invalid password error message
      '#message_password_invalid' => Messages::PASSWORD_INVALID,
      // Provides a string containing the challenge validator service
      '#challenge_validator_service' => 'trezor_connect.challenge_validator',
      // Provides a string containing the invalid challenge response error
      // message
      '#message_challenge_response_invalid' => Messages::CHALLENGE_RESPONSE_INVALID,
      // Provides a boolean to determine whether to create an AJAX wrapper
      '#create_ajax_wrapper' => FALSE,
      // Provides a string containing the ajax wrapper id
      '#ajax_wrapper_id' => NULL,
      // Provides a #ajax callback callable
      '#ajax_callback' => NULL,
      // Provides an array of submit handlers to attach to the authenticate
      // button
      '#submit' => array(),
      // Provides a boolean indicating whether to set the #limit_validation
      // property on the button element.
      '#set_button_limit_validation_errors' => TRUE,
      // Provides the #limit_validation_errors property for the button element,
      // if NULL and #set_button_limit_validation_errors is TRUE, the
      // $element['#parents'] will be set.
      '#button_limit_validation_errors' => NULL,
      // Provides a string containing the mapping manager service.
      '#mapping_manager_service' => 'trezor_connect.mapping_manager',
      // Provides a boolean indicating whether to check for existing challenge
      // response mappings
      '#validate_challenge_response_mapping' => FALSE,
      // Provides a string containing the mapping exists message
      '#message_challenge_response_mapping_exists' => Messages::CHALLENGE_RESPONSE_MAPPING_EXISTS,
    );

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    $output = array();

    $password_key = $element['#password_key'];
    $challenge_key = $element['#challenge_key'];
    $challenge_token_key = $element['#challenge_token_key'];
    $challenge_response_key = $element['#challenge_response_key'];

    if (!$input) {
      $output[$password_key] = NULL;

      $challenge = $element['#challenge'];

      if (is_null($challenge)) {
        $challenge = \Drupal::service($element['#challenge_manager_service'])->get();

        $element['#challenge'] = $challenge;
      }

      if (!($challenge instanceof ChallengeInterface)) {
        $message = 'Invalid challenge';

        // TODO: Create challenge exception class
        throw new \Exception($message);
      }

      $output[$challenge_key] = $challenge;
      $output[$challenge_token_key] = static::challengeToken($challenge);

      $output[$challenge_response_key] = NULL;
    }
    else {
      if (isset($input[$challenge_key]) && is_numeric($input[$challenge_key])) {
        // Convert the challenge id into a challenge object
        $challenge = \Drupal::service($element['#challenge_manager_service'])->get($input[$challenge_key]);

        if ($challenge) {
          $challenge_token = static::challengeToken($challenge);

          // Check the internal/submitted challenge tokens match
          if ($challenge_token != $input[$challenge_token_key]) {
            unset($input[$challenge_token_key]);
          }
          else {
            if (!isset($element['#challenge'])) {
              $element['#challenge'] = $challenge;
            }

            $input[$challenge_key] = $challenge;
          }
        }
      }

      if (isset($input[$challenge_response_key]) && !empty($input[$challenge_response_key])) {
        // Decode the challenge response JSON
        $challenge_response_obj = json_decode($input[$challenge_response_key]);

        if ($challenge_response_obj) {
          // Convert to a ChallengeResponse object
          $challenge_response = new ChallengeResponse();

          $challenge_response->setSignature($challenge_response_obj->signature);
          $challenge_response->setPublicKey($challenge_response_obj->public_key);
          $challenge_response->setVersion($challenge_response_obj->version);

          $input[$challenge_response_key] = $challenge_response;
        }
      }

      $output = $input;
    }

    return $output;
  }

  public static function challengeToken(ChallengeInterface $challenge) {
    $value = (string)$challenge;

    $private_key = \Drupal::service('private_key')->get();
    $salt = Settings::getHashSalt();

    $key = $private_key . $salt;

    $output = Crypt::hmacBase64($value, $key);

    return $output;
  }

  /**
   * Processes a form button element.
   */
  public static function process(&$element, FormStateInterface $form_state, &$complete_form) {
    // TODO: Refactor to use lazy builder and placeholder, see form action example
    $challenge_key = $element['#challenge_key'];

    $challenge = $element['#challenge'];

    if (!($challenge instanceof ChallengeInterface)) {
      $message = 'Invalid challenge';

      throw new \Exception($message);
    }

    if (is_null($element['#account'])) {
      $current_user = \Drupal::currentUser();

      $element['#account'] = $current_user;
    }

    if (!($element['#account'] instanceof AccountInterface)) {
      $message = 'Invalid account';

      throw new \Exception($message);
    }

    $selector = $element['#attributes']['data-drupal-selector'];

    if ($element['#password']) {
      $password_key = $element['#password_key'];

      if (!isset($element[$password_key])) {
        $element[$password_key] = array(
          '#type' => 'password',
          '#required' => TRUE,
          '#title' => t('Current password'),
        );
      }
    }

    $wrapper_id = $element['#ajax_wrapper_id'];

    if (empty($wrapper_id)) {
      $parts = array_merge(['wrapper'], $element['#parents'], ['trezor-connect']);

      $wrapper_id = implode('-', $parts);
      $wrapper_id = Html::getId($wrapper_id);
    }

    if ($element['#create_ajax_wrapper']) {
      $element['#prefix'] = '<div id="' . $wrapper_id . '">';
      $element['#suffix'] = '</div>';
    }

    $key = $element['#key'];

    $element[$key] = array(
      '#type' => $element['#tag'],
      '#value' => $element['#text'],
      //'#theme' => 'trezor_connect',
      //'#theme_wrappers' => array(
      //  'container',
      //),
      //'#process' => array(
      //  array(get_class(), 'processAjaxForm'),
      //),
      '#ajax' => array(
        'event' => $element['#event'],
        'wrapper' => $wrapper_id,
      ),
    );

    $button = &$element[$key];

    if (is_array($element['#submit'])) {
      $button['#submit'] = $element['#submit'];
    }

    if (isset($element['#ajax_callback']) && is_callable($element['#ajax_callback'])) {
      $button['#ajax']['callback'] = $element['#ajax_callback'];
    }
    else {
      $button['#ajax']['callback'] = array(
        get_called_class(),
        'jsCallback',
      );
    }

    if ($element['#set_button_limit_validation_errors']) {
      if (is_null($element['#button_limit_validation_errors'])) {
        $button['#limit_validation_errors'] = array(
          $element['#parents'],
        );
      }
      else {
        $button['#limit_validation_errors'] = $element['#button_limit_validation_errors'];
      }
    }

    $element['#challenge_hidden'] = $challenge->getChallengeHidden();
    $element['#challenge_visual'] = $challenge->getChallengeVisual();

    $map = array(
      'text',
      'icon',
      'challenge_hidden',
      'challenge_visual',
    );

    foreach ($map as $value) {
      $property = '#' . $value;

      $button[$property] = $element[$property];
    }

    Element::setAttributes($button, $map);

    $challenge_js = $challenge->toArray();

    $element[$challenge_key] = array(
      '#type' => 'hidden',
      '#value' => $challenge->getId(),
    );

    $challenge_token = static::challengeToken($challenge);

    $element[$element['#challenge_token_key']] = array(
      '#type' => 'hidden',
      '#value' => $challenge_token,
    );

    $element[$element['#challenge_response_key']] = array(
      '#type' => 'hidden',
    );

    $element['#attached']['drupalSettings']['trezor_connect']['elements'][$selector] = array(
      'icon' => $element['#icon'],
      'challenge' => $challenge_js,
      'selector' => $selector,
      'event' => $element['#event'],
      'key' => $element['#key'],
    );

    $element['#attached']['library'][] = 'trezor_connect/core';

    if ($element['#library_type'] == LibraryType::EXTERNAL) {
      $element['#attached']['library'][] = 'trezor_connect/external';
    }
    else {
      $element['#attached']['library'][] = 'trezor_connect/local';
    }

    $renderer = \Drupal::service('renderer');

    $renderer->addCacheableDependency($element, $challenge);

    \Drupal::service('page_cache_kill_switch')->trigger();

    return $element;
  }

  /**
   * Provides the password validation handler.
   *
   * @param $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $complete_form
   */
  public static function validatePassword(&$element, FormStateInterface $form_state, &$complete_form) {
    $valid = NULL;

    $user = $element['#account'];

    $values = $form_state->getValues();

    $input_exists = FALSE;

    $input = NestedArray::getValue($values, $element['#parents'], $input_exists);

    if ($input_exists) {
      $key = $element['#password_key'];

      if (isset($element[$key])) {
        if (!isset($input[$key]) || empty($input[$key])) {
          $valid = FALSE;

          $message = t($element['#message_password_empty']);

          $form_state->setError($element[$key], $message);
        }
        else {
          $password = $input[$key];
          $password = trim($password);

          $hash = $user->getPassword();

          $valid = \Drupal::service($element['#password_service'])
            ->check($password, $hash);

          if (!$valid) {
            $message = t($element['#message_password_invalid']);

            $form_state->setError($element[$key], $message);
          }
        }
      }
    }
  }

  public static function validateChallengeResponse(&$element, FormStateInterface $form_state, &$complete_form) {
    $challenge_response_key = $element['#challenge_response_key'];

    $values = $form_state->getValues();

    $input_exists = FALSE;

    $input = NestedArray::getValue($values, $element['#parents'], $input_exists);

    if ($input_exists) {
      if (isset($input[$challenge_response_key]) && $input[$challenge_response_key] instanceof ChallengeResponseInterface) {
        $valid = FALSE;

        $challenge_response = $input[$challenge_response_key];

        $challenge_key = $element['#challenge_key'];

        if (isset($input[$challenge_key]) && $input[$challenge_key] instanceof ChallengeInterface) {
          $challenge = $input[$challenge_key];

          $challenge_response->setChallenge($challenge);

          $challenge_validator = \Drupal::service($element['#challenge_validator_service']);

          $challenge_validator->setChallenge($challenge);
          $challenge_validator->setChallengeResponse($challenge_response);

          $result = $challenge_validator->validate();

          if ($result) {
            $valid = TRUE;

            $input[$challenge_response_key] = $challenge_response;

            $form_state->setValueForElement($element[$challenge_response_key], $input[$challenge_response_key]);
          }
        }

        if (!$valid) {
          $message = t($element['#message_challenge_response_invalid']);

          $form_state->setError($element[$challenge_response_key], $message);
        }
      }
    }
  }

  public static function validateChallengeResponseMapping(&$element, FormStateInterface $form_state, &$complete_form) {
    if ($element['#validate_challenge_response_mapping']) {
      $triggering_element = $form_state->getTriggeringElement();

      if ($triggering_element['#value'] == $element[$element['#key']]['#value']) {
        $challenge_response_key = $element['#challenge_response_key'];

        $key = array_merge($element['#parents'], array($challenge_response_key));

        $challenge_response = $form_state->getValue($key);

        if ($challenge_response) {
          $public_key = $challenge_response->getPublicKey();

          $mappings = \Drupal::service($element['#mapping_manager_service'])
            ->getFromPublicKey($public_key);
          $total = count($mappings);

          if ($total > 0) {
            $form_state->setError($element, $element['#message_challenge_response_mapping_exists']);
          }
        }
      }
    }
  }

  public static function jsCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

}

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
    $implementation = $tc->getImplementation();
    $callback = $tc->getCallback();
    $tag = $tc->getTag();
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
      // Provides a string containing the URL that will be called to handle
      // challenge responses
      '#url' => NULL,
      // Provides a boolean indicating whether to display the password check
      // field
      '#password' => FALSE,
      // Provides a string containing the form api key name for the password
      // field
      '#password_key' => 'password',
      // Provides a string containing a LibraryType constant indicating whether
      // to use the external or internal TREZOR Connect javascript library.
      '#library_type' => $library_type,
      // Provides a Implementation constant indicating whether to use the
      // trezor:login button, or the javascript API
      '#implementation' => $implementation,
      // Provides a string containing the global javascript function to be
      // called if the trezor:login implementation is used.  This will be
      // set as the callback property on the trezor:login element.
      '#callback' => $callback,
      // Provides a Tag constant indicating which element type to use to trigger
      // the popup window displaying the challenge
      '#tag' => $tag,
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
          $challenge = array_shift($challenge);

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

    $id = $element['#id'];

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

    $parts = array_merge(['wrapper'], $element['#parents'], ['trezor-connect']);
    $wrapper_id = implode('-', $parts);
    $wrapper_id = Html::getId($wrapper_id);

    $element['#prefix'] = '<div id="' . $wrapper_id . '">';
    $element['#suffix'] = '</div>';

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
        'callback' => array(
          get_called_class(),
          'jsCallback',
        ),
        'options' => array(
          'query' => array(
            'element_parents' => implode('/', $element['#array_parents']),
          ),
        ),
        'wrapper' => $wrapper_id,
      ),
      '#limit_validation_errors' => array(
        $element['#parents'],
      ),
    );

    $button = &$element[$key];

    $element['#challenge_hidden'] = $challenge->getChallengeHidden();
    $element['#challenge_visual'] = $challenge->getChallengeVisual();

    $map = array(
      'tag',
      'text',
      'icon',
      'challenge_hidden',
      'challenge_visual',
    );

    if ($element['#implementation'] == Implementations::BUTTON) {
      $map['callback'];
    }

    foreach ($map as $value) {
      $property = '#' . $value;

      $button[$property] = $element[$property];
    }

    if ($element['#implementation'] == Implementations::BUTTON) {
      Element::setAttributes($button, $map);
    }

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

    $element['#attached']['drupalSettings']['trezor_connect']['elements'][$id] = array(
      'implementation' => $element['#implementation'],
      'callback' => $element['#callback'],
      'icon' => $element['#icon'],
      'challenge' => $challenge_js,
      'url' => $element['#url'],
      'id' => $id,
      'tag' => $element['#tag'],
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

    /*
    $renderer = \Drupal::service('renderer');

    $renderer->addCacheableDependency($element, $challenge);

    \Drupal::service('page_cache_kill_switch')->trigger();
    */

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
    $valid = FALSE;

    $challenge_response_key = $element['#challenge_response_key'];

    $values = $form_state->getValues();

    $input_exists = FALSE;

    $input = NestedArray::getValue($values, $element['#parents'], $input_exists);

    if ($input_exists) {
      if (isset($input[$challenge_response_key]) && $input[$challenge_response_key] instanceof ChallengeResponseInterface) {
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

            $form_state->setValueForElement($element['challenge_response'], $input[$challenge_response_key]);
          }
        }
      }
    }

    if (!$valid) {
      $message = t($element['#message_challenge_response_invalid']);

      $form_state->setError($element[$challenge_response_key], $message);
    }
  }

  public static function jsCallback(&$form, FormStateInterface &$form_state, Request $request) {
    // TODO: Fix AJAX handling
    $triggering_element = $form_state->getTriggeringElement();

    $parents = $triggering_element['#parents'];
    $parents = array_splice($parents, -1);

    $subform = NestedArray::getValue($form, $parents);

    return $subform;
  }

}

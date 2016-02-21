<?php
/**
 * @file
 * Provides registration form functionality.
 */

/**
 * Provides the page callback used to process a TREZOR connect registration
 * response.
 */
function trezor_connect_page_callback_register($type = 'nojs') {
  $output = NULL;

  $response = $_POST['response'];
  $challenge = $_POST['challenge'];

  $result = trezor_connect_response_valid($response, $challenge);

  if (!$result) {
    if ($type == 'nojs') {
      drupal_access_denied();
    }
    else {
      $commands = array();

      $selector = '';

      if (isset($_POST['selector'])) {
        $selector = $_POST['selector'];
        $selector = check_plain($selector);
        //$selector = '#' . $selector;
      }

      $arguments = array();

      $arguments['redirect'] = FALSE;
      $arguments['error'] = TRUE;

      $message = t('An error has occurred validating your TREZOR credentials.');

      $variables = array(
        'type' => 'error',
        'message' => $message,
      );

      $message = theme('trezor_connect_message', $variables);

      $arguments['message'] = $message;

      // IMPORTANT: misc/ajax.js line 605 $element[response.method].apply($element, response.arguments);
      // requires a very specific format otherwise the $arguments will be passed as undefined
      $arguments = array(
        'callback',
        $arguments,
      );

      $commands[] = ajax_command_invoke($selector, 'trezor_connect', $arguments);

      $output = array(
        '#type' => 'ajax',
        '#commands' => $commands,
      );

      $output = ajax_deliver($output);
    }
  }
  else {
    $result = trezor_connect_mapping($response);

    if (is_array($result) && isset($result['uid'])) {
      $text = t('please click here to login');
      $path = 'user/login';

      $link = l($text, $path);

      $args = array(
        '!link' => $link,
      );

      $message = t('There is already an account associated with the TREZOR, !link', $args);

      if ($type != 'ajax') {
        drupal_set_message($message, 'warning');
      }
    }
    else {
      $_SESSION['trezor_connect_response'] = $response;
      $_SESSION['trezor_connect_challenge'] = $challenge;

      $message = t('Your TREZOR device authentication has been saved to your session, please complete the registration process to associate your TREZOR device with your account.');

      if ($type != 'ajax') {
        drupal_set_message($message);
      }
    }

    if ($type != 'ajax') {
      $path = 'user/register';

      drupal_goto($path);
    }
    else {
      $commands = array();

      $selector = '';

      if (isset($_POST['selector'])) {
        $selector = $_POST['selector'];
        $selector = check_plain($selector);
        //$selector = '#' . $selector;
      }

      $arguments = array();

      $arguments['redirect'] = FALSE;

      $variables = array(
        'message' => $message,
      );

      $message = theme('trezor_connect_message', $variables);

      $arguments['message'] = $message;

      // IMPORTANT: misc/ajax.js line 605 $element[response.method].apply($element, response.arguments);
      // requires a very specific format otherwise the $arguments will be passed as undefined
      $arguments = array(
        'callback',
        $arguments,
      );

      $commands[] = ajax_command_invoke($selector, 'trezor_connect', $arguments);

      $output = array(
        '#type' => 'ajax',
        '#commands' => $commands,
      );

      $output = ajax_deliver($output);
    }
  }

  return $output;
}

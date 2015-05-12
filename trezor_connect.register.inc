<?php
/**
 * @file
 * Provides registration form functionality.
 */

/**
 * Provides the page callback used to process a trezor connect registration
 * response.
 */
function trezor_connect_page_callback_register($type = 'nojs') {
  $output = NULL;

  $response = $_POST['response'];

  $result = trezor_connect_response_valid($response);

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

      $arguments['error'] = TRUE;

      $message = t('An error has occurred validating your trezor credentials.');

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

      $message = t('There is already an account associated with the trezor, !link', $args);

      if ($type != 'ajax') {
        drupal_set_message($message, 'warning');
      }
    }
    else {
      $_SESSION['trezor_connect_response'] = $response;

      $message = t('Your trezor device authentication has been saved to your session, please complete the registration process to associate your trezor device with your account.');

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

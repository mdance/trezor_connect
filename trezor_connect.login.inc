<?php
/**
 * @file
 * Provides login form functionality.
 */

/**
 * Provides the page callback used to process a trezor connect login response.
 */
function trezor_connect_page_callback_login($type = 'nojs') {
  global $user;

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
      $user = user_load($result['uid']);

      drupal_session_regenerate();

      if ($type != 'ajax') {
        $message = t('You have been successfully logged in using your trezor device.');

        drupal_set_message($message);

        $path = 'user';

        drupal_goto($path);
      }
      else {
        $text = t('click here');
        $path = 'user';

        $link = l($text, $path);

        $args = array(
          '!link' => $link,
        );

        $message = t('You have been successfully logged in using your trezor device, you should now be automatically redirected, otherwise !link', $args);
      }
    }
    else {
      $_SESSION['trezor_connect_response'] = $response;

      $text = t('click here to register an account');
      $path = 'user/register';

      $register = l($text, $path);

      $args = array(
        '!register' => $register,
      );

      $message = t('There is no account associated with your trezor device.  Please login with your existing username and password to associate your account with your trezor device, otherwise !register.', $args);

      if ($type != 'ajax') {
        drupal_set_message($message, 'warning');

        $path = 'user/login';

        drupal_goto($path);
      }
    }

    if ($type == 'ajax') {
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

      $options = array(
        'absolute' => TRUE,
      );

      $url = url('user', $options);

      $arguments['url'] = $url;

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

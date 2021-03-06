<?php
/**
 * @file
 * Provides login form functionality.
 */

/**
 * Provides the page callback used to process a TREZOR connect login response.
 */
function trezor_connect_page_callback_login($type = 'nojs') {
  global $user;

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
    $redirect = TRUE;

    $result = trezor_connect_mapping($response);

    if (is_array($result) && isset($result['uid'])) {
      $account = user_load($result['uid']);

      if ($account->status == 0) {
        $redirect = FALSE;

        $message = <<<EOF
The account associated with your TREZOR device is not active.  If you have just
registered, your account may be awaiting to be approved by an administrator.
EOF;
        $message = t($message);

        drupal_set_message($message);

        if ($type != 'ajax') {
          $path = 'user';

          drupal_goto($path);
        }
      }
      else {
        $user = $account;

        drupal_session_regenerate();

        if ($type != 'ajax') {
          $message = t('You have been successfully logged in using your TREZOR device.');

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

          $message = t('You have been successfully logged in using your TREZOR device, you should now be automatically redirected, otherwise !link', $args);
        }
      }
    }
    else {
      $_SESSION['trezor_connect_response'] = $response;
      $_SESSION['trezor_connect_challenge'] = $challenge;

      $text = t('click here to register an account');
      $path = 'user/register';

      $register = l($text, $path);

      $args = array(
        '!register' => $register,
      );

      $message = t('There is no account associated with your TREZOR device.  Please login with your existing username and password to associate your account with your TREZOR device, otherwise !register.', $args);

      if ($type != 'ajax') {
        drupal_set_message($message, 'warning');

        $path = 'user/login';

        drupal_goto($path);
      }
      else {
        $redirect = FALSE;
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

      $arguments['redirect'] = $redirect;

      $variables = array(
        'message' => $message,
        'type' => 'warning',
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

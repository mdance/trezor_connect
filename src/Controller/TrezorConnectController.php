<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Controller\TrezorConnectController.
 */

namespace Drupal\trezor_connect\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\Core\Url;
use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
use Drupal\trezor_connect\Challenge\ChallengeResponseManagerInterface;
use Drupal\trezor_connect\Challenge\ChallengeValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TrezorConnectController extends ControllerBase {

  var $challenge_manager;

  var $challenge_response_manager;

  var $challenge_validator;

  /**
   * Constructs a new object.
   */
  public function __construct(ChallengeManagerInterface $challenge_manager, ChallengeResponseManagerInterface $challenge_response_manager, ChallengeValidatorInterface $challenge_validator) {
    $this->challenge_manager = $challenge_manager;
    $this->challenge_response_manager = $challenge_response_manager;
    $this->challenge_validator = $challenge_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('trezor_connect.challenge_manager'),
      $container->get('trezor_connect.challenge_response_manager'),
      $container->get('trezor_connect.challenge_validator')
    );
  }

  /**
   * Provides the page callback used to process a TREZOR connect registration
   * response.
   */
  function userRegister($js = 'nojs') {
    $output = NULL;

    $challenge_validator = $this->challenge_validator;

    $challenge_manager = $this->challenge_manager;
    $challenge = $challenge_manager->get();
    $challenge_validator->setChallenge($challenge);

    $challenge_response_manager = $this->challenge_response_manager;
    $challenge_response = $challenge_response_manager->get();
    $challenge_validator->setChallengeResponse($challenge_response);

    $result = $this->challenge_validator->validate();

    if (!$result) {
      if ($js == 'nojs') {
        throw new AccessDeniedHttpException();
      }
      else {
        $commands = array();

        $selector = '';

        if (isset($_POST['selector'])) {
          $selector = $_POST['selector'];

          // TODO: D8 check_plain
          //$selector = check_plain($selector);
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

        // TODO: D8 theme
        $message = theme('trezor_connect_message', $variables);

        $arguments['message'] = $message;

        // IMPORTANT: misc/ajax.js line 605 $element[response.method].apply($element, response.arguments);
        // requires a very specific format otherwise the $arguments will be passed as undefined
        $arguments = array(
          'callback',
          $arguments,
        );

        // TODO: D8 ajax_command_invoke
        $commands[] = ajax_command_invoke($selector, 'trezor_connect', $arguments);

        $output = array(
          '#type' => 'ajax',
          '#commands' => $commands,
        );

        // TODO: D8 ajax_deliver
        $output = ajax_deliver($output);
      }
    }
    else {
      // TODO: Finish refactoring from here....
      $result = trezor_connect_mapping($response);

      if (is_array($result) && isset($result['uid'])) {
        $text = t('please click here to login');
        $url = Url::fromRoute('user.login');

        $link = \Drupal::l($text, $url);

        $args = array(
          '@link' => $link,
        );

        $message = t('There is already an account associated with the TREZOR, @link', $args);

        if ($type != 'ajax') {
          drupal_set_message($message, 'warning');
        }
      }
      else {
        $_SESSION['trezor_connect_response'] = $response;

        $message = t('Your TREZOR device authentication has been saved to your session, please complete the registration process to associate your TREZOR device with your account.');

        if ($type != 'ajax') {
          drupal_set_message($message);
        }
      }

      if ($type != 'ajax') {
        $path = 'user.register';


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

  /**
   * Provides the page callback used to process a TREZOR connect login response.
   */
  public function userLogin($js = 'nojs') {
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

  /**
   * Provides the page callback used to process a TREZOR connect manage response.
   */
  public function userManage($account, $type = 'nojs') {
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
        $message = t('There is already an account associated with the TREZOR device.');

        drupal_set_message($message, 'warning');
      }
      else {
        $response['uid'] = $account->uid;

        trezor_connect_write_map($response);

        $message = t('Your TREZOR device has been associated to your account.  You should now be able to login with just your TREZOR device.');

        drupal_set_message($message);
      }

      $path = str_replace('%user', $account->uid, TREZOR_CONNECT_URL_MANAGE);

      if ($type != 'ajax') {
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

        $arguments['redirect'] = TRUE;

        $variables = array(
          'message' => $message,
        );

        $message = theme('trezor_connect_message', $variables);

        $arguments['message'] = $message;

        $arguments['url'] = '/' . $path;

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
}

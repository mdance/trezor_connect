<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Controller\TrezorConnectController.
 */

namespace Drupal\trezor_connect\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Controller\ControllerBase;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface;
use Drupal\trezor_connect\ChallengeValidator\ChallengeValidatorInterface;
use Drupal\trezor_connect\Mapping\MappingManagerInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\trezor_connect\TrezorConnectInterface;

class TrezorConnectController extends ControllerBase {

  var $challenge_manager;

  var $challenge_response_manager;

  var $challenge_validator;

  var $mapping_manager;

  /**
   * Constructs a new object.
   */
  public function __construct(ChallengeManagerInterface $challenge_manager, ChallengeResponseManagerInterface $challenge_response_manager, ChallengeValidatorInterface $challenge_validator, MappingManagerInterface $mapping_manager) {
    $this->challenge_manager = $challenge_manager;
    $this->challenge_response_manager = $challenge_response_manager;
    $this->challenge_validator = $challenge_validator;
    $this->mapping_manager = $mapping_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('trezor_connect.challenge_manager'),
      $container->get('trezor_connect.challenge_response_manager'),
      $container->get('trezor_connect.challenge_validator'),
      $container->get('trezor_connect.mapping_manager')
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
      $message = t('An error has occurred validating your TREZOR credentials.');

      if ($js == 'nojs') {
        drupal_set_message($message, 'error');

        throw new AccessDeniedHttpException();
      }
      else {
        $output = new AjaxResponse();

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

        $message = array(
          '#theme' => 'trezor_connect_message',
          '#type' => 'error',
          '#message' => $message,
        );

        $message = render($message);

        $arguments['message'] = $message;

        // IMPORTANT: misc/ajax.js line 605 $element[response.method].apply($element, response.arguments);
        // requires a very specific format otherwise the $arguments will be passed as undefined
        $arguments = array(
          'callback',
          $arguments,
        );

        $command = new InvokeCommand($selector, 'trezor_connect', $arguments);

        $output->addCommand($command);
      }
    }
    else {
      $mapping_manager = $this->mapping_manager;

      $public_key = $challenge_response->getPublicKey();

      $mappings = $mapping_manager->get($public_key);
      $total = count($mappings);

      if ($total > 0) {
        $text = t('please click here to login');
        $url = Url::fromRoute('user.login');

        $link = Link::fromTextAndUrl($text, $url);
        $link = $link->toString();

        $args = array(
          '@link' => $link,
        );

        $message = t('There is already an account associated with the TREZOR, @link', $args);

        if ($js != 'ajax') {
          drupal_set_message($message, 'warning');
        }
      }
      else {
        $challenge_response_manager->remember();

        $message = t('Your TREZOR device authentication has been saved to your session, please complete the registration process to associate your TREZOR device with your account.');

        if ($js != 'ajax') {
          drupal_set_message($message);
        }
      }

      if ($js != 'ajax') {
        $this->redirect(TrezorConnectInterface::ROUTE_LOGIN);
      }
      else {
        $output = new AjaxResponse();

        $selector = '';

        if (isset($_POST['selector'])) {
          $selector = $_POST['selector'];

          // TODO: Port to drupal 8
          //$selector = check_plain($selector);
          //$selector = '#' . $selector;
        }

        $arguments = array();

        $arguments['redirect'] = FALSE;

        // TODO: Fix trezor_connect_message twig template rendering
        $message = array(
          '#theme' => 'trezor_connect_message',
          '#type' => 'error',
          '#message' => $message,
        );

        $message = render($message);

        $arguments['message'] = $message;

        // IMPORTANT: misc/ajax.js line 605 $element[response.method].apply($element, response.arguments);
        // requires a very specific format otherwise the $arguments will be passed as undefined
        $arguments = array(
          'callback',
          $arguments,
        );

        $command = new InvokeCommand($selector, 'trezor_connect', $arguments);

        $output->addCommand($command);
      }
    }

    return $output;
  }

  /**
   * Provides the page callback used to process a TREZOR connect login response.
   */
  public function userLogin($js = 'nojs') {
    $output = NULL;

    $challenge_response = $this->challenge_response_manager->get();

    if (!$challenge_response) {
      $message = t('An error has occurred validating your TREZOR credentials.');

      if ($js == 'nojs') {
        drupal_set_message($message, 'error');

        throw new AccessDeniedHttpException();
      }

      $error = TRUE;
      $type = 'error';
    }
    else {
      $challenge_validator = $this->challenge_validator;

      $challenge_validator->setChallenge($challenge_response->getChallenge());
      $challenge_validator->setChallengeResponse($challenge_response);

      $result = $this->challenge_validator->validate();

      $redirect = FALSE;
      $redirect_url = NULL;
      $error = FALSE;
      $type = 'default';

      if (!$result) {
        $message = t('An error has occurred validating your TREZOR credentials.');

        if ($js == 'nojs') {
          drupal_set_message($message, 'error');

          throw new AccessDeniedHttpException();
        }

        $error = TRUE;
        $type = 'error';
      }
      else {
        $mapping_manager = $this->mapping_manager;

        $public_key = $challenge_response->getPublicKey();

        $mappings = $mapping_manager->get($public_key);
        $total = count($mappings);

        if (!$total) {
          $text = t('click here to register an account');
          $url = Url::fromRoute('user.register');

          $link = Link::fromTextAndUrl($text, $url);
          $link = $link->toString();

          $args = array(
            '@link' => $link,
          );

          $message = t('There is no account associated with your TREZOR device.  Please login with your existing username and password to associate your account with your TREZOR device, otherwise @link.', $args);

          $error = TRUE;
          $type = 'error';
        }
        else {
          $mapping = array_shift($mappings);

          $uid = $mapping->getUid();

          $account = User::load($uid);

          $result = $account->isBlocked();

          if ($result) {
            $message = <<<EOF
The account associated with your TREZOR device is not active.  If you have just
registered, your account may be awaiting to be approved by an administrator.
EOF;

            $message = t($message);

            $error = TRUE;
            $type = 'error';
          }
          else {
            user_login_finalize($account);

            $message = t('You have been successfully logged in using your TREZOR device.');

            if ($js == 'nojs') {
              drupal_set_message($message);

              $this->redirect(TrezorConnectInterface::ROUTE_USER);
            }
            else {
              $text = t('click here');
              $url = Url::fromRoute(TrezorConnectInterface::ROUTE_USER);

              $link = Link::fromTextAndUrl($text, $url);
              $link = $link->toString();

              $args = array(
                '@link' => $link,
              );

              $message = t('You have been successfully logged in using your TREZOR device, you should now be automatically redirected, otherwise @link', $args);

              $redirect = TRUE;
              $redirect_url = $url->toString();
            }
          }
        }
      }
    }

    if ($js == 'ajax') {
      $output = new AjaxResponse();

      $selector = '';

      if (isset($_POST['selector'])) {
        $selector = $_POST['selector'];

        // TODO: Port to drupal 8
        //$selector = check_plain($selector);
        //$selector = '#' . $selector;
      }

      $arguments = array();

      $arguments['redirect'] = $redirect;
      $arguments['redirect_url'] = $redirect_url;

      // TODO: Fix trezor_connect_message twig template rendering
      $message = array(
        '#theme' => 'trezor_connect_message',
        '#type' => $type,
        '#message' => $message,
      );

      $message = render($message);

      $arguments['message'] = $message;

      // IMPORTANT: misc/ajax.js line 605 $element[response.method].apply($element, response.arguments);
      // requires a very specific format otherwise the $arguments will be passed as undefined
      $arguments = array(
        'callback',
        $arguments,
      );

      $command = new InvokeCommand($selector, 'trezor_connect', $arguments);

      $output->addCommand($command);
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

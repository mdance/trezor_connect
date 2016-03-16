<?php

/**
 * @file
 * Contains \Drupal\user\Form\UserLoginForm.
 */

namespace Drupal\trezor_connect\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\trezor_connect\Enum\Messages;
use Drupal\user\UserAuthInterface;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Form\UserLoginForm;
use Drupal\trezor_connect\Enum\Permissions;
use Drupal\trezor_connect\TrezorConnectInterface;
use Drupal\trezor_connect\Enum\Modes;
use Drupal\trezor_connect\Enum\Routes;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\NestedArray;
use Drupal\trezor_connect\Enum\MappingStatus;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a user login form.
 */
class TrezorConnectUserLoginForm extends UserLoginForm {

  /**
   * The flood service.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The user authentication object.
   *
   * @var \Drupal\user\UserAuthInterface
   */
  protected $userAuth;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  protected $account;

  /**
   * Provides a string containing the trezor connect key name.
   *
   * @var string
   */
  protected $key = 'trezor_connect';

  /**
   * Constructs a new UserLoginForm.
   *
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood service.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\user\UserAuthInterface $user_auth
   *   The user authentication object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param Drupal\Core\Session\AccountInterface $account
   *   The current user object.
   */
  public function __construct(FloodInterface $flood, UserStorageInterface $user_storage, UserAuthInterface $user_auth, RendererInterface $renderer, AccountInterface $account) {
    $this->flood = $flood;
    $this->userStorage = $user_storage;
    $this->userAuth = $user_auth;
    $this->renderer = $renderer;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('flood'),
      $container->get('entity.manager')->getStorage('user'),
      $container->get('user.auth'),
      $container->get('renderer'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $account = $this->account;

    $form = parent::buildForm($form, $form_state);

    $access = $account->hasPermission(Permissions::LOGIN);

    if ($access) {
      $form['#validate'] = array_merge(array('::validateChallengeResponse'), $form['#validate']);

      $wrapper_id = 'wrapper-trezor-connect';

      $form['#prefix'] = '<div id="' . $wrapper_id . '">';
      $form['#suffix'] = '</div>';

      /**
       * @var Drupal\trezor_connect\TrezorConnectInterface $tc
       */
      $tc = \Drupal::service('trezor_connect');

      $mode = Modes::LOGIN;

      $text = $tc->getText($mode, $account);

      $form[$this->key] = array(
        '#type' => 'trezor_connect',
        '#weight' => 1,
        '#text' => $text,
        '#ajax_wrapper_id' => $wrapper_id,
        '#ajax_callback' => array(
          get_called_class(),
          'jsCallback',
        ),
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateChallengeResponse(array &$form, FormStateInterface $form_state) {
    if (isset($form[$this->key])) {
      $triggering_element = $form_state->getTriggeringElement();

      if ($triggering_element['#value'] == $form[$this->key]['button']['#value']) {
        $element = $form[$this->key];

        $key_exists = NULL;

        $values = $form_state->getValues();

        $element_values = NestedArray::getValue($values, $element['#parents'], $key_exists);

        if ($key_exists) {
          $error = $form_state->getError($element);

          if (is_null($error)) {
            $challenge_response = $element_values['challenge_response'];

            $public_key = $challenge_response->getPublicKey();

            $mappings = \Drupal::service('trezor_connect.mapping_manager')
              ->getFromPublicKey($public_key);

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

              $form_state->setError($element, $message);
            }
            else {
              $mapping = array_shift($mappings);

              $status = $mapping->getStatus();

              if ($status == MappingStatus::DISABLED) {
                $message = t('The mapping associated with your TREZOR device is currently disabled.  Please login to your account with your username, and password and re-enable your TREZOR device.');

                $form_state->setError($element, $message);
              }
              else {
                $uid = $mapping->getUid();

                $account = User::load($uid);

                $result = $account->isBlocked();

                if ($result) {
                  $message = <<<EOF
The account associated with your TREZOR device is not active.  If you have just
registered, your account may be waiting to be approved by an administrator.
EOF;

                  $message = t($message);

                  $form_state->setError($element, $message);
                }
                else {
                  $form_state->set('uid', $uid);
                }
              }
            }
          }
        }
      }
    }
  }

  /**
   * Overrides UserLoginForm::validateName to first check if the challenge
   * response authentication was successful.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateName(array &$form, FormStateInterface $form_state) {
    $uid = $form_state->get('uid');

    if (!$uid) {
      parent::validateName($form, $form_state);
    }
  }

  /**
   * Overrides UserLoginForm::validateAuthentication to first check if the
   * challenge response authentication was successful.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateAuthentication(array &$form, FormStateInterface $form_state) {
    $uid = $form_state->get('uid');

    if (!$uid) {
      parent::validateAuthentication($form, $form_state);
    }
  }

  public function validateFinal(array &$form, FormStateInterface $form_state) {
    $flood_config = $this->config('user.flood');
    if (!$form_state->get('uid')) {
      $name = 'name';

      if (isset($form[$this->key])) {
        $triggering_element = $form_state->getTriggeringElement();

        if ($triggering_element['#value'] == $form[$this->key]['button']['#value']) {
          $name = $this->key;
        }
      }

      // Always register an IP-based failed login event.
      $this->flood->register('user.failed_login_ip', $flood_config->get('ip_window'));

      // Register a per-user failed login event.
      if ($flood_control_user_identifier = $form_state->get('flood_control_user_identifier')) {
        $this->flood->register('user.failed_login_user', $flood_config->get('user_window'), $flood_control_user_identifier);
      }

      if ($flood_control_triggered = $form_state->get('flood_control_triggered')) {
        if ($flood_control_triggered == 'user') {
          $form_state->setErrorByName($name, $this->formatPlural($flood_config->get('user_limit'), 'There has been more than one failed login attempt for this account. It is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', 'There have been more than @count failed login attempts for this account. It is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', array(':url' => $this->url('user.pass'))));
        }
        else {
          // We did not find a uid, so the limit is IP-based.
          $form_state->setErrorByName($name, $this->t('Too many failed login attempts from your IP address. This IP address is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', array(':url' => $this->url('user.pass'))));
        }
      }
      else {
        $log = 'Login attempt failed from %ip';

        $context = array();

        $context['%ip'] = $this->getRequest()->getClientIp();

        if ($name == 'name') {
          $value = $form_state->getValue('name');

          $options = array(
            'query' => array(
              'name' => $value,
            ),
          );

          $password = $this->url('user.pass', [], $options);

          $args = array(
            ':password' => $password,
          );

          $message = $this->t('Unrecognized username or password. <a href=":password">Have you forgotten your password?</a>', $args);

          $values = array();

          $values['name'] = $name;

          $accounts = $this->userStorage->loadByProperties($values);

          if (!empty($accounts)) {
            $log = 'Login attempt failed for %user';

            $context['%user'] = $value;
          }
        }
        else {
          $message = $this->t(Messages::CHALLENGE_RESPONSE_INVALID);
        }

        $form_state->setErrorByName($name, $message);

        $this->logger('user')->notice($log, $context);
      }
    }
    elseif ($flood_control_user_identifier = $form_state->get('flood_control_user_identifier')) {
      // Clear past failures for this user so as not to block a user who might
      // log in and out more than once in an hour.
      $this->flood->clear('user.failed_login_user', $flood_control_user_identifier);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

  public static function jsCallback(array &$form, FormStateInterface $form_state, Request $request) {
    $uid = $form_state->get('uid');

    if (!$uid) {
      return $form;
    }
    else {
      $output = new AjaxResponse();

      if (!$request->query->has('destination')) {
        $route_name = 'entity.user.canonical';

        $route_parameters['user'] = $uid;

        $url = Url::fromRoute($route_name, $route_parameters)->toString();
      }
      else {
        $url = $request->query->get('destination');
      }

      $command = new RedirectCommand($url);

      $output->addCommand($command);

      return $output;
    }
  }
}

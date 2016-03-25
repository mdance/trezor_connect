<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Form\ManageForm.
 *
 * TODO: Fix ajax rebinding on validation error
 */

namespace Drupal\trezor_connect\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface;
use Drupal\trezor_connect\Enum\Messages;
use Drupal\trezor_connect\Mapping\MappingManagerInterface;
use Drupal\views\Plugin\views\style\Mapping;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\trezor_connect\Enum\Permissions;
use Drupal\trezor_connect\Enum\Routes;
use Drupal\trezor_connect\Enum\MappingStatus;
use Drupal\trezor_connect\Enum\Modes;
use Drupal\trezor_connect\TrezorConnectInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows you to manage the authenticated devices associated with your account.
 */
class ManageForm extends ConfirmFormBase {

  /**
   * Provides the form id.
   */
  const FORM_ID = 'trezor_connect_manage';

  /**
   * Provides the password flood service event name.
   */
  const FLOOD_NAME = 'trezor_connect_manage_password';

  /**
   * Provides the challenge response flood service event name.
   */
  const FLOOD_NAME_CHALLENGE_RESPONSE = 'trezor_connect_manage_challenge_response';

  /**
   * Provides the challenge response manager service.
   *
   * @var \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface
   */
  protected $challenge_response_manager;

  /**
   * Provides the mapping manager service.
   *
   * @var \Drupal\trezor_connect\Mapping\MappingManagerInterface
   */
  protected $mapping_manager;

  /**
   * Provides the date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $date_formatter;

  /**
   * The password service.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $password_checker;

  /**
   * The flood service.
   *
   * @var \Drupal\user\UserAuthInterface
   */
  protected $flood;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $current_user;

  /**
   * Provides the trezor connect service.
   *
   * @var \Drupal\trezor_connect\TrezorConnectInterface
   */
  protected $trezor_connect;

  /**
   * Provides the form mode.
   *
   * @var
   */
  protected $mode;

  /**
   * The account being affected.
   * @var
   */
  protected $user;

  /**
   * Constructs a new form.
   */
  public function __construct(ChallengeResponseManagerInterface $challenge_response_manager, MappingManagerInterface $mapping_manager, DateFormatterInterface $date_formatter, PasswordInterface $password_checker, FloodInterface $flood, AccountInterface $current_user, TrezorConnectInterface $trezor_connect) {
    $this->challenge_response_manager = $challenge_response_manager;
    $this->mapping_manager = $mapping_manager;
    $this->date_formatter = $date_formatter;
    $this->password_checker = $password_checker;
    $this->flood = $flood;
    $this->current_user = $current_user;
    $this->trezor_connect = $trezor_connect;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('trezor_connect.challenge_response_manager'),
      $container->get('trezor_connect.mapping_manager'),
      $container->get('date.formatter'),
      $container->get('password'),
      $container->get('flood'),
      $container->get('current_user'),
      $container->get('trezor_connect')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return static::FORM_ID;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $uid = $this->user->id();
    $current_uid = $this->current_user->id();

    if ($uid == $current_uid) {
      $target = $this->t('your account');
    }
    else {
      $target = $this->user->getAccountName();
    }

    $action = NULL;

    if ($this->mode == Modes::MANAGE_CONFIRM_DISABLE) {
      $action = $this->t('disable');
    }
    else if ($this->mode == Modes::MANAGE_CONFIRM_REMOVE) {
      $action = $this->t('remove');
    }

    $args = array();

    $args['%action'] = $action;
    $args['%target'] = $target;

    $output = $this->t('Are you sure you want to %action the authentication device for %target?', $args);

    return $output;
  }

  public function getDescription() {
    $uid = $this->user->id();
    $current_uid = $this->current_user->id();

    if ($uid == $current_uid) {
      $target = $this->t('you');
    }
    else {
      $target = $this->user->getAccountName();
    }

    $args = array();

    $args['%target'] = $target;

    $output = $this->t('By proceeding %target will no longer be able to login using the authentication device.', $args);

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Confirm');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    // buildConfirmForm line 475 converts the link to a input type submit to
    // support AJAX and a form rebuild
    $route_parameters = array();

    $route_parameters['user'] = $this->user->id();

    $output = new Url(Routes::MANAGE, $route_parameters);

    return $output;
  }

  /**
   * Provides the manage mappings form.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = NULL) {
    $mode = $form_state->get('mode');

    $wrapper_id = 'wrapper-trezor-connect-manage';

    if (!$mode) {
      $form = $this->buildManageForm($form, $form_state, $wrapper_id, $user);
    }
    else {
      $form = $this->buildConfirmForm($form, $form_state, $wrapper_id, $user);
    }

    $form['#prefix'] = '<div id="' . $wrapper_id . '">';
    $form['#suffix'] = '</div>';

    return $form;
  }

  public function buildManageForm(array $form, FormStateInterface $form_state, $wrapper_id, AccountInterface $user = NULL) {
    $uid = $user->id();

    $current_user = $this->current_user;
    $current_uid = $current_user->id();

    $admin = $current_user->hasPermission(Permissions::ACCOUNTS);
    $bypass = $current_user->hasPermission(Permissions::BYPASS);
    $view = $current_user->hasPermission(Permissions::VIEW);
    $toggle = $current_user->hasPermission(Permissions::DISABLE);
    $remove = $current_user->hasPermission(Permissions::REMOVE);

    $name = self::FLOOD_NAME;
    $threshold = $this->trezor_connect->getFloodThreshold();
    $window = $this->trezor_connect->getFloodWindow();
    $identifier = $current_uid . ':' . $uid;

    $flood = $this->flood->isAllowed($name, $threshold, $window, $identifier);

    if (!$flood) {
      $toggle = FALSE;
      $remove = FALSE;

      $message = $this->t('The authentication device actions have been temporarily disabled because you have failed to authenticate too many times.');

      drupal_set_message($message, 'warning');
    }

    $uid = (int) $user->id();

    $mappings = $this->mapping_manager->getFromUid($uid);
    $total = count($mappings);

    if (!$total) {
      $text = $this->trezor_connect->getText(Modes::MANAGE, $user);

      $route_parameters = array(
        'js' => 'nojs',
        'user' => $uid,
      );

      $options = array(
        'absolute' => TRUE,
      );

      $url = Url::fromRoute(Routes::MANAGE, $route_parameters, $options);
      $url = $url->toString();

      if ($admin || $bypass) {
        $password = FALSE;
      }
      else {
        $password = TRUE;
      }

      $form['trezor_connect'] = array(
        '#type' => 'trezor_connect',
        '#weight' => 1,
        '#text' => $text,
        '#account' => $user,
        '#url' => $url,
        '#password' => $password,
        '#create_ajax_wrapper' => FALSE,
        '#ajax_wrapper_id' => $wrapper_id,
      );
    }
    else {
      $access = TRUE;

      $admin = $current_user->hasPermission(Permissions::ACCOUNTS);
      $bypass = $current_user->hasPermission(Permissions::BYPASS);

      if ($admin || $bypass) {
        $access = FALSE;
      }

      $form['password'] = array(
        '#type' => 'password',
        '#required' => TRUE,
        '#title' => t('Current password'),
        '#access' => $access,
      );

      $header = array(
        t('Created On'),
        t('Status'),
      );

      $status = NULL;

      $rows = array();

      foreach ($mappings as $mapping) {
        $row = array();

        $created = $mapping->getCreated();

        $row[] = $this->date_formatter->format($created);

        $status = $mapping->getStatus();

        if ($status == MappingStatus::ACTIVE) {
          $status_display = t('Active');
        }
        else {
          $status_display = t('Disabled');
        }

        $row[] = $status_display;

        $rows[] = $row;

        $form['mapping'] = array(
          '#type' => 'value',
          '#value' => $mapping,
        );
      }

      $form['table'] = array(
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#access' => $view,
      );

      if ($status == MappingStatus::ACTIVE) {
        $value = t('Disable Authentication Device');
      }
      else {
        $value = t('Enable Authentication Device');
      }

      $ajax = array(
        'wrapper' => $wrapper_id,
      );

      $form['toggle'] = array(
        '#type' => 'submit',
        '#name' => 'toggle',
        '#value' => $value,
        '#access' => $toggle,
        '#status' => $status,
        '#ajax' => $ajax,
      );

      $form['remove'] = array(
        '#type' => 'submit',
        '#name' => 'remove',
        '#value' => t('Remove Authentication Device'),
        '#access' => $remove,
        '#ajax' => $ajax,
      );

      $form['user'] = array(
        '#type' => 'value',
        '#value' => $user,
      );
    }

    return $form;
  }

  public function buildConfirmForm(array $form, FormStateInterface $form_state, $wrapper_id, AccountInterface $user = NULL) {
    $this->user = $user;

    $mode = $form_state->get('mode');

    $this->mode = $mode;

    $form = parent::buildForm($form, $form_state);

    $ajax = array(
      'wrapper' => $wrapper_id,
    );

    $form['actions']['submit']['#button_type'] = 'danger';
    $form['actions']['submit']['#ajax'] = $ajax;

    // Convert the cancel link into a button to support AJAX
    $form['actions']['cancel'] = array(
      '#type' => 'submit',
      '#value' => $form['actions']['cancel']['#title'],
      '#button_type' => 'primary',
      '#ajax' => $ajax,
    );

    $form['user'] = array(
      '#type' => 'value',
      '#value' => $user,
    );

    $form['mode'] = array(
      '#type' => 'value',
      '#value' => $mode,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $mode = $form_state->get('mode');

    if (is_null($mode)) {
      // Manage/Listing Mode
      $this->validatePassword($form, $form_state);
      $this->validateChallengeResponse($form, $form_state);

      $errors = $form_state->getErrors();

      if (!$errors) {
        if (isset($form['toggle']) && isset($form['remove'])) {
          // If there were no errors, and the toggle/remove button triggered
          // the submission, put the form in confirmation mode
          $triggering_element = $form_state->getTriggeringElement();

          $rebuild = TRUE;

          if ($triggering_element['#name'] == $form['toggle']['#name']) {
            if ($triggering_element['#status'] == MappingStatus::DISABLED) {
              $form_state->set('mode', Modes::MANAGE_ENABLE);

              $rebuild = FALSE;
            }
            else {
              $form_state->set('mode', Modes::MANAGE_CONFIRM_DISABLE);
            }
          }
          else if ($triggering_element['#name'] == $form['remove']['#name']) {
            $form_state->set('mode', Modes::MANAGE_CONFIRM_REMOVE);
          }

          $user = $form_state->getValue('user');

          $form_state->set('user', $user);

          $form_state->setRebuild($rebuild);
        }
      }
    }
  }

  public function validatePassword(array &$form, FormStateInterface $form_state) {
    $current_user = $this->current_user;

    $current_uid = $current_user->id();
    $current_username = $current_user->getAccountName();

    if (isset($form['trezor_connect']['#account'])) {
      // Mapping mode
      $user = $form['trezor_connect']['#account'];
    }
    else {
      // Listing mode
      $user = $form_state->getValue('user');
    }

    $uid = $user->id();
    $username = $user->getAccountName();

    $admin = $current_user->hasPermission(Permissions::ACCOUNTS);
    $bypass = $current_user->hasPermission(Permissions::BYPASS);

    $error = NULL;

    if (!$admin && !$bypass) {
      $key = 'password';

      if (!isset($form['trezor_connect'])) {
        // If in manage mode, the password check is not handled by the
        // trezor_connect element
        $password = $form_state->getValue($key);
        $password = trim($password);

        if (empty($password)) {
          $message = t(Messages::PASSWORD_EMPTY);

          $form_state->setError($form[$key], $message);
        }
        else {
          $hash = $user->getPassword();

          $result = \Drupal::service('password')->check($password, $hash);

          if (!$result) {
            $message = t(Messages::PASSWORD_INVALID);

            $form_state->setError($form[$key], $message);
          }
        }

        $error = $form_state->getError($form[$key]);
      }
      else {
        $error = $form_state->getError($form['trezor_connect'][$key]);
      }

      $name = self::FLOOD_NAME;
      $identifier = $current_uid . ':' . $uid;

      $channel = 'trezor_connect';

      $threshold = $this->trezor_connect->getFloodThreshold();
      $window = $this->trezor_connect->getFloodWindow();

      if (is_null($error)) {
        $this->flood->clear($name, $identifier);
      }
      else {
        $result = $this->flood->isAllowed($name, $threshold, $window, $identifier);

        if (!$result) {
          $message = $this->t(Messages::PASSWORD_MAX_ATTEMPTS);

          $form_state->setError($form['trezor_connect'][$key], $message);

          $context = array();

          $context['%username'] = $username;

          if ($current_uid != $uid) {
            $message = 'The authentication attempt limit has been reached for %current_username attempting to authenticate for %username.';

            $context['%current_username'] = $current_username;
          }
          else {
            $message = 'The authentication attempt limit has been reached for %username.';
          }

          $this->logger($channel)->notice($message, $context);
        }
        else {
          $this->flood->register($name, $window, $identifier);

          $context = array();

          $context['%username'] = $username;

          if ($current_uid != $uid) {
            $message = 'Invalid password attempt by %current_username attempting to authenticate for %username.';

            $context['%current_username'] = $current_username;
          }
          else {
            $message = 'Invalid password attempt by %username.';
          }

          $this->logger($channel)->notice($message, $context);
        }
      }
    }
  }

  public function validateChallengeResponse(array &$form, FormStateInterface $form_state) {
    $key = 'challenge_response';

    if (isset($form['trezor_connect'][$key])) {
      $current_user = $this->current_user;

      $current_uid = $current_user->id();
      $current_username = $current_user->getAccountName();

      $user = $form['trezor_connect']['#account'];

      $uid = $user->id();
      $username = $user->getAccountName();

      $name = self::FLOOD_NAME_CHALLENGE_RESPONSE;
      $identifier = $current_uid . ':' . $uid;

      $channel = 'trezor_connect';

      $threshold = $this->trezor_connect->getFloodThreshold();
      $window = $this->trezor_connect->getFloodWindow();

      $error = $form_state->getError($form['trezor_connect'][$key]);

      if (is_null($error)) {
        $this->flood->clear($name, $identifier);
      }
      else {
        $result = $this->flood->isAllowed($name, $threshold, $window, $identifier);

        if (!$result) {
          $message = $this->t(Messages::CHALLENGE_RESPONSE_MAX_ATTEMPTS);

          $form_state->setError($form['trezor_connect'][$key], $message);

          $context = array();

          $context['%username'] = $username;

          if ($current_uid != $uid) {
            $message = 'The authentication device attempt limit has been reached for %current_username attempting to authenticate for %username.';

            $context['%current_username'] = $current_username;
          }
          else {
            $message = 'The authentication device attempt limit has been reached for %username.';
          }

          $this->logger($channel)->notice($message, $context);
        }
        else {
          $this->flood->register($name, $window, $identifier);

          $context = array();

          $context['%username'] = $username;

          if ($current_uid != $uid) {
            $message = 'Invalid authentication device attempt by %current_username attempting to authenticate for %username.';

            $context['%current_username'] = $current_username;
          }
          else {
            $message = 'Invalid authentication device attempt by %username.';
          }

          $this->logger($channel)->notice($message, $context);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $key_exists = FALSE;

    $input = NestedArray::getValue($values, array('trezor_connect'), $key_exists);

    $current_uid = $this->current_user->id();

    $mapping_manager = $this->mapping_manager;

    if ($key_exists) {
      /**
       * @var \Drupal\Core\Session\AccountInterface $user
       */
      $user = $form['trezor_connect']['#account'];

      $uid = $user->id();

      $challenge_response = $input['challenge_response'];

      if ($challenge_response instanceof ChallengeResponseInterface) {
        $challenge_response_id = $challenge_response->getId();

        if (is_null($challenge_response_id)) {
          $this->challenge_response_manager->set($challenge_response, FALSE);
        }

        $public_key = $challenge_response->getPublicKey();

        $mappings = $mapping_manager->getFromPublicKey($public_key);
        $total = count($mappings);

        if ($total > 0) {
          $message = t('There is already an account associated with the TREZOR device.');

          $type = 'warning';

          drupal_set_message($message, $type);
        }
        else {
          $mapping_manager->mapChallengeResponse($uid, $challenge_response);

          if ($current_uid == $uid) {
            $message = t('Your TREZOR device has been associated to your account.  You should now be able to login with just your TREZOR device.');
          }
          else {
            $args = array();

            $args['@username'] = $user->getAccountName();

            $message = t('The TREZOR device has been associated to the @username account.  The account should now be able to login with just their TREZOR device.', $args);
          }

          drupal_set_message($message);

          $form_state->setRebuild(TRUE);
        }
      }
    }
    else {
      $triggering_element = $form_state->getTriggeringElement();

      if ($triggering_element['#value'] != $form['actions']['cancel']['#value']) {
        // If the cancel button was not clicked, perform the confirmed action
        $mode = $form_state->getValue('mode');

        if (is_null($mode)) {
          $mode = $form_state->get('mode');
        }

        $user = $form_state->getValue('user');

        $uid = $user->id();

        if ($mode == Modes::MANAGE_ENABLE) {
          $action = $this->t('enabled');

          $this->mapping_manager->enable($uid);
        }
        else {
          if ($mode == Modes::MANAGE_CONFIRM_DISABLE) {
            $action = $this->t('disabled');

            $this->mapping_manager->disable($uid);
          }
          else {
            if ($mode == Modes::MANAGE_CONFIRM_REMOVE) {
              $action = $this->t('removed');

              $this->mapping_manager->delete($uid);
            }
          }
        }

        if ($uid == $current_uid) {
          $target = $this->t('your account');
        }
        else {
          $target = $user->getAccountName();
        }

        $args = array();

        $args['%action'] = $action;
        $args['%target'] = $target;

        $message = $this->t('The authentication device has been %action for %target.', $args);

        drupal_set_message($message);
      }

      $form_state->setRebuild(TRUE);

      $form_state->set('mode', NULL);
    }
  }

}

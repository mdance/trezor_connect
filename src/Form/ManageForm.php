<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Form\ManageForm.
 */

namespace Drupal\trezor_connect\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Password\PasswordInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\trezor_connect\Mapping\MappingInterface;
use Drupal\trezor_connect\Mapping\MappingManagerInterface;
use Drupal\trezor_connect\TrezorConnectInterface;
use Drupal\user\UserAuthInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Allows you to manage the authenticated devices associated with your account.
 */
class ManageForm extends FormBase {

  /**
   * Provides the form id.
   */
  const FORM_ID = 'trezor_connect_manage';

  /**
   * Provides the flood service event name.
   */
  const FLOOD_NAME = 'trezor_connect_manage';

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
   * Constructs a new form.
   */
  public function __construct(MappingManagerInterface $mapping_manager, DateFormatterInterface $date_formatter, PasswordInterface $password_checker, FloodInterface $flood, AccountInterface $current_user) {
    $this->mapping_manager = $mapping_manager;
    $this->date_formatter = $date_formatter;
    $this->password_checker = $password_checker;
    $this->flood = $flood;
    $this->current_user = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('trezor_connect.mapping_manager'),
      $container->get('date.formatter'),
      $container->get('password'),
      $container->get('flood'),
      $container->get('current_user')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return static::FORM_ID;
  }

  /**
   * Provides the manage mappings form.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = NULL) {
    $current_user = $this->current_user;

    $result = $current_user->hasPermission(TrezorConnectInterface::PERMISSION_ADMIN);

    if (!$result) {
      $description = $this->t('Please enter your current password to make changes to your accounts authenticated devices.');

      $form['password'] = array(
        '#type' => 'password',
        '#required' => TRUE,
        '#title' => t('Current Password'),
        '#description' => $description,
      );
    }

    $uid = (int)$user->id();

    $mappings = $this->mapping_manager->getFromUid($uid);
    $total = count($mappings);

    if (!$total) {
      $form_id = $this->getFormId();

      trezor_connect_alter_form($form, $form_state, $form_id);
    }
    else {
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

        if ($status == MappingInterface::STATUS_ACTIVE) {
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
      );

      if ($status == MappingInterface::STATUS_ACTIVE) {
        $value = t('Disable Authentication Device');
      }
      else {
        $value = t('Enable Authentication Device');
      }

      $form['toggle'] = array(
        '#type' => 'submit',
        '#name' => 'toggle',
        '#value' => $value,
      );

      $form['remove'] = array(
        '#type' => 'submit',
        '#name' => 'remove',
        '#value' => t('Remove Authentication Device'),
      );

      $form['user'] = array(
        '#type' => 'value',
        '#value' =>  $user,
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $current_user = $this->current_user;

    $result = $current_user->hasPermission(TrezorConnectInterface::PERMISSION_ADMIN);

    if (!$result) {
      $channel = 'trezor_connect';

      $current_uid = $current_user->id();
      $current_username = $current_user->getAccountName();

      $user = $form_state->getValue('user');

      $uid = $user->id();
      $username = $user->getAccountName();

      $password = $form_state->getValue('password');
      $password = trim($password);

      $config = $this->config('trezor_connect.settings');

      $name = self::FLOOD_NAME;
      $threshold = $config->get('flood_threshold');
      $window = $config->get('flood_window');
      $identifier = $current_uid . ':' . $uid;

      $result = $this->flood->isAllowed($name, $threshold, $window, $identifier);

      if (!$result) {
        $message = $this->t('You are not allowed anymore password guesses.');

        $form_state->setErrorByName('password', $message);

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
        $hash = $user->getPassword();

        $result = $this->password_checker->check($password, $hash);

        if (!$result) {
          $message = $this->t('The password you have entered is invalid.');

          $form_state->setErrorByName('password', $message);

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

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $current_uid = $this->current_user->id();

    $user = $form_state->getValue('user');

    $uid = $user->id();

    $triggering_element = $form_state->getTriggeringElement();

    $route_parameters = array(
      'user' => $uid,
    );

    if ($triggering_element['#name'] == $form['toggle']['#name']) {
      $mapping = $form_state->getValue('mapping');

      $status = $mapping->getStatus();

      if ($status == MappingInterface::STATUS_ACTIVE) {
        $route_name = TrezorConnectInterface::ROUTE_MANAGE_DISABLE;
      }
      else {
        $this->mapping_manager->enable($uid);

        if ($uid == $current_uid) {
          $target = $this->t('your account');
        }
        else {
          $target = $user->getAccountName();
        }

        $args = array();

        $args['%target'] = $target;

        $message = $this->t('The authentication device has been enabled for %target.', $args);

        drupal_set_message($message);

        $route_name = TrezorConnectInterface::ROUTE_MANAGE;
      }
    }
    else if ($triggering_element['#name'] == $form['remove']['#name']) {
      $route_name = TrezorConnectInterface::ROUTE_MANAGE_REMOVE;
    }

    $form_state->setRedirect($route_name, $route_parameters);
  }

}

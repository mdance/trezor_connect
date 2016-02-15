<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Form\RemoveForm.
 */

namespace Drupal\trezor_connect\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\trezor_connect\Mapping\MappingManagerInterface;
use Drupal\trezor_connect\TrezorConnectInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the remove authentication device confirmation form.
 */
class RemoveForm extends ConfirmFormBase {

  /**
   * Provides the mapping manager service.
   *
   * @var \Drupal\trezor_connect\Mapping\MappingManagerInterface
   */
  protected $mapping_manager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $current_user;

  /**
   * The account being affected.
   * @var
   */
  protected $user;

  /**
   * Constructs a new form.
   */
  public function __construct(MappingManagerInterface $mapping_manager, AccountInterface $current_user) {
    $this->mapping_manager = $mapping_manager;
    $this->current_user = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('trezor_connect.mapping_manager'),
      $container->get('current_user')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'trezor_connect_remove';
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

    $args = array();

    $args['%target'] = $target;

    $output = $this->t('Are you sure you want to remove the authentication device for %target?', $args);

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
    $route_parameters = array();

    $route_parameters['user'] = $this->user->id();

    $output = new Url(TrezorConnectInterface::ROUTE_MANAGE, $route_parameters);

    return $output;
  }

  /**
   * Provides the remove authentication device confirmation form.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = NULL) {
    $this->user = $user;

    $form = parent::buildForm($form, $form_state);

    $form['user'] = array(
      '#type' => 'value',
      '#value' => $user,
    );

    return $form;
  }

  /**
   * Provides the submit handler.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $current_uid = $this->current_user->id();

    $user = $form_state->getValue('user');

    $uid = $user->id();

    $this->mapping_manager->delete($uid);

    if ($uid == $current_uid) {
      $target = $this->t('your account');
    }
    else {
      $target = $this->user->getAccountName();
    }

    $args = array();

    $args['%target'] = $target;

    $message = $this->t('The authentication device has been deleted for %target.', $args);

    drupal_set_message($message);

    $route_name = TrezorConnectInterface::ROUTE_MANAGE;

    $route_parameters = array();

    $route_parameters['user'] = $uid;

    $form_state->setRedirect($route_name, $route_parameters);
  }

}

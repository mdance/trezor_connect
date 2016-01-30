<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Form\ForgetForm.
 */

namespace Drupal\trezor_connect\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;

/**
 * Allows you to manage the authenticated devices associated with your account.
 */
class ForgetForm extends ConfigFormBase {
  /**
   * The state keyvalue collection.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Provides a string containing the namespace.
   */
  protected $namespace = TREZOR_CONNECT_NS_MAPPINGS;

  /**
   * Constructs a new form.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state keyvalue collection to use.
   */
  public function __construct(ConfigFactoryInterface $config_factory, StateInterface $state) {
    parent::__construct($config_factory);

    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('state')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'trezor_connect_manage';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [$this->namespace];
  }

  /**
   * Provides the forget authenticated device form.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['account'] = array(
      '#type' => 'value',
      '#value' => $account,
    );

    $question = t('Are you sure you want to forget your authentication device?');
    $path = url('user/' . $account->uid . '/trezor-connect');
    $description = t('You will have to re-associate your authentication device to your account if you continue.');
    $yes = t('Confirm');
    $no = t('Cancel');

    $form = confirm_form($form, $question, $path, $description, $yes, $no);

    return parent::buildForm($form, $form_state);
  }

  /**
   * Provides the forget authenticated device confirmation form submit handler.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state['values'];

    $account = $values['account'];

    trezor_connect_delete_mapping($account);

    $message = t('Your authentication device has been forgotten.');

    drupal_set_message($message);

    $path = url('user/' . $account->uid . '/trezor-connect');

    $form_state['redirect'] = $path;

    parent::submitForm($form, $form_state);
  }
}

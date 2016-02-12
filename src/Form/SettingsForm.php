<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Form\SettingsForm.
 */

namespace Drupal\trezor_connect\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\trezor_connect\TrezorConnectInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides trezor connect settings.
 */
class SettingsForm extends ConfigFormBase {
  const NS = 'trezor_connect.settings';

  /**
   * The state keyvalue collection.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a new form.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state keyvalue collection to use.
   */
  public function __construct(ConfigFactoryInterface $config_factory, StateInterface $state, $challenge_backends, $challenge_backend, $challenge_response_backends, $challenge_response_backend, $mapping_backends, $mapping_backend, TrezorConnectInterface $trezor_connect) {
    parent::__construct($config_factory);

    $this->state = $state;

    $this->challenge_backends = $challenge_backends;
    $this->challenge_backend = $challenge_backend;

    $this->challenge_response_backends = $challenge_response_backends;
    $this->challenge_response_backend = $challenge_response_backend;

    $this->mapping_backends = $mapping_backends;
    $this->mapping_backend = $mapping_backend;

    $this->trezor_connect = $trezor_connect;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('state'),
      $container->getParameter('trezor_connect_challenge_backends'),
      $container->getParameter('trezor_connect_challenge_backend'),
      $container->getParameter('trezor_connect_challenge_response_backends'),
      $container->getParameter('trezor_connect_challenge_response_backend'),
      $container->getParameter('trezor_connect_mapping_backends'),
      $container->getParameter('trezor_connect_mapping_backend'),
      $container->get('trezor_connect')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'trezor_connect_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::NS];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::NS);

    $key = 'text';

    $description = t('Please specify the display text.');

    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'textfield',
      '#title' => t('Display Text'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $key = 'external';

    $description = t('Please specify whether to load the TREZOR connect javascript externally.');

    $options = array(
      TrezorConnectInterface::EXTERNAL_YES => t('Yes'),
      TrezorConnectInterface::EXTERNAL_NO => t('No'),
    );

    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'radios',
      '#title' => t('Use CDN'),
      '#description' => $description,
      '#options' => $options,
      '#default_value' => $default_value,
    );

    $key = 'url';

    $description = t('Please specify the external TREZOR connect javascript url.');
    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'textfield',
      '#title' => t('External TREZOR Connect Javascript URL'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $key = 'callback';

    $description = t('Please specify the TREZOR connect callback function.');
    $default_value = $config->get($key);

    $form[$key] = array(
      '#type' => 'textfield',
      '#title' => t('TREZOR Connect Callback'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $key = 'challenge_backend';

    $description = t('Please specify the challenge backend to use.');

    $options = $this->trezor_connect->challengeBackendOptions();

    $default_value = $this->challenge_backend;

    $result = $config->get($key);

    if ($result) {
      $default_value = $result;
    }

    $form[$key] = array(
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => t('Challenge Backend'),
      '#options' => $options,
      '#empty_value' => '',
      '#empty_option' => t('Select an option'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $key = 'challenge_response_backend';

    $description = t('Please specify the challenge response backend to use.');

    $options = $this->trezor_connect->challengeResponseBackendOptions();

    $default_value = $this->challenge_response_backend;

    $result = $config->get($key);

    if ($result) {
      $default_value = $result;
    }

    $form[$key] = array(
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => t('Challenge Response Backend'),
      '#options' => $options,
      '#empty_value' => '',
      '#empty_option' => t('Select an option'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    $key = 'mapping_backend';

    $description = t('Please specify the mapping backend to use.');

    $options = $this->trezor_connect->mappingBackendOptions();

    $default_value = $this->mapping_backend;

    $result = $config->get($key);

    if ($result) {
      $default_value = $result;
    }

    $form[$key] = array(
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => t('Mapping Backend'),
      '#options' => $options,
      '#empty_value' => '',
      '#empty_option' => t('Select an option'),
      '#description' => $description,
      '#default_value' => $default_value,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(self::NS);

    $keys = array(
      'text',
      'external',
      'url',
      'callback',
      'challenge_backend',
      'challenge_response_backend',
      'mapping_backend',
    );

    foreach ($keys as $key) {
      $config->set($key, $form_state->getValue($key));
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}

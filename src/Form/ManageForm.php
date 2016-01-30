<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Form\ManageForm.
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
class ManageForm extends ConfigFormBase {
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
   * Provides the manage mappings form.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config($this->namespace);

    $mapping = trezor_connect_account_mapping($account);

    if (!$mapping) {
      $form_id = 'trezor_connect_manage_form';

      trezor_connect_alter_form($form, $form_state, $form_id);

      $mode = 'manage';
      $url = '/' . str_replace('%user', $account->uid, TREZOR_CONNECT_URL_MANAGE_JS);

      $form['#attached']['js'][] = array(
        'data' => array(
          'trezor_connect' => array(
            'mode' => $mode,
            'url' => $url,
            'form_id' => $form_id,
          ),
        ),
        'type' => 'setting',
      );
    }
    else {
      $header = array(
        t('Created On'),
        //t('Address'),
      );

      $rows = array();

      $created = $mapping['created'];

      $row[] = format_date($created);

      //$address = $mapping['address'];
      //$address = check_plain($address);

      /*
      $path = 'http://blockchain.info/address/' . $address;

      $options = array(
        'attributes' => array(
          'target' => '_new',
        ),
      );

      $link = l($address, $path, $options);
      */

      //$link = $address;

      //$row[] = $link;

      $rows[] = $row;

      $variables = array(
        'header' => $header,
        'rows' => $rows,
      );

      $markup = theme('table', $variables);

      $form['table'] = array(
        '#markup' => $markup,
      );

      $form['forget'] = array(
        '#type' => 'submit',
        '#value' => t('Forget Authentication Device'),
      );

      $form['account'] = array(
        '#type' => 'value',
        '#value' => $account,
      );
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Provides the manage mappings form submit handler.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state['values'];

    $account = $values['account'];

    $uid = $account->uid;

    $form_state['redirect'] = 'user/' . $uid . '/trezor-connect/forget';

    parent::submitForm($form, $form_state);
  }
}

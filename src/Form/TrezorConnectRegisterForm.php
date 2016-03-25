<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Form\TrezorConnectRegisterForm.
 *
 * This class is assigned by trezor_connect_entity_type_alter().
 */

namespace Drupal\trezor_connect\Form;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\trezor_connect\Enum\Modes;
use Drupal\trezor_connect\Enum\Permissions;
use Drupal\user\RegisterForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the user register forms.
 */
class TrezorConnectRegisterForm extends RegisterForm {

  const KEY = 'trezor_connect';

  protected $account;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, QueryFactory $entity_query, AccountInterface $account) {
    parent::__construct($entity_manager, $language_manager, $entity_query);

    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('language_manager'),
      $container->get('entity.query'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $account = $this->account;

    $access = $account->hasPermission(Permissions::REGISTER);

    if ($access) {
      $wrapper_id = 'wrapper-trezor-connect';

      $form['#prefix'] = '<div id="' . $wrapper_id . '">';
      $form['#suffix'] = '</div>';

      $text = \Drupal::service('trezor_connect')->getText(Modes::REGISTER, $account);

      $form[self::KEY] = array(
        '#type' => 'trezor_connect',
        '#weight' => 1,
        '#text' => $text,
        '#ajax_wrapper_id' => $wrapper_id,
        '#ajax_callback' => array(
          get_called_class(),
          'jsCallback',
        ),
        '#set_button_limit_validation_errors' => FALSE,
        '#validate_challenge_response_mapping' => TRUE,
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $form = parent::actions($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $triggering_element = $form_state->getTriggeringElement();

    if ($triggering_element['#value'] == $form[self::KEY]['button']['#value']) {
      $this->save($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $triggering_element = $form_state->getTriggeringElement();

    if ($triggering_element['#value'] == $form[self::KEY]['button']['#value']) {
      $key = array(
        self::KEY,
        'challenge_response',
      );

      $challenge_response = $form_state->getValue($key);

      if ($challenge_response) {
        $id = $challenge_response->getId();

        if (!$id) {
          \Drupal::service('trezor_connect.challenge_response_manager')
            ->set($challenge_response);
        }

        $account = $this->getEntity($form_state);

        \Drupal::service('trezor_connect')
          ->mapChallengeResponse($account->id(), $challenge_response);
      }
    }
  }

  public static function jsCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

}

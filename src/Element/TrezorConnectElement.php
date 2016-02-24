<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Element\TrezorConnectElement.
 */

namespace Drupal\trezor_connect\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

use Drupal\trezor_connect\Form\ManageForm;
use Drupal\trezor_connect\TrezorConnectInterface;


/**
 * Provides a TREZOR Connect form element.
 *
 * Usage example:
 *
 * @code
 * $form['trezor_connect'] = array(
 *   '#type' => 'trezor_connect',
 *   '#title' => t('Sign in with TREZOR'),
 * );
 * @endcode
 *
 * @RenderElement("trezor_connect")
 */
class TrezorConnectElement extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    return array(
      '#input' => FALSE,
      '#process' => array(
        array($class, 'processAjaxForm'),
        array($class, 'processGroup'),
      ),
      '#pre_render' => array(
        array($class, 'preRender'),
        array($class, 'preRenderGroup'),
      ),
      '#theme' => 'trezor_connect',
      '#theme_wrappers' => array(
        'container',
      ),
      '#form_id' => NULL,
      '#account' => NULL,
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
  }

  /**
   * Prepares a #type 'trezor_connect' render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRender($element) {
    // TODO: Implement password check functionality
    $current_user = \Drupal::currentUser();
    $current_uid = $current_user->id();

    $account = NULL;

    if (isset($element['#account'])) {
      $account = $element['#account'];
    }

    if (!($account instanceof AccountInterface)) {
      $account = $current_user;
    }

    $uid = $account->id();

    $admin = $current_user->hasPermission(TrezorConnectInterface::PERMISSION_ACCOUNTS);
    $login = $account->hasPermission(TrezorConnectInterface::PERMISSION_LOGIN);
    $register = $account->hasPermission(TrezorConnectInterface::PERMISSION_REGISTER);
    $remove = $account->hasPermission(TrezorConnectInterface::PERMISSION_REMOVE);

    if (($uid == $current_uid && ($login || $register || $remove)) || $admin) {
      $form_id = $element['#form_id'];

      $ids = array(
        'user_login_form',
        'user_login_block',
      );

      $result = in_array($form_id, $ids);

      if ($result) {
        $mode = TrezorConnectInterface::MODE_LOGIN;
      }
      else {
        if ($form_id == ManageForm::FORM_ID) {
          $mode = TrezorConnectInterface::MODE_MANAGE;
        }
        else {
          $mode = TrezorConnectInterface::MODE_REGISTER;
        }
      }

      $route_parameters = array(
        'js' => 'nojs',
      );

      $options = array(
        'absolute' => TRUE,
      );

      if ($mode == TrezorConnectInterface::MODE_LOGIN) {
        $url = Url::fromRoute(TrezorConnectInterface::ROUTE_LOGIN, $route_parameters, $options);
      }
      else {
        if ($mode == TrezorConnectInterface::MODE_MANAGE) {
          $route_parameters['user'] = $account->id();

          $url = Url::fromRoute(TrezorConnectInterface::ROUTE_MANAGE_JS, $route_parameters, $options);
        }
        else {
          $url = Url::fromRoute(TrezorConnectInterface::ROUTE_REGISTER, $route_parameters, $options);
        }
      }

      $tc = \Drupal::service('trezor_connect');

      $element['#attached']['library'][] = 'trezor_connect/core';

      $external = $tc->getExternal();

      if ($external == TrezorConnectInterface::EXTERNAL_YES) {
        $element['#attached']['library'][] = 'trezor_connect/external';
      }
      else {
        $element['#attached']['library'][] = 'trezor_connect/local';
      }

      $url = $url->toString();

      $implementation = $tc->getImplementation();

      if (!isset($element['#implementation'])) {
        $element['#implementation'] = $implementation;
      }
      else {
        $implementation = $element['#implementation'];
      }

      $tag = $tc->getTag();

      if (!isset($element['#tag'])) {
        $element['#tag'] = $tag;
      }
      else {
        $tag = $element['#tag'];
      }

      $text = $tc->getText($mode, $account);

      if (!isset($element['#text'])) {
        $element['#text'] = $text;
      }
      else {
        $text = $element['#text'];
      }

      if (!isset($element['#text_login'])) {
        $text_login = $tc->getLoginText();

        $element['#text_login'] = $text_login;
      }

      if (!isset($element['#text_register'])) {
        $text_register = $tc->getRegistrationText();

        $element['#text_register'] = $text_register;
      }

      if (!isset($element['#text_manage'])) {
        $text_manage = $tc->getManageText();

        $element['#text_manage'] = $text_manage;
      }

      if (!isset($element['#text_manage_admin'])) {
        $text_manage_admin = $tc->getAdminManageText();

        $element['#text_manage_admin'] = $text_manage_admin;
      }

      if (!isset($element['#icon'])) {
        $icon = $tc->getIcon();

        $element['#icon'] = $icon;
      }

      if (!isset($element['#callback'])) {
        $callback = $tc->getCallback();

        $element['#callback'] = $callback;
      }

      $challenge_manager = \Drupal::service('trezor_connect.challenge_manager');
      $challenge = $challenge_manager->get();

      $element['#challenge_id'] = $challenge->getId();
      $element['#challenge_hidden'] = $challenge->getChallengeHidden();
      $element['#challenge_visual'] = $challenge->getChallengeVisual();

      Element::setAttributes($element, array(
        'implementation',
        'tag',
        'text',
        'text_login',
        'text_register',
        'text_manage',
        'text_manage_admin',
        'icon',
        'callback',
        'challenge_id',
        'challenge_hidden',
        'challenge_visual',
      ));

      $challenge_js = $challenge->toArray();

      $element['#attached']['drupalSettings']['trezor_connect']['elements'][$element['#id']] = array(
        'mode' => $mode,
        'url' => $url,
        'form_id' => $form_id,
        'id' => $element['#id'],
        'implementation' => $element['#implementation'],
        'tag' => $element['#tag'],
        'text' => $element['#text'],
        'text_login' => $element['#text_login'],
        'text_register' => $element['#text_register'],
        'text_manage' => $element['#text_manage'],
        'text_manage_admin' => $element['#text_manage_admin'],
        'icon' => $element['#icon'],
        'callback' => $element['#callback'],
        'challenge' => $challenge_js,
      );

      if ($tag != TrezorConnectInterface::TAG_TREZORLOGIN) {
        $element['text'] = array(
          '#type' => 'markup',
          '#markup' => $text,
        );
      }

      $renderer = \Drupal::service('renderer');

      $renderer->addCacheableDependency($element, $challenge);

      \Drupal::service('page_cache_kill_switch')->trigger();
    }

    return $element;
  }

}

<?php
/**
 * @file
 * Contains Drupal\trezor_connect\TrezorConnect
 */

namespace Drupal\trezor_connect;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface;
use Drupal\trezor_connect\Mapping\MappingManagerInterface;

class TrezorConnect implements TrezorConnectInterface, ContainerInjectionInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $current_user;

  /**
   * @var SessionInterface.
   */
  protected $session;

  /**
   * Provides the config factory.
   */
  protected $config_factory;

  /**
   * Provides the trezor connect config.
   */
  protected $config;

  /**
   * Provides the theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $theme_handler;

  /**
   * Provides the Challenge Manager.
   *
   * @var \Drupal\trezor_connect\ChallengeManagerInterface
   */
  protected $challenge_manager;

  /**
   * Provides the Mapping Manager.
   *
   * @var \Drupal\trezor_connect\MappingManager
   */
  protected $mapping_manager;

  protected $challenge_backends;
  protected $challenge_backend;

  protected $challenge_response_backends;
  protected $challenge_response_backend;

  protected $mapping_backends;
  protected $mapping_backend;

  /**
   * Constructs a new object.
   */
  public function __construct(AccountInterface $current_user, SessionInterface $session, ConfigFactoryInterface $config_factory, ThemeHandlerInterface $theme_handler, ChallengeManagerInterface $challenge_manager, ChallengeResponseManagerInterface $challenge_response_manager, MappingManagerInterface $mapping_manager, $challenge_backends, $challenge_backend, $challenge_response_backends, $challenge_response_backend, $mapping_backends, $mapping_backend) {
    $this->current_user = $current_user;
    $this->session = $session;
    $this->config_factory = $config_factory;

    $this->config = $config_factory->get('trezor_connect.settings');

    $this->theme_handler = $theme_handler;

    $this->challenge_manager = $challenge_manager;
    $this->challenge_response_manager = $challenge_response_manager;
    $this->mapping_manager = $mapping_manager;

    $this->challenge_backends = $challenge_backends;
    $this->challenge_backend = $challenge_backend;

    $this->challenge_response_backends = $challenge_response_backends;
    $this->challenge_response_backend = $challenge_response_backend;

    $this->mapping_backends = $mapping_backends;
    $this->mapping_backend = $mapping_backend;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('session'),
      $container->get('config.factory'),
      $container->get('theme_handler'),
      $container->get('trezor_connect.challenge_manager'),
      $container->get('trezor_connect.challenge_response_manager'),
      $container->get('trezor_connect.mapping_manager'),
      $container->getParameter('trezor_connect_challenge_backends'),
      $container->getParameter('trezor_connect_challenge_backend'),
      $container->getParameter('trezor_connect_challenge_response_backends'),
      $container->getParameter('trezor_connect_challenge_response_backend'),
      $container->getParameter('trezor_connect_mapping_backends'),
      $container->getParameter('trezor_connect_mapping_backend')
    );
  }

  /**
   * @inheritdoc
   */
  public function getText($mode = TrezorConnectInterface::MODE_LOGIN, AccountInterface $account = NULL) {
    $key = 'text';

    if ($mode == TrezorConnectInterface::MODE_REGISTER) {
      $key .= '_register';
    }
    else if ($mode == TrezorConnectInterface::MODE_MANAGE) {
      $key .= '_manage';

      if (is_null($account)) {
        $account = $this->current_user;
      }

      $current_uid = $this->current_user->id();
      $uid = $account->id();

      $admin = $this->current_user->hasPermission(TrezorConnectInterface::PERMISSION_ADMIN);

      if ($admin && $current_uid != $uid) {
        $key .= '_admin';
      }
    }

    $output = $this->config->get($key);

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function getLoginText() {
    $output = $this->config->get('text');

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function getRegistrationText() {
    $output = $this->config->get('text_register');

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function getManageText(AccountInterface $account = NULL) {
    $output = $this->config->get('text_manage');

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function getAdminManageText(AccountInterface $account = NULL) {
    $output = $this->config->get('text_manage_admin');

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getIcon() {
    $output = NULL;

    $source = $this->config->get('icon.source');

    if ($source == TrezorConnectInterface::ICON_SOURCE_THEME) {
      $theme = $this->theme_handler->getDefault();

      $logo = theme_get_setting('logo', $theme);

      if (isset($logo['url'])) {
        $output = $logo['url'];
      }
    }
    else if ($source == TrezorConnectInterface::ICON_SOURCE_CUSTOM) {
      $path = $this->config->get('icon.path');

      $output = file_create_url($path);
    }

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function getExternal() {
    return $this->config->get('external');
  }

  /**
   * @inheritdoc
   */
  public function getUrl() {
    return $this->config->get('url');
  }

  /**
   * @inheritdoc
   */
  public function getCallback() {
    return $this->config->get('callback');
  }

  /**
   * @inheritdoc
   */
  public function getFloodThreshold() {
    return $this->config->get('flood_threshold');
  }

  /**
   * @inheritdoc
   */
  public function getFloodWindow() {
    return $this->config->get('flood_window');
  }

  /**
   * @inheritDoc
   */
  public function getChallengeOffset() {
    return $this->config->get('challenge_offset');
  }

  /**
   * @inheritDoc
   */
  public function getChallengeResponseOffset() {
    return $this->config->get('challenge_response_offset');
  }

  /**
   * @inheritdoc
   */
  public function challengeBackendOptions() {
    $output = array();

    foreach ($this->challenge_backends as $id => $value) {
      $output[$id] = $value['title'];
    }

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function getChallengeBackend() {
    return $this->config->get('trezor_connect_challenge_backend');
  }

  /**
   * @inheritdoc
   */
  public function challengeResponseBackendOptions() {
    $output = array();

    foreach ($this->challenge_response_backends as $id => $value) {
      $output[$id] = $value['title'];
    }

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function getChallengeResponseBackend() {
    return $this->config->get('trezor_connect_challenge_response_backend');
  }

  /**
   * @inheritdoc
   */
  public function mappingBackendOptions() {
    $output = array();

    foreach ($this->mapping_backends as $id => $value) {
      $output[$id] = $value['title'];
    }

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function getMappingBackend() {
    return $this->config->get('trezor_connect_mapping_backend');
  }

  /**
   * @inheritdoc
   */
  public function mapChallengeResponse($uid) {
    $this->mapping_manager->mapChallengeResponse($uid);
  }

  /**
   * @inheritDoc
   */
  public function checkChallengeResponseState($uid) {
    $output = TrezorConnectInterface::STATE_CHALLENGE_RESPONSE_NOT_FOUND;

    $challenge_response = $this->challenge_response_manager->get();

    if ($challenge_response) {
      $public_key = $challenge_response->getPublicKey();

      $mappings = $this->mapping_manager->getFromPublicKey($public_key);
      $total = count($mappings);

      if (!$total) {
        $output = TrezorConnectInterface::STATE_CHALLENGE_RESPONSE_NEW;

        $mappings = $this->mapping_manager->getFromUid($uid);
        $total = count($mappings);

        if ($total) {
          $output = TrezorConnectInterface::STATE_CHALLENGE_RESPONSE_UPDATE;
        }
      }
      else {
        $mapping = array_shift($mappings);

        $mapping_uid = $mapping->getUid();

        if ($uid != $mapping_uid) {
          $output = TrezorConnectInterface::STATE_CHALLENGE_RESPONSE_OTHER_ACCOUNT;
        }
        else {
          $output = TrezorConnectInterface::STATE_CHALLENGE_RESPONSE_EXISTS;
        }
      }
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function deleteMapping($uid) {
    $this->mapping_manager->delete($uid);
  }

  /**
   * @inheritDoc
   */
  public function deleteExpiredChallengeResponses() {
    $this->challenge_response_manager->deleteExpired($this->mapping_manager);
  }

  /**
   * @inheritDoc
   */
  public function deleteExpiredChallenges() {
    $this->challenge_manager->deleteExpired($this->challenge_response_manager);
  }

}

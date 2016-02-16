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
  public function mapChallengeResponse($uid) {
    $this->mapping_manager->mapChallengeResponse($uid);
  }

  /**
   * @inheritDoc
   */
  public function deleteMapping($uid) {
    $this->mapping_manager->delete($uid);
  }

}

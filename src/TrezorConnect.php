<?php
/**
 * @file
 * Contains Drupal\trezor_connect\TrezorConnect
 */
namespace Drupal\trezor_connect;

use Drupal\trezor_connect\Mapping\Mapping;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountProxyInterface;

use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
use Drupal\trezor_connect\Challenge\ChallengeResponseManagerInterface;
use Drupal\trezor_connect\Mapping\MappingManagerInterface;

class TrezorConnect implements TrezorConnectInterface, ContainerInjectionInterface {

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

  /**
   * Constructs a new object.
   */
  public function __construct(SessionInterface $session, ConfigFactoryInterface $config_factory, ChallengeManagerInterface $challenge_manager, ChallengeResponseManagerInterface $challenge_response_manager, MappingManagerInterface $mapping_manager, $mapping_backends, $mapping_backend) {
    $this->session = $session;
    $this->config_factory = $config_factory;

    $this->config = $config_factory->get('trezor_connect.settings');

    $this->challenge_manager = $challenge_manager;
    $this->challenge_response_manager = $challenge_response_manager;
    $this->mapping_manager = $mapping_manager;

    $this->mapping_backends = $mapping_backends;
    $this->mapping_backend = $mapping_backend;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('session'),
      $container->get('config.factory'),
      $container->get('trezor_connect.challenge_manager'),
      $container->get('trezor_connect.challenge_response_manager'),
      $container->get('trezor_connect.mapping_manager'),
      $container->getParameter('trezor_connect_mapping_backends'),
      $container->getParameter('trezor_connect_mapping_backend')
    );
  }

  /**
   * @inheritdoc
   */
  public function getText() {
    return $this->config->get('text');
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

}

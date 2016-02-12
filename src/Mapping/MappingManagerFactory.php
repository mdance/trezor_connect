<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Mapping\MappingManagerFactory.
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Drupal\Core\Site\Settings;
use Symfony\Component\Process\Exception\LogicException;

class MappingManagerFactory implements MappingManagerFactoryInterface, ContainerAwareInterface {

  use ContainerAwareTrait;

  /**
   * The settings array.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * The module config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * An array of backends.
   *
   * This is set through the constructor by:
   * \Drupal\trezor_connect\Compiler\MappingBackendsPass
   *
   * MappingBackendsPass is registered by TrezorConnectServiceProvider.
   *
   * @var array
   */
  protected $backends;

  /**
   * A string containing the default backend as specified by the services
   * configuration.
   *
   * This can be overridden by the configuration, and settings subsystems.
   *
   * @var string
   */
  protected $backend;

  /**
   * Provides the challenge manager.
   *
   * @var
   */
  protected $challenge_manager;

  /**
   * Provides the challenge response manager.
   *
   * @var
   */
  protected $challenge_response_manager;

  /**
   * Constructs a new object.
   */
  public function __construct(Settings $settings, ConfigFactoryInterface $config_factory, array $backends = array(), $backend, ChallengeManagerInterface $challenge_manager, ChallengeResponseManagerInterface $challenge_response_manager) {
    $this->settings = $settings;
    $this->config_factory = $config_factory;
    $this->config = $config_factory->get('trezor_connect.settings');

    $this->backends = $backends;
    $this->backend = $backend;

    $this->challenge_manager = $challenge_manager;
    $this->challenge_response_manager = $challenge_response_manager;
  }

  /**
   * Instantiates a backend class.
   *
   * @return \Drupal\trezor_connect\MappingBackendInterface
   *   The backend object.
   */
  public function get() {
    $service = $this->backend;

    // The config subsystem takes precedence over the services configuration
    $result = $this->config->get('mapping_backend');

    if ($result) {
      $service = $result;
    }

    // The settings subsystem takes precedence over the config subsystem
    $result = $this->settings->get('trezor_connect_mapping_backend');

    if ($result) {
      $service = $result;
    }

    if (!isset($this->backends[$service])) {
      $message = sprintf('The TREZOR Connect mapping manager cannot be instantiated because the mapping backend service does not exist: %s', $service);

      throw new LogicException($message);
    }
    else {
      $backend = $this->container->get($service);

      $output = new MappingManager($this->config_factory);

      $output->setBackend($backend);
      $output->setChallengeManager($this->challenge_manager);
      $output->setChallengeResponseManager($this->challenge_response_manager);

      return $output;
    }
  }

}

<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Challenge\ChallengeManagerFactory.
 */

namespace Drupal\trezor_connect\Challenge;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

use Drupal\trezor_connect\TrezorConnectInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Process\Exception\LogicException;

class ChallengeManagerFactory implements ChallengeManagerFactoryInterface, ContainerAwareInterface {

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
   * The current request.
   *
   * @var
   */
  protected $request;

  /**
   * The session service.
   *
   * @var
   */
  protected $session;

  /**
   * An array of backends.
   *
   * This is set through the constructor by:
   * \Drupal\trezor_connect\Compiler\ChallengeBackendsPass
   *
   * ChallengeBackendsPass is registered by TrezorConnectServiceProvider.
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
   * Provides the challenge service.
   *
   * @var
   */
  protected $challenge;

  /**
   * Provides the cache tags invalidator service.
   *
   * @var
   */
  protected $cache_tags_invalidator;

  /**
   * Constructs a new object.
   */
  public function __construct(Settings $settings, ConfigFactoryInterface $config_factory, RequestStack $request_stack, SessionInterface $session, array $backends = array(), $backend, ChallengeInterface $challenge, CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->settings = $settings;
    $this->config = $config_factory->get(TrezorConnectInterface::CONFIG_NS);
    $this->request = $request_stack->getCurrentRequest();
    $this->session = $session;

    $this->backends = $backends;
    $this->backend = $backend;

    $this->challenge = $challenge;

    $this->cache_tags_invalidator = $cache_tags_invalidator;
  }

  /**
   * Instantiates a backend class.
   *
   * @return \Drupal\trezor_connect\Challenge\ChallengeBackendInterface
   *   The backend object.
   */
  public function get() {
    $service = $this->backend;

    // The config subsystem takes precedence over the services configuration
    $result = $this->config->get('challenge_backend');

    if ($result) {
      $service = $result;
    }

    // The settings subsystem takes precedence over the config subsystem
    $result = $this->settings->get('trezor_connect_challenge_backend');

    if ($result) {
      $service = $result;
    }

    if (!isset($this->backends[$service])) {
      $message = sprintf('The TREZOR Connect challenge manager cannot be instantiated because the challenge backend service does not exist: %s', $service);

      throw new LogicException($message);
    }
    else {
      $backend = $this->container->get($service);

      // TODO: Refactor this so its not hardcoded
      $output = new ChallengeManager();

      $output->setRequest($this->request);
      $output->setSession($this->session);
      $output->setBackend($backend);
      $output->setChallenge($this->challenge);
      $output->setCacheTagsInvalidator($this->cache_tags_invalidator);

      return $output;
    }
  }

}

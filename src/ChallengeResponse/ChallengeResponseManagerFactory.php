<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerFactory.
 */

namespace Drupal\trezor_connect\ChallengeResponse;

use Drupal\Core\Config\ConfigFactoryInterface;

use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Process\Exception\LogicException;

class ChallengeResponseManagerFactory implements ChallengeResponseManagerFactoryInterface, ContainerAwareInterface {

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
   * \Drupal\trezor_connect\Compiler\ChallengeResponseBackendsPass
   *
   * ChallengeResponseBackendsPass is registered by TrezorConnectServiceProvider.
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
   * Provides the challenge response service.
   *
   * @var
   */
  protected $challenge_response;

  /**
   * Provides the challenge manager service.
   *
   * @var
   */
  protected $challenge_manager;

  /**
   * Constructs a new object.
   */
  public function __construct(Settings $settings, ConfigFactoryInterface $config_factory, SessionInterface $session, RequestStack $request_stack, array $backends = array(), $backend, ChallengeResponseInterface $challenge_response, ChallengeManagerInterface $challenge_manager) {
    $this->settings = $settings;
    $this->config = $config_factory->get('trezor_connect.settings');
    $this->request = $request_stack->getCurrentRequest();
    $this->session = $session;

    $this->backends = $backends;
    $this->backend = $backend;

    $this->challenge_response = $challenge_response;
    $this->challenge_manager = $challenge_manager;
  }

  /**
   * Instantiates a backend class.
   *
   * @return \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface
   *   The backend object.
   */
  public function get() {
    $service = $this->backend;

    // The config subsystem takes precedence over the services configuration
    $result = $this->config->get('challenge_response_backend');

    if ($result) {
      $service = $result;
    }

    // The settings subsystem takes precedence over the config subsystem
    $result = $this->settings->get('trezor_connect_challenge_response_backend');

    if ($result) {
      $service = $result;
    }

    if (!isset($this->backends[$service])) {
      $message = sprintf('The TREZOR Connect challenge response manager cannot be instantiated because the challenge response backend service does not exist: %s', $service);

      throw new LogicException($message);
    }
    else {
      $backend = $this->container->get($service);

      // TODO: Refactor this so its not hardcoded
      $output = new ChallengeResponseManager();

      $output->setRequest($this->request);
      $output->setSession($this->session);
      $output->setBackend($backend);
      $output->setChallengeResponse($this->challenge_response);
      $output->setChallengeResponseOffset($this->config->get('challenge_response_offset'));
      $output->setChallengeManager($this->challenge_manager);

      return $output;
    }
  }

}

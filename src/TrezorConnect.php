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
use Drupal\trezor_connect\Challenge\ChallengeResponseInterface;
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
  public function __construct(SessionInterface $session, ConfigFactoryInterface $config_factory, ChallengeManagerInterface $challenge_manager, MappingManagerInterface $mapping_manager, $mapping_backends, $mapping_backend) {
    $this->session = $session;
    $this->config_factory = $config_factory;

    $this->config = $config_factory->get('trezor_connect.settings');

    $this->challenge_manager = $challenge_manager;
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
   * Returns an array of mapping backends suitable for a form api #options.
   */
  public function mappingBackendOptions() {
    $output = array();

    foreach ($this->mapping_backends as $id => $value) {
      $output[$id] = $value['title'];
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function newChallenge() {
    return $this->challenge_manager->newChallenge();
  }

  public function handleChallengeResponse($uid) {
    $validate = TRUE;

    $response = $this->challengeResponse($validate);

    if ($response) {
      $this->mapChallengeResponse($uid, $response);
    }
  }

  /**
   * @inheritDoc
   */
  public function challengeResponse($validate = TRUE) {
    $output = $this->session->get('trezor_connect_response');

    if (!($output instanceof ChallengeResponseInterface)) {
      $output = NULL;
    }
    else {
      if ($validate) {
        $result = $output->isValid();

        if (!$result) {
          $output = NULL;
        }
      }
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function mapChallengeResponse($uid, ChallengeResponseInterface $response) {
    $mapping = Mapping::fromChallengeResponse($response);

    $mapping->setUid($uid);

    $this->mapping_manager->set($mapping);
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
  public function accountMapping($uid) {
    $output = $this->mapping_manager->get($uid);

    return $output;
  }

  public function checkChallengeResponseState($uid) {
    $output = static::STATE_CHALLENGE_RESPONSE_NOT_FOUND;

    // Do not validate the challenge response
    $validate = FALSE;

    $response = $this->challengeResponse($validate);

    if ($response) {
      // A challenge response is stored on the session
      $result = $response->isValid();

      if (!$result) {
        // The challenge response is invalid
        $output = static::STATE_CHALLENGE_RESPONSE_INVALID;
      }
      else {
        // The challenge response is valid

        // Check the account mappings, against the challenge response
        $public_key_mapping = $this->getMappingsFromPublicKey($response->getPublicKey());

        if (!$public_key_mapping) {
          // The challenge response has not associated with an account
          $output = static::STATE_CHALLENGE_RESPONSE_NEW;

          $mapping = $this->getMappingsFromUid($uid);

          if ($mapping) {
            // The challenge response public key is different
            $output = static::STATE_CHALLENGE_RESPONSE_UPDATE;
          }
        }
        else {
          $mapping_uid = $public_key_mapping->getUid();

          if ($uid != $mapping_uid) {
            $output = static::STATE_CHALLENGE_RESPONSE_OTHER_ACCOUNT;
          }
          else {
            $output = static::STATE_CHALLENGE_RESPONSE_EXISTS;
          }
        }
      }
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMappingsFromPublicKey($public_key) {
    $output = $this->mapping_manager->get($public_key);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMappingsFromUid($uid) {
    $output = $this->mapping_manager->getFromUid($uid);

    return $output;
  }
}

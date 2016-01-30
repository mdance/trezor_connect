<?php

/**
 * @file
 *
 * Contains \Drupal\trezor_connect\ChallengeResponseManager
 */

namespace Drupal\trezor_connect\Challenge;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\HttpFoundation\Request;
use Drupal\trezor_connect\Challenge\ChallengeResponseInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChallengeResponseManager implements ChallengeResponseManagerInterface, ContainerInjectionInterface {
  const SESSION_KEY = 'trezor_connect.challenge_response';

  /**
   * The session to store the challenge response on.
   *
   * @var SessionInterface
   */
  var $session;

  /**
   * The request to check for a challenge response.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  var $request;

  /**
   * Provides the challenge response.
   *
   * @var \Drupal\trezor_connect\Challenge\ChallengeResponseInterface
   */
  var $challenge_response;

  /**
   * Constructs a new object.
   *
   * @param $request
   *   The request to check for a challenge response.
   */
  public function __construct(SessionInterface $session, RequestStack $request_stack, ChallengeResponseInterface $challenge_response) {
    $this->session = $session;
    $this->request = $request_stack->getCurrentRequest();
    $this->challenge_response = $challenge_response;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('session'),
      $container->get('request_stack'),
      $container->get('trezor_connect.challenge_response')
    );
  }

  /**
   * @inheritDoc
   */
  public function get() {
    $challenge_response = $this->challenge_response;

    $response = $this->request->request->get('response');

    if (is_array($response)) {
      $mappings = [
        'success' => [
          $challenge_response,
          'setSuccess',
        ],
        'public_key' => [
          $challenge_response,
          'setPublicKey',
        ],
        'signature' => [
          $challenge_response,
          'setSignature',
        ],
        'version' => [
          $challenge_response,
          'setVersion',
        ],
      ];

      foreach ($mappings as $key => $callable) {
        if (isset($response[$key])) {
          $callable($response[$key]);
        }
      }
    }
    else {
      // Check the session for a challenge response
      $challenge_response = $this->session->get(self::SESSION_KEY);
    }

    return $challenge_response;
  }

  /**
   * @inheritDoc
   */
  public function set(ChallengeResponseInterface $challenge_response = NULL) {
    if (is_null($challenge_response)) {
      $challenge_response = $this->get();
    }

    $this->session->set(self::SESSION_KEY, $challenge_response);
  }

  /**
   * @inheritDoc
   */
  public function delete() {
    $this->session->remove(self::SESSION_KEY);
  }

}

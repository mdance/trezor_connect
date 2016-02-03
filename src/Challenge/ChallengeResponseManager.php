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
    $output = $this->getPost();

    if (is_null($output)) {
      $output = $this->getSession();
    }

    return $output;
  }

  public function getPost() {
    $output = NULL;

    $response = $this->request->request->get('response');

    if (is_array($response)) {
      $output = $this->challenge_response;

      $mappings = [
        'success' => [
          $output,
          'setSuccess',
        ],
        'public_key' => [
          $output,
          'setPublicKey',
        ],
        'signature' => [
          $output,
          'setSignature',
        ],
        'version' => [
          $output,
          'setVersion',
        ],
      ];

      foreach ($mappings as $key => $callable) {
        if (isset($response[$key])) {
          $callable($response[$key]);
        }
      }
    }

    return $output;
  }

  public function getSession() {
    $output = $this->session->get(self::SESSION_KEY);

    return $output;
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

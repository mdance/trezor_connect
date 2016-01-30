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

class ChallengeResponseManager implements ChallengeResponseManagerInterface, ContainerInjectionInterface {

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
  public function __construct(RequestStack $request_stack, ChallengeResponseInterface $challenge_response) {
    $this->request = $request_stack->getCurrentRequest();
    $this->challenge_response = $challenge_response;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('trezor_connect.challenge_response')
    );
  }

  /**
   * @inheritDoc
   */
  public function get() {
    $response = $this->request->request->get('response');

    $challenge_response = $this->challenge_response;

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

    return $challenge_response;
  }

}

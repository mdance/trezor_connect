<?php

/**
 * @file
 *
 * Contains \Drupal\trezor_connect\ChallengeManager
 */

namespace Drupal\trezor_connect\Challenge;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Drupal\trezor_connect\Challenge\ChallengeInterface;

class ChallengeManager implements ChallengeManagerInterface, ContainerInjectionInterface {
  const SESSION_KEY = 'trezor_connect.challenge';

  /**
   * Provides the session service.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  var $session;

  /**
   * Provides the challenge service.
   *
   * @var \Drupal\trezor_connect\Challenge\ChallengeInterface
   */
  var $challenge;

  /**
   * Constructs a new object.
   *
   * @param Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session service used to store challenges.
   *
   * @param Drupal\trezor_connect\Challenge\ChallengeInterface $challenge
   *   The challenge service used to create challenges.
   */
  public function __construct(SessionInterface $session, ChallengeInterface $challenge) {
    $this->session = $session;
    $this->challenge = $challenge;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('session'),
      $container->get('trezor_connect.challenge')
    );
  }

  /**
   * Returns a challenge.
   *
   * If a challenge is not stored on the session, a new challenge will be
   * generated.
   *
   * @return ChallengeInterface|false
   *   The challenge object or FALSE.
   */
  public function get() {
    $name = self::SESSION_KEY;

    $output = $this->session->get($name);

    if ( is_null($output) ) {
      $output = $this->challenge;

      $this->session->set($name, $output);
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function hash() {
    $output = $this->get();
    $output = (string)$output;
    $output = hash('sha256', $output);

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function delete() {
    $this->session->remove(self::SESSION_KEY);
  }

}

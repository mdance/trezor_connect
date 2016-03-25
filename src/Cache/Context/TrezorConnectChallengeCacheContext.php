<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Cache\Context\TrezorConnectChallengeCacheContext.
 *
 * TODO: Fix challenge caching
 */

namespace Drupal\trezor_connect\Cache\Context;

use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;

/**
 * Provides the TREZOR Connect Challenge caching context.
 *
 * Cache context ID: 'trezor_connect_challenge'.
 */
class TrezorConnectChallengeCacheContext implements CacheContextInterface {

  /**
   * Provides the challenge manager service.
   */
  protected $challenge_manager;

  /**
   * Constructs a new object.
   */
  public function __construct(ChallengeManagerInterface $challenge_manager) {
    $this->challenge_manager = $challenge_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('TREZOR Connect Challenge');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $output = $this->challenge_manager->get();

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function getCacheableMetadata() {
    $output = new CacheableMetadata();

    $challenge = $this->challenge_manager->get();

    if ($challenge) {
      $id = $challenge->getId();

      if ($id) {
        $tags = ['trezor_connect_challenge:' . $id];

        $output->setCacheTags($tags);
      }
    }

    return $output;
  }
}

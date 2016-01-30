<?php
/**
 * Contains \Drupal\trezor_connect\MappingInterface.
 */

namespace Drupal\trezor_connect\Mapping;

use Drupal\trezor_connect\Challenge\ChallengeResponseInterface;

interface MappingInterface {

  /**
   * Returns an array of property keys.
   *
   * @return array
   */
  public static function keys();

  /**
   * Returns a Mapping as an array.
   *
   * @param \Drupal\trezor_connect\MappingInterface $mapping
   *
   * @return array
   */
  public static function toArray(MappingInterface $mapping);

  /**
   * Returns a Mapping from an array.
   *
   * @param array $mapping
   *
   * @return \Drupal\trezor_connect\Mapping
   */
  public static function fromArray(array $mapping);

  /**
   * Returns a Mapping from a challenge response.
   *
   * @param \Drupal\trezor_connect\Challenge\ChallengeResponseInterface $response
   *
   * @return MappingInterface
   */
  public static function fromChallengeResponse(ChallengeResponseInterface $response);
}

<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\MappingManagerFactoryInterface.
 */

namespace Drupal\trezor_connect\Mapping;

/**
 * Provides the MappingManagerFactoryInterface interface.
 */
interface MappingManagerFactoryInterface {

  /**
   * Gets a mapping backend class.
   *
   * @return \Drupal\trezor_connect\MappingBackendInterface
   *   The mapping backend object.
   */
  public function get();

}

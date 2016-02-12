<?php

/**
 * @file
 * Contains \Drupal\trezor_connect\Compiler\ChallengeResponseBackendsPass.
 */

namespace Drupal\trezor_connect\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds trezor_connect_challenge_response_backends parameter to the container.
 */
class ChallengeResponseBackendsPass implements CompilerPassInterface {

  /**
   * Implements CompilerPassInterface::process().
   *
   * Collects the tagged services and stores them into the appropriate parameter.
   */
  public function process(ContainerBuilder $container) {
    $services = array();
    $default = NULL;

    $definitions = $container->findTaggedServiceIds('trezor_connect_challenge_response_backend');

    foreach ($definitions as $key => $value) {
      if (is_null($default)) {
        $default = $key;
      }

      if (isset($value[0]['default'])) {
        $default = $key;
      }

      $definition = $container->getDefinition($key);

      $tag = $definition->getTag('trezor_connect_challenge_response_backend');

      $services[$key] = array(
        'class' => $definition->getClass(),
        'title' => $tag['0']['title'],
      );
    }

    $container->setParameter('trezor_connect_challenge_response_backends', $services);
    $container->setParameter('trezor_connect_challenge_response_backend', $default);
  }
}

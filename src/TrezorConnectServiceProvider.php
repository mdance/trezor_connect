<?php
/**
 * @file
 * Contains \Drupal\trezor_connect\TrezorConnectServiceProvider.
 */

namespace Drupal\trezor_connect;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;

use Drupal\trezor_connect\Compiler\ChallengeBackendsPass;
use Drupal\trezor_connect\Compiler\ChallengeResponseBackendsPass;
use Drupal\trezor_connect\Compiler\MappingBackendsPass;

class TrezorConnectServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $container->addCompilerPass(new ChallengeBackendsPass());
    $container->addCompilerPass(new ChallengeResponseBackendsPass());
    $container->addCompilerPass(new MappingBackendsPass());
  }

}

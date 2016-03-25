<?php
/**
 * @file
 * Provides entity hooks.
 */

/**
 * Implements hook_entity_type_alter().
 *
 * Responsible for altering the user register form class.
 *
 * @param array $entity_types
 */
function trezor_connect_entity_type_alter(array &$entity_types) {
  if (isset($entity_types['user'])) {
    /**
     * @var Drupal\Core\Entity\ContentEntityType $entity_type
     */
    $entity_type = $entity_types['user'];

    $handlers = $entity_type->getHandlerClasses();

    if (isset($handlers['form']['register'])) {
      $value = 'Drupal\trezor_connect\Form\TrezorConnectRegisterForm';

      $handlers['form']['register'] = $value;

      $entity_type->setHandlerClass('form', $handlers['form']);
    }
  }
}

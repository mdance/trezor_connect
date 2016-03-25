<?php
/**
 * Contains Drupal\trezor_connect\Enum\Messages.
 */

namespace Drupal\trezor_connect\Enum;

class Messages {

  /**
   * Provides the password empty message.
   */
  const PASSWORD_EMPTY = 'Please specify the current password for the account in order to make changes to the authentication device';

  /**
   * Provides the invalid password message.
   */
  const PASSWORD_INVALID = 'The password you have entered is invalid.';

  /**
   * Provides the max password attempts message.
   */
  const PASSWORD_MAX_ATTEMPTS = 'You are not allowed anymore password guesses.';

  /**
   * Provides the invalid challenge response message.
   */
  const CHALLENGE_RESPONSE_INVALID = 'An error has occurred validating your TREZOR credentials.';

  /**
   * Provides the max challenge response attempts message.
   */
  const CHALLENGE_RESPONSE_MAX_ATTEMPTS = 'You are not allowed anymore authentication attempts.';

  /**
   * Provides the challenge response mapping exists message.
   */
  const CHALLENGE_RESPONSE_MAPPING_EXISTS = 'There is already an account associated with the authentication device.';

}

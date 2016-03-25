<?php
/**
 * @file
 * Contains \Drupal\trezor_connect\Challenge\ChallengeManagerInterface.
 */

namespace Drupal\trezor_connect\Challenge;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

interface ChallengeManagerInterface {

  /**
   * Sets the current request.
   *
   * @param $request
   *
   * @return mixed
   */
  public function setRequest(Request $request);

  /**
   * Returns the current request.
   *
   * @return
   */
  public function getRequest();

  /**
   * Sets the session service.
   *
   * @param $session
   *
   * @return mixed
   */
  public function setSession(SessionInterface $session);

  /**
   * Returns the session service.
   *
   * @return
   */
  public function getSession();

  /**
   * Sets the backend.
   *
   * @param \Drupal\trezor_connect\Challenge\ChallengeBackendInterface $backend
   *
   * @return mixed
   */
  public function setBackend(ChallengeBackendInterface $backend);

  /**
   * Returns the challenge backend.
   *
   * @return \Drupal\trezor_connect\Challenge\ChallengeBackendInterface
   */
  public function getBackend();

  /**
   * Sets the challenge.
   *
   * @param \Drupal\trezor_connect\Challenge\ChallengeInterface $challenge
   *
   * @return mixed
   */
  public function setChallenge(ChallengeInterface $challenge);

  /**
   * Returns the challenge.
   *
   * @return \Drupal\trezor_connect\Challenge\ChallengeInterface
   */
  public function getChallenge();

  /**
   * Sets the challenge offset.
   *
   * @param int $challenge_offset
   */
  public function setChallengeOffset($challenge_offset);

  /**
   * Returns the challenge offset.
   *
   * @return int
   */
  public function getChallengeOffset();

  /**
   * Gets the cache tags invalidator service.
   *
   * @return mixed
   */
  public function getCacheTagsInvalidator();

  /**
   * Sets the cache tags invalidator service.
   *
   * @param mixed $cache_tags_invalidator
   */
  public function setCacheTagsInvalidator(CacheTagsInvalidatorInterface $cache_tags_invalidator);

  /**
   * Returns a challenge associated with an id.
   *
   * @param int|array|NULL $id
   *   The challenge id to retrieve.  If null, the current request will be
   * checked for a challenge, otherwise a new challenge will be generated and
   * returned.
   *
   * @param array $conditions
   *   An array of conditions.  The array should contain the following keys:
   *
   *     field - A string containing the name of the field.
   *     value - A string containing the value for the condition.
   *     operator - A string containing the condition operator.
   *
   * @return ChallengeInterface
   *   A Challenge object.
   *
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::getRequestChallenge()
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::getMultiple()
   */
  public function get($id = NULL, array $conditions = NULL);

  /**
   * Returns the challenges associated with an array of ids.
   *
   * @param int|array|NULL $ids
   *   The challenge ids to retrieve.  If null, the current request will be
   * checked for a challenge, otherwise a new challenge will be generated and
   * returned.
   *
   * @param array $conditions
   *   An array of conditions.  The array should contain the following keys:
   *
   *     field - A string containing the name of the field.
   *     value - A string containing the value for the condition.
   *     operator - A string containing the condition operator.
   *
   * @return array
   *   An array of Challenge objects.
   *
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::get()
   */
  public function getMultiple(array $ids, array $conditions = NULL);

  /**
   * Stores a challenge.
   *
   * @param ChallengeInterface $challenge
   *   The challenge object to store.
   */
  public function set(ChallengeInterface $challenge);

  /**
   * Deletes a challenge.
   *
   * @param integer $id
   *   The challenge id to be deleted.
   *
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::deleteAll()
   */
  public function delete($id);

  /**
   * Deletes all challenges.
   */
  public function deleteAll();

  /**
   * Deletes any expired challenges.
   *
   * @param ChallengeResponseManagerInterface $challenge_response_manager
   *   The challenge response manager used to retrieve any challenges associated
   * with challenges, as these cannot be deleted.
   */
  public function deleteExpired(ChallengeResponseManagerInterface $challenge_response_manager);

}

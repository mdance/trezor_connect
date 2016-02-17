<?php
/**
 * @file
 * Contains \Drupal\trezor_connect\Challenge\ChallengeManagerInterface.
 */

namespace Drupal\trezor_connect\Challenge;

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
  public function setCacheTagsInvalidator($cache_tags_invalidator);

  /**
   * Returns a challenge associated with an id.
   *
   * @param int $id
   *   The challenge id to retrieve.  If null, the current request will be
   * checked for a challenge, otherwise a new challenge will be generated and
   * returned.
   *
   * @return Challenge|false
   *   The challenge object or FALSE.
   *
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::getRequestChallenge()
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::getMultiple()
   */
  public function get($id = NULL, $reset = FALSE);

  /**
   * Returns a challenge associated with the current request.
   *
   * @return Challenge|false
   *   The challenge object or FALSE.
   */
  public function getRequestChallenge();

  /**
   * Returns the challenges associated with an array of ids.
   *
   * @param array $ids
   *   An array of challenge ids.
   *
   * @return array
   *   An array of Challenge objects.
   *
   * @see \Drupal\trezor_connect\ChallengeBackendInterface::get()
   */
  public function getMultiple(array $ids);

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

}

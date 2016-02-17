<?php
/**
 * @file
 * Contains \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface.
 */

namespace Drupal\trezor_connect\ChallengeResponse;

use Drupal\trezor_connect\Challenge\ChallengeInterface;
use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

interface ChallengeResponseManagerInterface {

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
   * Sets the backend.
   *
   * @param \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface $backend
   *
   * @return mixed
   */
  public function setBackend(ChallengeResponseBackendInterface $backend);

  /**
   * Returns the challenge response backend.
   *
   * @return \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface
   */
  public function getBackend();

  /**
   * Sets the challenge response.
   *
   * @param \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface $challenge
   *
   * @return mixed
   */
  public function setChallengeResponse(ChallengeResponseInterface $challenge_response);

  /**
   * Returns the challenge response.
   *
   * @return \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseInterface
   */
  public function getChallengeResponse();

  /**
   * Sets the challenge manager service.
   *
   * @param \Drupal\trezor_connect\Challenge\ChallengeManagerInterface $challenge_manager
   *
   * @return mixed
   */
  public function setChallengeManager(ChallengeManagerInterface $challenge_manager);

  /**
   * Returns the challenge manager service.
   *
   * @return \Drupal\trezor_connect\Challenge\ChallengeManagerInterface
   */
  public function getChallengeManager();

  /**
   * Returns a challenge response associated with an id.
   *
   * @param int $id
   *   The challenge response id to retrieve.  If not specified, the request,
   * and session will be checked for a challenge response.
   *
   * @return ChallengeResponse|false
   *   The challenge response object or FALSE.
   *
   * @see \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface::getMultiple()
   */
  public function get($id = NULL);

  /**
   * Returns a challenge response associated with the current request.
   *
   * @return ChallengeResponse|false
   *   The challenge response object or FALSE.
   *
   * @see \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface::getMultiple()
   */
  public function getRequestChallengeResponse();

  /**
   * Returns the challenges response associated with an array of ids.
   *
   * @param array $ids
   *   An array of challenge response ids.
   *
   * @return array
   *   An array of challenge response objects.
   *
   * @see \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface::get()
   */
  public function getMultiple(array $ids);

  /**
   * Returns the challenge response associated with the public key.
   *
   * @param $public_key
   *
   * @return mixed
   */
  public function getPublicKey($public_key);

  /**
   * Returns the challenge responses associated with the public keys.
   *
   * @param $public_keys
   *
   * @return mixed
   */
  public function getMultipleFromPublicKey($public_keys);

  /**
   * Stores a challenge response.
   *
   * @param ChallengeResponse $challenge_response
   *   The challenge response object to store.
   *
   * @param boolean $session
   *   Determines whether to store the challenge response on the session.
   */
  public function set(ChallengeResponseInterface $challenge_response, $session = TRUE);

  /**
   * Stores the active challenge response on the session.
   *
   * @return mixed
   */
  public function setSessionChallengeResponse(ChallengeResponseInterface $challenge_response);

  /**
   * Deletes a challenge response.
   *
   * @param integer $id
   *   The challenge response id to be deleted.
   *
   * @see \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseBackendInterface::deleteAll()
   */
  public function delete($id);

  /**
   * Deletes all challenge responses.
   */
  public function deleteAll();

  /**
   * Removes the active challenge response from the session.
   *
   * @return mixed
   */
  public function deleteSessionChallengeResponse();

}

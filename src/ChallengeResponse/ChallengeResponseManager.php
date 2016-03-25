<?php
/**
 * Contains \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManager
 */

namespace Drupal\trezor_connect\ChallengeResponse;

use Drupal\trezor_connect\Challenge\ChallengeInterface;
use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
use Drupal\trezor_connect\Mapping\MappingManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChallengeResponseManager implements ChallengeResponseManagerInterface {

  const KEY = 'trezor_connect_challenge_response';

  /**
   * Provides the session object.
   */
  protected $session;

  /**
   * The current request.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  var $request;

  /**
   * Provides the backend service.
   */
  protected $backend;

  /**
   * Provides the challenge response object.
   */
  protected $challenge_response;

  /**
   * Provides the challenge manager service.
   */
  protected $challenge_manager;

  public function __construct() {
  }

  /**
   * @inheritDoc
   */
  public function setSession(SessionInterface $session) {
    $this->session = $session;
  }

  /**
   * @inheritDoc
   */
  public function getSession() {
    return $this->session;
  }

  /**
   * @inheritDoc
   */
  public function setRequest(Request $request) {
    $this->request = $request;
  }

  /**
   * @inheritDoc
   */
  public function getRequest() {
    return $this->request;
  }

  /**
   * @inheritDoc
   */
  public function setBackend(ChallengeResponseBackendInterface $backend) {
    $this->backend = $backend;
  }

  /**
   * @inheritDoc
   */
  public function getBackend() {
    return $this->backend;
  }

  /**
   * @inheritDoc
   */
  public function setChallengeResponse(ChallengeResponseInterface $challenge_response) {
    $this->challenge_response = $challenge_response;
  }

  /**
   * @inheritDoc
   */
  public function getChallengeResponse() {
    return $this->challenge_response;
  }

  /**
   * @inheritDoc
   */
  public function setChallengeManager(ChallengeManagerInterface $challenge_manager) {
    $this->challenge_manager = $challenge_manager;
  }

  /**
   * @inheritDoc
   */
  public function getChallengeManager() {
    return $this->challenge_manager;
  }

  /**
   * @inheritDoc
   */
  public function setChallengeResponseOffset($challenge_response_offset) {
    $this->challenge_response_offset = $challenge_response_offset;
  }

  /**
   * @inheritDoc
   */
  public function getChallengeResponseOffset() {
    return $this->challenge_response_offset;
  }

  /**
   * @inheritDoc
   */
  public function get($id = NULL, array $conditions = NULL) {
    $output = $this->backend->get($id, $conditions);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultiple(array $ids, array $conditions = NULL) {
    $output = $this->backend->getMultiple($ids, $conditions);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getPublicKey($public_key) {
    if (is_array($public_key)) {
      $public_key = array(
        $public_key,
      );
    }

    $output = $this->getMultipleFromPublicKey($public_key);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultipleFromPublicKey($public_keys) {
    $output = $this->backend->getMultipleFromPublicKey($public_keys);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function set(ChallengeResponseInterface $challenge_response) {
    $created = $challenge_response->getCreated();

    if (!$created) {
      $created = time();

      $challenge_response->setCreated($created);
    }

    // Save the challenge response
    $this->backend->set($challenge_response);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function delete($id) {
    $this->backend->delete($id);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function deleteMultiple(array $ids) {
    $this->backend->deleteMultiple($ids);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function deleteAll() {
    $this->backend->deleteAll();

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function deleteExpired(MappingManagerInterface $mapping_manager) {
    $now = time();
    $offset = $this->getChallengeResponseOffset();

    $value = $now - $offset;

    $conditions = array();

    $condition = array(
      'field' => 'created',
      'value' => $value,
      'operator' => '<=',
    );

    $conditions[] = $condition;

    $challenge_responses = $this->backend->getMultiple(array(), $conditions);

    $ids = array();

    foreach ($challenge_responses as $challenge_response) {
      $id = $challenge_response->getId();

      $ids[$id] = $id;
    }

    $conditions = array();

    if (count($ids)) {
      $condition = array(
        'field' => 'challenge_response_id',
        'value' => $ids,
        'operator' => 'IN',
      );

      $conditions[] = $condition;

      // Filter out any ids used in mappings
      $mappings = $mapping_manager->getMultiple(array(), $conditions);

      foreach ($mappings as $mapping) {
        $challenge_response_id = $mapping->getChallengeResponse()->getId();

        unset($ids[$challenge_response_id]);
      }
    }

    if (count($ids)) {
      $this->backend->delete($ids);
    }
  }

}

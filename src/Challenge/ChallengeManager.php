<?php
/**
 * Contains \Drupal\trezor_connect\Challenge\ChallengeManager
 */

namespace Drupal\trezor_connect\Challenge;

use Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChallengeManager implements ChallengeManagerInterface {

  /**
   * Provides the key used to identify the challenge in post requests, and on
   * the session object.
   */
  const KEY = 'trezor_connect_challenge';

  /**
   * Provides the current request.
   */
  protected $request;

  /**
   * Provides the session.
   */
  protected $session;

  /**
   * Provides the backend service.
   */
  protected $backend;

  /**
   * Provides the challenge object.
   */
  protected $challenge;

  /**
   * Provides an integer containing the challenge offset.
   *
   * @var int
   */
  protected $challenge_offset;

  /**
   * Provides the cache tags invalidator service.
   */
  protected $cache_tags_invalidator;

  public function __construct() {
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
  public function setBackend(ChallengeBackendInterface $backend) {
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
  public function setChallenge(ChallengeInterface $challenge) {
    $this->challenge = $challenge;
  }

  /**
   * @inheritDoc
   */
  public function getChallenge() {
    return $this->challenge;
  }

  /**
   * @inheritDoc
   */
  public function setChallengeOffset($challenge_offset) {
    $this->challenge_offset = $challenge_offset;
  }

  /**
   * @inheritDoc
   */
  public function getChallengeOffset() {
    return $this->challenge_offset;
  }

  /**
   * @return mixed
   */
  public function getCacheTagsInvalidator() {
    return $this->cache_tags_invalidator;
  }

  /**
   * @param mixed $cache_tags_invalidator
   */
  public function setCacheTagsInvalidator($cache_tags_invalidator) {
    $this->cache_tags_invalidator = $cache_tags_invalidator;
  }

  /**
   * @inheritDoc
   */
  public function get($id = NULL, array $conditions = NULL) {
    if (is_null($id)) {
      // Check if a challenge exists on the current request
      $output = $this->getRequestChallenge();

      if (!$output) {
        $output = $this->getChallenge();

        $id = $output->getId();

        if (is_null($id)) {
          // Generate a new challenge
          $output->generate();

          // Store the challenge on the backend
          $this->set($output);
        }
      }
    }
    else {
      // Retrieve a specific challenge
      $output = $this->backend->get($id, $conditions);
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getRequestChallenge() {
    $output = NULL;

    $result = $this->request->get(self::KEY);

    if (is_array($result)) {
      if (isset($result['id']) && is_numeric($result['id'])) {
        $results = $this->backend->get($result['id']);
        $total = count($results);

        if ($total == 1) {
          $output = array_shift($results);
        }
      }
    }

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
  public function set(ChallengeInterface $challenge) {
    $this->backend->set($challenge);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function delete($id) {
    // TODO: Implement cache tag invalidation
    $this->backend->delete($id);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function deleteAll() {
    // TODO: Implement cache tag invalidation
    $this->backend->deleteAll();

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function deleteExpired(ChallengeResponseManagerInterface $challenge_response_manager) {
    $now = time();
    $offset = $this->getChallengeOffset();

    $value = $now - $offset;

    $conditions = array();

    $condition = array(
      'field' => 'created',
      'value' => $value,
      'operator' => '<=',
    );

    $conditions[] = $condition;

    $challenges = $this->backend->get(NULL, $conditions);

    $ids = array();

    foreach ($challenges as $challenge) {
      $id = $challenge->getId();

      $ids[$id] = $id;
    }

    $conditions = array();

    $condition = array(
      'field' => 'challenge_id',
      'value' => $ids,
      'operator' => 'IN',
    );

    $conditions[] = $condition;

    // Filter out any ids used in challenge responses
    $challenge_responses = $challenge_response_manager->get(array(), $conditions);

    foreach ($challenge_responses as $challenge_response) {
      $challenge_id = $challenge_response->getChallenge()->getId();

      unset($ids[$challenge_id]);
    }

    $this->backend->delete($ids);
  }

}

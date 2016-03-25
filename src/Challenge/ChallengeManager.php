<?php
/**
 * Contains \Drupal\trezor_connect\Challenge\ChallengeManager
 */

namespace Drupal\trezor_connect\Challenge;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
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
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
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
  public function setCacheTagsInvalidator(CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->cache_tags_invalidator = $cache_tags_invalidator;
  }

  /**
   * @inheritDoc
   */
  public function get($id = NULL, array $conditions = NULL) {
    if (is_null($id)) {
      $output = $this->getSessionChallenge();

      if ($output) {
        // Make sure the challenge still exists
        $output = $this->backend->get($output->getId());
      }

      if (!$output) {
        $output = $this->getChallenge();
      }

      $id = $output->getId();

      if (is_null($id)) {
        // Generate a new challenge
        $output->generate();

        // Store the challenge on the backend
        $this->set($output);

        $this->setSessionChallenge($output);
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
  public function getSessionChallenge() {
    $output = NULL;

    $result = $this->session->get(self::KEY);

    if ($result instanceof ChallengeInterface) {
      $output = $result;
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

  public function setSessionChallenge(ChallengeInterface $challenge = NULL) {
    $this->session->set(self::KEY, $challenge);
  }

  /**
   * @inheritDoc
   */
  public function delete($id) {
    $this->backend->delete($id);

    $tags = array(
      'trezor_connect_challenge:' . $id,
    );

    $this->cache_tags_invalidator->invalidateTags($tags);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function deleteAll() {
    $this->backend->deleteAll();

    $tags = array(
      'trezor_connect_challenge',
    );

    $this->cache_tags_invalidator->invalidateTags($tags);

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

    $challenges = $this->backend->getMultiple(NULL, $conditions);

    $ids = array();

    foreach ($challenges as $challenge) {
      $id = $challenge->getId();

      $ids[$id] = $id;
    }

    if (count($ids)) {
      $conditions = array();

      $condition = array(
        'field' => 'challenge_id',
        'value' => $ids,
        'operator' => 'IN',
      );

      $conditions[] = $condition;

      // Filter out any ids used in challenge responses
      $challenge_responses = $challenge_response_manager->getMultiple(array(), $conditions);

      foreach ($challenge_responses as $challenge_response) {
        $challenge_id = $challenge_response->getChallenge()->getId();

        unset($ids[$challenge_id]);
      }
    }

    if (count($ids)) {
      $this->backend->delete($ids);
    }
  }

}

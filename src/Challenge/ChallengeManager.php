<?php
/**
 * Contains \Drupal\trezor_connect\Challenge\ChallengeManager
 */

namespace Drupal\trezor_connect\Challenge;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChallengeManager implements ChallengeManagerInterface {

  const SESSION_KEY = 'trezor_connect.challenge';

  /**
   * @inheritdoc
   */
  protected $session;

  /**
   * @inheritdoc
   */
  protected $backend;

  /**
   * @inheritdoc
   */
  protected $challenge;

  /**
   * @inheritdoc
   */
  protected $cache_tags_invalidator;

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
  public function get($id = NULL, $reset = FALSE) {
    if (is_null($id)) {
      // Check if a challenge exists on the session
      $output = $this->getSessionChallenge();

      if (!$output || $reset) {
        $output = $this->getChallenge();

        $id = $output->getId();

        if (is_null($id)) {
          // Generate a new challenge
          $output->generate();

          // Store the challenge on the backend
          $this->backend->set($output);

          // Save the challenge to the session
          $this->session->set(self::SESSION_KEY, $output);
        }
      }
    }
    else {
      // Retrieve a specific challenge
      $output = $this->backend->get($id);
    }

    return $output;
  }

  public function getSessionChallenge() {
    $output = $this->session->get(self::SESSION_KEY);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultiple(array $ids) {
    $output = $this->backend->getMultiple($ids);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function set(Challenge $challenge) {
    $this->backend->set($challenge);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setMultiple(array $challenges) {
    $this->backend->setMultiple($challenges);

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

  public function remember() {
    $value = $this->get();

    if ($value) {
      $this->session->set(self::SESSION_KEY, $value);
    }

    return $this;
  }

  public function forget() {
    $this->session->remove(self::SESSION_KEY);

    return $this;
  }

}

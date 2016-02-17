<?php
/**
 * Contains \Drupal\trezor_connect\ChallengeResponse\ChallengeResponseManager
 */

namespace Drupal\trezor_connect\ChallengeResponse;

use Drupal\trezor_connect\Challenge\ChallengeInterface;
use Drupal\trezor_connect\Challenge\ChallengeManagerInterface;
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
  public function get($id = NULL) {
    if (is_null($id)) {
      // Check if a challenge exists on the current request
      $output = $this->getRequestChallengeResponse();

      if (!$output) {
        $output = $this->getSessionChallengeResponse();
      }

      if ($output) {
        $id = $output->getId();

        if (is_null($id)) {
          // Store the challenge response
          $this->set($output);
        }
      }
    }
    else {
      // Retrieve a specific challenge response
      $output = $this->backend->get($id);
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getRequestChallengeResponse() {
    $output = NULL;

    $response = $this->request->request->get(self::KEY);

    if (is_array($response)) {
      if (isset($response['success']) && $response['success']) {
        $output = $this->getChallengeResponse();

        $challenge = $this->challenge_manager->getRequestChallenge();

        if ($challenge) {
          $output->setChallenge($challenge);
        }

        $mappings = [
          'public_key' => [
            $output,
            'setPublicKey',
          ],
          'signature' => [
            $output,
            'setSignature',
          ],
          'version' => [
            $output,
            'setVersion',
          ],
        ];

        foreach ($mappings as $key => $callable) {
          if (isset($response[$key])) {
            $callable($response[$key]);
          }
        }
      }
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getSessionChallengeResponse() {
    $output = $this->session->get(self::KEY);

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
  public function set(ChallengeResponseInterface $challenge_response, $session = TRUE) {
    $created = $challenge_response->getCreated();

    if (!$created) {
      $created = time();

      $challenge_response->setCreated($created);
    }

    // Save the challenge response to the session
    $this->backend->set($challenge_response);

    if ($session) {
      $this->setSessionChallengeResponse($challenge_response);
    }

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setSessionChallengeResponse(ChallengeResponseInterface $challenge_response) {
    $this->session->set(self::KEY, $challenge_response);

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
  public function deleteAll() {
    $this->backend->deleteAll();

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function deleteSessionChallengeResponse() {
    $this->session->remove(self::KEY);

    return $this;
  }

}

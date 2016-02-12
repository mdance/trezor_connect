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

  const SESSION_KEY = 'trezor_connect.challenge_response';

  /**
   * @inheritdoc
   */
  protected $session;

  /**
   * The request to check for a challenge response.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  var $request;

  /**
   * @inheritdoc
   */
  protected $backend;

  /**
   * @inheritdoc
   */
  protected $challenge_response;

  /**
   * @inheritdoc
   */
  protected $challenge_manager;

  /**
   * @inheritdoc
   */
  protected $challenge;

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
  public function get($id = NULL) {
    if (is_null($id)) {
      // Check if a challenge exists on the current request
      $output = $this->getPost();

      if (!$output) {
        $output = $this->getSessionChallengeResponse();
      }

      $id = $output->getId();

      if (is_null($id)) {
        // Store the challenge response on the backend
        $this->backend->set($output);

        // Save the challenge response to the session
        $this->session->set(self::SESSION_KEY, $output);
      }
    }
    else {
      // Retrieve a specific challenge response
      $output = $this->backend->get($id);
    }

    return $output;
  }

  public function getPost() {
    $output = NULL;

    $response = $this->request->request->get('response');

    if (is_array($response)) {
      if (isset($response['success']) && $response['success']) {
        $output = $this->getChallengeResponse();

        $challenge = $this->request->request->get('challenge');

        if (isset($challenge['id'])) {
          $challenges = $this->challenge_manager->get($challenge['id']);
          $total = count($challenges);

          if ($total) {
            $challenge = array_shift($challenges);

            $output->setChallenge($challenge);
          }
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

  public function getSessionChallengeResponse() {
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
  public function getPublicKey($public_key) {
    if (is_array($public_key)) {
      $public_key = array(
        $public_key,
      );
    }

    $output = $this->getMultiplePublicKey($public_key);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function getMultiplePublicKey($public_keys) {
    $output = $this->backend->getMultiplePublicKey($public_keys);

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function set(ChallengeResponseInterface $challenge_response) {
    $this->backend->set($challenge_response);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setMultiple(array $challenge_responses) {
    $this->backend->setMultiple($challenge_responses);

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

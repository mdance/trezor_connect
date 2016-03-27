<?php

/**
 * @file
 * Contains Drupal\Tests\trezor_connect\Unit\ChallengeTest
 */

namespace Drupal\Tests\trezor_connect\Unit;

use Drupal\Tests\UnitTestCase;

use Drupal\trezor_connect\Challenge\Challenge;

/**
 * Provides the Challenge unit tests.
 *
 * @coversDefaultClass \Drupal\trezor_connect\Challenge\Challenge
 * @ingroup trezor_connect
 * @group trezor_connect
 */
class ChallengeTest extends UnitTestCase {

  /**
   * @covers ::generate
   * @covers ::getCreated
   * @covers ::getChallengeHidden
   * @covers ::getChallengeVisual
   */
  public function testGenerate() {
    $now = time();

    $challenge = new Challenge();

    $challenge->generate();

    $created = $challenge->getCreated();

    $message = 'The Challenge created timestamp is invalid';

    $this->assertGreaterThanOrEqual($now, $created, $message);

    $challenge_hidden = $challenge->getChallengeHidden();

    $message = 'The hidden challenge is invalid';

    $this->assertNotEmpty($challenge_hidden, $message);

    $challenge_visual = $challenge->getChallengeVisual();

    $message = 'The visual challenge is invalid';

    $this->assertNotEmpty($challenge_visual, $message);

  }

}

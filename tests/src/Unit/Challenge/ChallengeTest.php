<?php

/**
 * @file
 * Contains Drupal\Tests\trezor_connect\Unit\Challenge\ChallengeTest.
 */

namespace Drupal\Tests\trezor_connect\Unit\Challenge;

use Drupal\Tests\UnitTestCase;

use Drupal\trezor_connect\Challenge\Challenge;

/**
 * Provides Challenge unit tests.
 *
 * @coversDefaultClass \Drupal\trezor_connect\Challenge\Challenge
 *
 * @group trezor_connect
 */
class ChallengeTest extends UnitTestCase {

  /**
   * @return array
   *
   * @todo Determine if this is necessary.
   */
  public static function getInfo() {
    $output = [
      'name' => 'Challenge Unit Tests',
      'description' => 'Provides challenge unit tests.',
      'group' => 'TREZOR Connect',
    ];

    return $output;
  }

  /**
   * Tests a new challenge.
   *
   * @covers ::generate
   * @covers ::setId
   * @covers ::getId
   * @covers ::setCreated
   * @covers ::getCreated
   * @covers ::setChallengeHidden
   * @covers ::getChallengeHidden
   * @covers ::setChallengeVisual
   * @covers ::getChallengeVisual
   * @covers ::random
   * @covers ::toArray
   */
  public function testChallenge() {
    $now = time();

    $challenge = new Challenge();

    $challenge->generate();

    $id = $challenge->getId();

    $created = $challenge->getCreated();

    $message = 'The Challenge created timestamp is invalid';

    $this->assertGreaterThanOrEqual($now, $created, $message);

    $challenge_hidden = $challenge->getChallengeHidden();

    $message = 'The hidden challenge is invalid';

    $this->assertNotEmpty($challenge_hidden, $message);

    $challenge_visual = $challenge->getChallengeVisual();

    $message = 'The visual challenge is invalid';

    $this->assertNotEmpty($challenge_visual, $message);

    $expected = [
      'id' => $id,
      'created' => $created,
      'challenge_hidden' => $challenge_hidden,
      'challenge_visual' => $challenge_visual,
    ];

    $result = $challenge->toArray();

    $this->assertArrayEquals($expected, $result, $message);

    $challenge->setId(1);

    $expected['id'] = 1;

    $result = $challenge->toArray();

    $message = 'The challenge id should be 1';

    $this->assertArrayEquals($expected, $result, $message);
  }

  /**
   * Tests a challenges hash.
   *
   * @covers ::__toString
   * @covers ::hash
   */
  public function testHash() {
    $challenge = new Challenge();

    $expected = 'de9acafb0bc52dc6db546cfa5f1769a420f9ba2a8c0f6a715fd89a0b3b5da204';
    $actual = $challenge->hash();

    $message = 'The challenge hash is invalid';

    $this->assertEquals($expected, $actual, $message);
  }

  /**
   * Tests a challenges cache contexts.
   *
   * @covers ::getCacheContexts
   */
  public function testGetCacheContexts() {
    $challenge = new Challenge();

    $actual = $challenge->getCacheContexts();

    $expected = [];

    $message = 'The challenge cache contexts are invalid';

    $this->assertEquals($expected, $actual, $message);
  }

  /**
   * Tests a challenges cache tags.
   *
   * @covers ::getCacheTags
   */
  public function testGetCacheTags() {
    $challenge = new Challenge();

    $challenge->generate();

    $challenge->setId(1);

    $actual = $challenge->getCacheTags();

    $expected = [];

    $expected[] = 'trezor_connect_challenge';
    $expected[] = 'trezor_connect_challenge:1';

    $message = 'The challenge cache tags are invalid';

    $this->assertArrayEquals($expected, $actual, $message);
  }

  /**
   * Tests a challenges cache max age.
   *
   * @covers ::getCacheMaxAge
   */
  public function testGetCacheMaxAge() {
    $challenge = new Challenge();

    $actual = $challenge->getCacheMaxAge();

    $expected = 0;

    $message = 'The challenge cache max age is invalid';

    $this->assertEquals($expected, $actual, $message);
  }

}

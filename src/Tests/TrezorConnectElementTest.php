<?php
/**
 * @file
 * Contains Drupal\trezor_connect\Tests\TrezorConnectElementTest.
 */

namespace Drupal\trezor_connect\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\trezor_connect\Enum\Permissions;
use Drupal\Core\Url;
use Drupal\trezor_connect\Enum\Routes;
use Drupal\Core\Session\AccountProxy;

/**
 * Class TrezorConnectElementTest
 *
 * @package Drupal\trezor_connect\Tests
 * @group trezor_connect
 */
class TrezorConnectElementTest extends WebTestBase {

  /**
   * Provides an array of modules to install.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'trezor_connect',
  );

  /**
   * Provides a test user.
   *
   * @var AccountProxy
   */
  private $user;

  public function setUp() {
    parent::setUp();

    $permissions = array(
      'access content',
      Permissions::LOGIN,
      Permissions::REGISTER,
      Permissions::VIEW,
      Permissions::REMOVE,
    );

    $this->user = $this->drupalCreateUser($permissions);
  }

  public function testTrezorConnectElement() {
    $this->drupalLogin($this->user);

    $route_parameters = array(
      'user' => $this->user->id(),
    );

    $path = Url::fromRoute(Routes::MANAGE, $route_parameters);

    $this->drupalGet($path);
    $this->assertResponse(200);

    $text = 'Authorise TREZOR device';
    $this->assertText($text);
  }

}

<?php

use PHPUnit\Framework\TestCase;
use WPSL\Elementor\Elementor;

/**
 * Class ClassElementorTest
 */
class ClassElementorTest extends TestCase {

  public static $functions;

  public function setUp(): void {
    self::$functions = $this->createPartialMock(
      ClassElementorTest::class,
      ['add_filter', 'add_action']
    );
  }

  public function testShouldInitModule() {
    self::$functions->expects($this->exactly(2))
      ->method('add_filter')
      ->withConsecutive(['set_url_scheme'], ['elementor/settings/general/success_response_data']);

    self::$functions->expects($this->exactly(4))
      ->method('add_action')
      ->withConsecutive(
        ['elementor/core/files/clear_cache'],
        ['save_post'],
        ['deleted_post'],
        ['sm::pre::sync::nonMediaFiles']
      );

    $elementor = new Elementor();
    $elementor->module_init([]);
  }

  public function add_filter() {
  }
  public function add_action() {
  }
}

function add_filter($a, $b, $c = 10, $d = 1) {
  return ClassElementorTest::$functions->add_filter($a, $b, $c, $d);
}

function add_action($a, $b, $c = 10, $d = 1) {
  return ClassElementorTest::$functions->add_action($a, $b, $c, $d);
}

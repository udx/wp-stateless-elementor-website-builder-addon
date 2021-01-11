<?php

use PHPUnit\Framework\TestCase;
use wpCloud\StatelessMedia\WPStatelessStub;
use WPSL\Elementor\Elementor;

/**
 * Class ClassElementorTest
 */
class ClassElementorTest extends TestCase {

  public static $functions;

  public function setUp(): void {
    self::$functions = $this->createPartialMock(
      ClassElementorTest::class,
      ['add_filter', 'add_action', 'apply_filters', 'do_action']
    );

    $this::$functions->method('apply_filters')->will($this->returnArgument(1));
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

  public function testShouldSyncAndRewriteUrl() {
    $elementor = new Elementor();

    $this->assertEquals('https://test.test/test/test.test', $elementor->sync_rewrite_url('https://test.test/test/test.test', null, null));
    $this::$functions->expects($this->exactly(2))
      ->method('apply_filters')->with('wp_stateless_file_name');

    $this::$functions->expects($this->exactly(2))
      ->method('do_action')->with('sm:sync::syncFile');

    ud_get_stateless_media()->set('sm.mode', 'disabled');
    $this->assertEquals('https://test.test/uploads/elementor/test.test', $elementor->sync_rewrite_url('https://test.test/uploads/elementor/test.test', null, null));

    ud_get_stateless_media()->set('sm.mode', 'stateless');
    var_dump(ud_get_stateless_media()->get('sm.mode'));
    $this->assertEquals(ud_get_stateless_media()->get_gs_host() . '/elementor/test.test', $elementor->sync_rewrite_url('https://test.test/uploads/elementor/test.test', null, null));
  }

  public function testShouldDeleteElementorFiles() {
  }

  public function testShouldDeleteCssFiles() {
  }

  public function testShouldDeleteGlobalCss() {
  }

  public function testShouldFilterCssFile() {
  }

  public function add_filter() {
  }

  public function add_action() {
  }

  public function apply_filters($a, $b) {
  }

  public function do_action($a, ...$b) {
  }
}

function add_filter($a, $b, $c = 10, $d = 1) {
  return ClassElementorTest::$functions->add_filter($a, $b, $c, $d);
}

function add_action($a, $b, $c = 10, $d = 1) {
  return ClassElementorTest::$functions->add_action($a, $b, $c, $d);
}

function apply_filters($a, $b) {
  return ClassElementorTest::$functions->apply_filters($a, $b);
}

function do_action($a, ...$b) {
  return ClassElementorTest::$functions->do_action($a, ...$b);
}

function wp_get_upload_dir() {
  return [
    'baseurl' => 'https://test.test/uploads'
  ];
}

function ud_get_stateless_media() {
  return WPStatelessStub::instance();
}

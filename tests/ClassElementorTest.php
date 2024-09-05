<?php

namespace SLCA\Elementor;

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use wpCloud\StatelessMedia\WPStatelessStub;
use wpCloud\StatelessMedia\WP_Filesystem_Stub;

/**
 * Class ClassElementorTest
 */
class ClassElementorTest extends TestCase {

  // Adds Mockery expectations to the PHPUnit assertions count.
  use MockeryPHPUnitIntegration;

  const TEST_URL = 'https://test.test/elementor';
  const UPLOADS_URL = self::TEST_URL . '/uploads';
  const CSS_FILE = 'post-15.css';
  const SRC_URL = self::UPLOADS_URL . '/' . self::CSS_FILE;
  const DST_URL = WPStatelessStub::TEST_GS_HOST . '/' . self::CSS_FILE;
  const TEST_UPLOAD_DIR = [
    'baseurl' => self::UPLOADS_URL,
    'basedir' => '/var/www/uploads'
  ];

  public function setUp(): void {
		parent::setUp();
		Monkey\setUp();

    // WP mocks
    Functions\when('wp_get_upload_dir')->justReturn( self::TEST_UPLOAD_DIR );
    Functions\when('wp_upload_dir')->justReturn( self::TEST_UPLOAD_DIR );
    Functions\when('attachment_url_to_postid')->justReturn( 15 );
        
    // WP_Stateless mocks
    Filters\expectApplied('wp_stateless_file_name')
      ->andReturn( self::CSS_FILE );

    Filters\expectApplied('wp_stateless_handle_root_dir')
      ->andReturn( 'uploads' );

    Functions\when('ud_get_stateless_media')->justReturn( WPStatelessStub::instance() );
  }

  public function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

  public function testShouldInitModule() {
    $elementor = new Elementor();

    $elementor->module_init([]);

    self::assertNotFalse( has_action('elementor/core/files/clear_cache', [ $elementor, 'delete_elementor_files' ]) );
    self::assertNotFalse( has_action('save_post', [ $elementor, 'delete_css_files' ]) );
    self::assertNotFalse( has_action('deleted_post', [ $elementor, 'delete_css_files' ]) );
    self::assertNotFalse( has_action('sm::pre::sync::nonMediaFiles', [ $elementor, 'filter_css_file' ]) );
    
    self::assertNotFalse( has_filter('set_url_scheme', [ $elementor, 'sync_rewrite_url' ]) );
    self::assertNotFalse( has_filter('elementor/settings/general/success_response_data', [ $elementor, 'delete_global_css' ]) );
    self::assertNotFalse( has_filter('sm:sync::syncArgs', [ $elementor, 'sync_args' ]) );
    self::assertNotFalse( has_filter('sm:sync::nonMediaFiles', [ $elementor, 'get_sync_files' ]) );
  }

  public function testShouldCountHooks() {
    $elementor = new Elementor();

    Functions\expect('add_action')->times(4);
    Functions\expect('add_filter')->times(4);

    $elementor->module_init([]);
  }

  public function testShouldSyncAndRewriteUrl() {
    $elementor = new Elementor();

    Actions\expectDone('sm:sync::syncFile')->times(2);

    $this->assertEquals(
      self::DST_URL, 
      $elementor->sync_rewrite_url(self::SRC_URL, 1, 1)
    );

    ud_get_stateless_media()->set('sm.mode', 'disabled');

    $this->assertEquals(
      self::SRC_URL, 
      $elementor->sync_rewrite_url(self::SRC_URL, 1, 1)
    );
  }

  public function testShouldDeleteElementorFiles() {
    $elementor = new Elementor();

    Actions\expectDone('sm:sync::deleteFiles')->once();

    $elementor->delete_elementor_files();

    // Need any assertion, otherwise the test will be skipped
    $this->assertTrue(true);
  }

  public function testShouldDeleteCssFiles() {
    $elementor = new Elementor();

    Functions\when('current_action')->justReturn( 'deleted_post' );

    Actions\expectDone('sm:sync::deleteFile')
      ->with(self::CSS_FILE)
      ->once();

    $elementor->delete_css_files(15, null, true);

    // Need any assertion, otherwise the test will be skipped
    $this->assertTrue(true);
  }

  public function testShouldDeleteGlobalCss() {
    $elementor = new Elementor();

    Actions\expectDone('sm:sync::deleteFile')
      ->with(self::CSS_FILE)
      ->once();

    $this->assertEquals(
      self::TEST_URL, 
      $elementor->delete_global_css(self::TEST_URL, 15, null)
    );
  }

  public function testShouldFilterCssFile() {
    $elementor = new Elementor();

    /**
     * To make this fully testable need to find the way to redefine file_put_contents and check result 
     */

    $elementor->filter_css_file(self::CSS_FILE, self::SRC_URL);

    // Need any assertion, otherwise the test will be skipped
    $this->assertTrue(true);
  }
}

function file_exists() {
  return true;
}

function function_exists() {
  return true;
}

function WP_Filesystem() {
  global $wp_filesystem;
  $wp_filesystem = new WP_Filesystem_Stub();
}

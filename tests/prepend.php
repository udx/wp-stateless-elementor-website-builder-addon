<?php

namespace wpCloud\StatelessMedia {
  class Compatibility {
  }
  
  class WPStatelessStub {
  
    const TEST_GS_HOST = 'https://google.com'; 
  
    private static $instance = null;
  
    public static function instance() {
      return static::$instance ? static::$instance : static::$instance = new static;
    }
  
    public $options = [
      'sm.root_dir' => 'uploads',
      'sm.mode' => 'cdn',
    ];
  
    public function set($key, $value): void {
      $this->options[$key] = strval($value);
    }
  
    public function get($key): ?string {
      return $this->options[$key];
    }

    public function replaceable_file_types() {
      return '\.css';
    } 
  
    public function get_gs_host(): string {
      return self::TEST_GS_HOST;
    }
  }
  
  class Utility {
    public static function add_media($a, $b, $c) {}
  }  
};

namespace Elementor\Core\Files\CSS {

  class Post {
    const UPLOADS_DIR = 'elementor/';
    const DEFAULT_FILES_DIR = 'css/';

    private $id;

    public function __construct($id) {
      $this->id = $id;
    }
  
    public function get_file_name() {
      return 'post-' . $this->id . '.css';
    }
  }

  class Global_CSS {
    const UPLOADS_DIR = 'elementor/';
    const DEFAULT_FILES_DIR = 'css/';

    private $file;

    public function __construct($file) {
      $this->file = $file;
    }
  
    public function get_file_name() {
      return $this->file;
    }
  }
}

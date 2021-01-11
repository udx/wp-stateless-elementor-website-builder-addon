<?php

namespace wpCloud\StatelessMedia;

class Compatibility {
}

class WPStatelessStub {

  private static $instance = null;

  public static function instance(): self {
    return self::$instance ? self::$instance : self::$instance = new self;
  }

  public $options = [];

  public function set($key, $value): void {
    $this->options[$key] = strval($value);
  }

  public function get($key): ?string {
    return $this->options[$key];
  }

  public function get_gs_host(): string {
    return 'https://google.com';
  }
}

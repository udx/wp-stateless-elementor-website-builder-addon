<?php

/**
 * Plugin Name: WP-Stateless - Elementor Website Builder Addon
 * Plugin URI: https://stateless.udx.io/addons/elementor/
 * Description: Provides compatibility between the Elementor Website Builder and the WP-Stateless plugins.
 * Author: UDX
 * Version: 0.0.4
 * License: GPL v2 or later
 * Text Domain: wp-stateless-elementor-website-builder-addon
 * Author URI: https://udx.io
 * 
 * Copyright 2024 UDX (email: info@udx.io)
 */

namespace SLCA\Elementor;

add_action('plugins_loaded', function () {
  if (class_exists('wpCloud\StatelessMedia\Compatibility')) {
    require_once ( dirname( __FILE__ ) . '/vendor/autoload.php' );
    // Load 
    return new Elementor();
  }

  add_filter('plugin_row_meta', function ($plugin_meta, $plugin_file, $_, $__) {
    if ($plugin_file !== join(DIRECTORY_SEPARATOR, [basename(__DIR__), basename(__FILE__)])) return $plugin_meta;
    $plugin_meta[] = sprintf('<span style="color:red;">%s</span>', __('This plugin requires WP-Stateless plugin version 3.4.0 or greater to be installed and active.', 'wp-stateless-elementor-website-builder-addon'));
    return $plugin_meta;
  }, 10, 4);
});
<?php
/**
 * MIT License
 * 
 * Copyright (c) [year] [fullname]
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * @package p_wp_output_cache
 * 
 * Plugin Name: p_wp_output_cache
 * Plugin URI: https://github.com/matpoppl/p_wp_output_cache
 * Description: Caching, minifing and compressing (gzip, deflate) front pages output.
 * Version: 0.0.1
 * Author: matpoppl
 * Author URI: https://github.com/matpoppl
 * License: MIT License
 */

// prevent direct access
if (! function_exists('add_action')) {
    exit();
}

/**
 * Storage for cached output
 * @var string
 */
define('P_WP_OUTPUT_CACHE_DIR', WP_CONTENT_DIR . '/cache/p_wp_output_cache');

/**
 * Main plugin filename
 * @var string
 */
define('P_WP_OUTPUT_CACHE_FILE', __FILE__);

/**
 * Config pathname
 * @var string
 */
define('P_WP_OUTPUT_CACHE_CONFIG', __DIR__ . '/config.php');

// load config
global $p_wp_output_cache_options;
$p_wp_output_cache_options = require(P_WP_OUTPUT_CACHE_CONFIG);

require __DIR__ . '/src/main.php';
require __DIR__ . '/src/admin.php';

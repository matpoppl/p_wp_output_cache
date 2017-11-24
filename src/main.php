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
 * Get plugin option
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function p_wp_output_cache_get_option($key, $default = null)
{
    global $p_wp_output_cache_options;
    
    return isset($p_wp_output_cache_options[$key]) ? $p_wp_output_cache_options[$key] : $default;
}

/**
 * Cached file contents.
 *
 * @param boolean $verify
 *            Validate cache
 * @param string $uri
 *            Optional URI
 * @return string|NULL
 */
function p_wp_output_cache_get_pathname($verify = true, $uri = null)
{
    // config
    $lifetime = p_wp_output_cache_get_option('file_lifetime');
    
    $cached = P_WP_OUTPUT_CACHE_DIR . '/' . md5(null === $uri ? $_SERVER['REQUEST_URI'] : $uri);
    
    // validate cache
    if (! $verify || ($lifetime > 0 && is_file($cached) && filemtime($cached) + $lifetime > time())) {
        return $cached;
    }
    
    return null;
}

/**
 * Save buffer contents.
 *
 * @param string $output
 * @return string
 */
function p_wp_output_cache_buffer_handler($output)
{
    // config
    $lifetime = (int) p_wp_output_cache_get_option('file_lifetime');
    $minify = (bool) p_wp_output_cache_get_option('minify');
    
    // minify
    if ($minify) {
        $output = preg_replace('/\s*\r?\n\s*/m', ' ', $output);
    }
    
    // cache disabled
    if ($lifetime < 1) {
        return $output;
    }
    
    $use_compression = (bool) p_wp_output_cache_get_option('use_compression', false);
    
    // get cache pathname
    $cached = p_wp_output_cache_get_pathname(false);
    
    // save
    file_put_contents($cached, $output);
    
    if ($use_compression) {
        // HTTP gzip
        file_put_contents($cached . '.gz', gzencode($output, 9));
        // HTTP defalate
        file_put_contents($cached . '.def', gzcompress($output, 9));
    }
    
    // output
    return $output;
}

/**
 * Output cache contents
 */
function p_wp_output_cache_print_cache()
{
    $cached = p_wp_output_cache_get_pathname();
    
    // cache miss
    if (null === $cached) {
        return;
    }

    $lifetime = (int) p_wp_output_cache_get_option('file_lifetime', 0);
    $use_compression = (bool) p_wp_output_cache_get_option('use_compression', false);
    $encoding = null;
    
    // check browser encoding support
    if ($use_compression && isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
        $match = null;
        
        if (preg_match('/(gzip|deflate)/', $_SERVER['HTTP_ACCEPT_ENCODING'], $match)) {
            switch ($match[0]) {
                case 'gzip':
                    $encoding = 'gzip';
                    $cached .= '.gz';
                    break;
                case 'deflate':
                    $encoding = 'deflate';
                    $cached .= '.def';
                    break;
            }
        }
    }
    
    $hash = md5_file($cached);
    
    // compare browser cache
    if ($lifetime > 0) {
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $hash === $_SERVER['HTTP_IF_NONE_MATCH']) {
            header('Content-Length: 0', true, 304);
            die();
        }
    }
    
    // encoding
    if (null !== $encoding) {
        header('Content-Encoding: ' . $encoding);
    }
    
    // notify proxies
    if ($use_compression) {
        header('Vary: Content-Encoding');
    }
    
    // browser cache
    if ($lifetime > 0) {
        header('Cache-Control: public, max-age=' . $lifetime);
        header('Expires: ' . date(DATE_RFC1123, $_SERVER['REQUEST_TIME'] + $lifetime));
        header('ETag: ' . $hash);
    }
    
    // output file
    header('Content-Length: ' . filesize($cached));
    readfile($cached);
    die();
}

/**
 * Hook template_include.
 * Start output buffer.
 *
 * @param string $filename
 * @return string
 */
function p_wp_output_cache_template_include($filename)
{
    // don\'t cache logged users
    if (is_user_logged_in()) {
        return $filename;
    }
    
    $cached = p_wp_output_cache_get_pathname();
    
    if (null !== $cached) {
        return $cached;
    }
    
    ob_start('p_wp_output_cache_buffer_handler');
    
    return $filename;
}

if (! is_admin()) {
    
    p_wp_output_cache_print_cache();
    
    add_filter('template_include', 'p_wp_output_cache_template_include');
}

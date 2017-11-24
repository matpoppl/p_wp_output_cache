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
 * Set plugin option
 * 
 * @param string $key
 * @param mixed $value
 */
function p_wp_output_cache_set_option($key, $value)
{
    global $p_wp_output_cache_options;
    
    $p_wp_output_cache_options[$key] = $value;
}

/**
 * Write plugin options
 */
function p_wp_output_cache_save_options()
{
    global $p_wp_output_cache_options;
    
    file_put_contents(P_WP_OUTPUT_CACHE_CONFIG, '<?php return '.var_export($p_wp_output_cache_options, true).';');
}


/**
 * Activate plugin. Create storage directory.
 * @throws \ErrorException
 */
function p_wp_output_cache_activation()
{
    global $p_wp_output_cache_options;
    
    // minimal supported version
    if (version_compare('5.3.0', PHP_VERSION, '>')) {
        throw new \ErrorException('Unsupported PHP version');
    }
    
    // already exists
    if (is_dir(P_WP_OUTPUT_CACHE_DIR)) {
        return;
    }
    
    // create dir
    if (! mkdir(P_WP_OUTPUT_CACHE_DIR, 0755, true)) {
        throw new \ErrorException('CACHE directory creation failed');
    }
    
    $p_wp_output_cache_options = array(
        'file_lifetime' => 86400,
        'http_lifetime' => 86400,
        'use_compression' => true,
        'minify' => true,
    );
    
    p_wp_output_cache_save_options();
}

/**
 * Deactivate plugin. Remove storage direcotry.
 */
function p_wp_output_cache_deactivation()
{
    if (! is_dir(P_WP_OUTPUT_CACHE_DIR)) {
        return;
    }
    
    // Recursive contents iterator
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(P_WP_OUTPUT_CACHE_DIR, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::CURRENT_AS_FILEINFO), RecursiveIteratorIterator::CHILD_FIRST);
    
    // cleanup contents
    foreach ($iter as $fileInfo) {
        /* @var $fileInfo \SplFileInfo */
        if ($fileInfo->isDir()) {
            rmdir($fileInfo->getPathname());
        } else {
            unlink($fileInfo->getPathname());
        }
    }
    
    // remove dir
    rmdir(P_WP_OUTPUT_CACHE_DIR);
}

/**
 * Add custom links on plugins list
 */
function p_wp_output_cache_admin_init()
{
    add_filter('plugin_action_links_' . plugin_basename(P_WP_OUTPUT_CACHE_FILE), 'p_wp_output_cache_plugin_action_link');
}

/**
 * Add plugin Settings link
 *
 * @param array $links
 * @return array
 */
function p_wp_output_cache_plugin_action_link(array $links)
{
    $links[] = '<a href="' . admin_url('options-general.php?page=p_wp_output_cache') . '">' . __('Settings') . '</a>';
    
    return $links;
}

/**
 * Hook post_updated.
 * Cleanup cache after update.
 *
 * @param int $post_ID
 */
function p_wp_output_cache_post_updated($post_ID)
{
    $uri = parse_url(get_permalink($post_ID), PHP_URL_PATH);
    
    $cached = p_wp_output_cache_get_pathname(false, $uri);
    
    // cleanup cache
    is_file($cached) && unlink($cached);
    is_file($cached . '.gz') && unlink($cached . '.gz');
    is_file($cached . '.def') && unlink($cached . '.def');
}

/**
 * Hook save admin options
 * @return boolean
 */
function p_wp_output_cache_admin_post()
{
    if (! wp_verify_nonce($_POST['_wpnonce'], 'p_wp_output_cache')) {
        return false;
    }
    
    p_wp_output_cache_set_option('file_lifetime', (int) filter_input(INPUT_POST, 'file_lifetime', FILTER_VALIDATE_INT));
    p_wp_output_cache_set_option('http_lifetime', (int) filter_input(INPUT_POST, 'http_lifetime', FILTER_VALIDATE_INT));
    p_wp_output_cache_set_option('use_compression', (bool) filter_input(INPUT_POST, 'use_compression', FILTER_VALIDATE_BOOLEAN));
    p_wp_output_cache_set_option('minify', (bool) filter_input(INPUT_POST, 'minify', FILTER_VALIDATE_BOOLEAN));
    
    p_wp_output_cache_save_options();
    
    wp_redirect(admin_url('options-general.php?page=p_wp_output_cache'));
}

/**
 * Display options form
 */
function p_wp_output_cache_options_page()
{
    require __DIR__ . '/views/options_page.php';
}

/**
 * Add Settings link to Admin Menu
 */
function p_wp_output_cache_admin_menu()
{
    add_options_page(__('P Output Cache'), __('P Output Cache'), 'activate_plugins', 'p_wp_output_cache', 'p_wp_output_cache_options_page');
}

register_activation_hook(P_WP_OUTPUT_CACHE_FILE, 'p_wp_output_cache_activation');
register_deactivation_hook(P_WP_OUTPUT_CACHE_FILE, 'p_wp_output_cache_deactivation');

add_action('post_updated', 'p_wp_output_cache_post_updated');

add_action('admin_init', 'p_wp_output_cache_admin_init');
add_action('admin_menu', 'p_wp_output_cache_admin_menu');
add_action('admin_post_p_wp_output_cache', 'p_wp_output_cache_admin_post');

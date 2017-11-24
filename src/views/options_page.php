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
?>
<div class="wrap">
	<h1><?php _e('P Output Cache') ?></h1>
	<h2><?php _e('Settings') ?></h2>
	<form method="post" action="admin-post.php">
		<input type="hidden" name="action" value="p_wp_output_cache" />
		<?php wp_nonce_field('p_wp_output_cache'); ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="file_lifetime"><?php _e('Local cache lifetime in seconds') ?></label>
					</th>
					<td>
						<input type="number" min="0" step="1"
						name="file_lifetime" id="file_lifetime" required=""
						value="<?php echo esc_attr(p_wp_output_cache_get_option('file_lifetime')) ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="http_lifetime"><?php _e('HTTP cache lifetime in seconds') ?></label>
					</th>
					<td>
						<input type="number" min="0" step="1"
						name="http_lifetime" id="http_lifetime" required=""
						value="<?php echo esc_attr(p_wp_output_cache_get_option('http_lifetime')) ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="use_compression"><?php _e('Use HTTP compression (gzip, deflate)') ?></label>
					</th>
					<td>
						<?php $use_compression = (bool) p_wp_output_cache_get_option('use_compression', false); ?>
						<input type="radio" name="use_compression" id="use_compression_1"
							value="1<?php echo ($use_compression) ? '" checked="' : ''; ?>" />
						<label for="use_compression_1"><?php _e('yes') ?></label>
						
						<input type="radio" name="use_compression" id="use_compression_0"
							value="0<?php echo (! $use_compression) ? '" checked="' : ''; ?>" />
						<label for="use_compression_0"><?php _e('no') ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="minify"><?php _e('Minify HTML output') ?></label>
					</th>
					<td>
						<?php $minify = (bool) p_wp_output_cache_get_option('minify', false); ?>
						<input type="radio" name="minify" id="minify_1"
							value="1<?php echo ($minify) ? '" checked="' : ''; ?>" />
						<label for="minify_1"><?php _e('yes') ?></label>
						
						<input type="radio" name="minify" id="minify_0"
							value="0<?php echo (! $minify) ? '" checked="' : ''; ?>" />
						<label for="minify_0"><?php _e('no') ?></label>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<button type="submit" class="button button-primary"><?php _e('Save') ?></button>
		</p>
	</form>
</div>

<div class="clear"></div>
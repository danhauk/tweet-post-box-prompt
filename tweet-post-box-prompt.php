<?php
/*
Plugin Name: Tweet Post Box Prompt
Description: Show readers a prompt in the corner of their browser to easily tweet a post if they like it. Choose from a light or dark theme to match your site and customize the call to action.
Author: Dan Hauk
Version: 0.1
Author URI: http://danhauk.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if ( is_admin() ) {
	wp_enqueue_style( 'tweet-post-box-prompt-admin', plugins_url('tweet-post-box-prompt-admin.css', __FILE__) );
	add_action( 'admin_menu', 'tweet_post_box_prompt_menus' );
	add_action( 'admin_init', 'tweet_post_box_prompt_process' );
}
else {
	wp_register_style( 'tweet-post-box-prompt', plugins_url('tweet-post-box-prompt.css', __FILE__) );
	wp_enqueue_style( 'tweet-post-box-prompt' );
	wp_enqueue_script( 'tweet-post-box-prompt', plugins_url('tweet-post-box-prompt.js', __FILE__), array( 'jquery' ) );
}

// This function adds the tweet prompt box below the content
add_action( 'wp_footer', 'tweet_post_box_prompt_popup' );
function tweet_post_box_prompt_popup() {
	if ( is_single() ) {
		if ( get_option( 'tweet_post_box_prompt_theme' ) == 'dark' ) {
			$dark_theme_class = ' class="tweet-post-box-prompt-dark"';
		} else {
			$dark_theme_class = '';
		}

		echo '<div id="tweet-post-box-prompt"' . $dark_theme_class . '>
				<h4>' . get_option( 'tweet_post_box_prompt_heading' ) . '</h4>
				<p>"' . get_the_title() . '" by @' . get_option( 'tweet_post_box_prompt_username' ) . '</p>
				<a href="javascript:;" class="tweet-post-box-prompt-button" onclick="tweet_post_box_prompt_open_win(\'' . tweet_post_box_prompt_create_tweet() . '\');"><span>Tweet</span></a>
				<a href="javascript:;" class="tweet-post-box-prompt-close">Close</a>
			  </div>';
	}
}

// create the tweet intent URL
function tweet_post_box_prompt_create_tweet() {
	if ( get_option( 'tweet_post_box_prompt_shortlink' ) == 'on' ) {
		$permalink = wp_get_shortlink();
	} else {
		$permalink = get_the_permalink();
	}

	global $post;
	$tweet_link_text = urlencode( '"' . $post->post_title . '"' );

	$tweet_link = 'https://twitter.com/intent/tweet?url=' . urlencode($permalink) . '&text=' . $tweet_link_text;

	if ( get_option( 'tweetable_selections_username' ) != '' ) {
		$username = get_option( 'tweetable_selections_username' );
		$tweet_link .= '&via=' . $username;
	}

	return $tweet_link;
}


/* ==== ADMIN FUNCTIONS ==== */

// create the menu item under the "Settings" tab in /wp-admin/
function tweet_post_box_prompt_menus() {
  add_options_page('Tweet Post Box Prompt', 'Tweet Post Box Prompt', 8, 'tweetpostboxprompt', 'tweet_post_box_prompt_options');
}

// add settings link on plugin listing
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'tweet_post_box_prompt_plugin_settings' );
function tweet_post_box_prompt_plugin_settings( $links ) {
   $links[] = '<a href="'. get_admin_url(null, 'options-general.php?page=tweetpostboxprompt') .'">Settings</a>';
   return $links;
}

// here we create the options page
function tweet_post_box_prompt_options() {
?>
	<h1><?php _e( 'Tweet Post Box Prompt' ); ?></h1>

	<form method="post" action="options.php" id="options">
	
		<?php wp_nonce_field( 'update-options '); ?>
		<?php settings_fields( 'tweet-post-box-prompt-group' ); ?>

		<table class="form-table">
			<tr>
				<th><label for="tweet_post_box_prompt_username">Twitter username</label></th>
				<td>
					@<input type="text" name="tweet_post_box_prompt_username" id="tweet_post_box_prompt_username" value="<?php echo get_option( 'tweet_post_box_prompt_username' ); ?>" />
					<p class="description">Enter your Twitter username to add "via @username" to the default tweet</p>
				</td>
			</tr>
			<tr>
				<th><label for="tweet_post_box_prompt_heading">Prompt headline</label></th>
				<td>
					<input type="text" name="tweet_post_box_prompt_heading" id="tweet_post_box_prompt_heading" value="<?php echo get_option( 'tweet_post_box_prompt_heading', 'Like this post? Tweet it!' ); ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="tweet_post_box_prompt_shortlink">Use shortlink</label></th>
				<td>
					<input type="checkbox" name="tweet_post_box_prompt_shortlink" <?php if(get_option('tweet_post_box_prompt_shortlink') == 'on') { echo 'checked'; } ?>/>
					<p class="description">example: <?php echo wp_get_shortlink(1); ?></p>
				</td>
			</tr>
		</table>

		<hr>

		<h3>Choose a theme</h3>
		
		<div id="tweet-post-box-preview-light" class="tweet-post-box-prompt-theme-admin-select<?php if ( get_option( 'tweet_post_box_prompt_theme' ) == 'light' ) { echo ' tweet-post-box-preview-active'; } ?>" onclick="tweet_post_box_prompt_admin_theme('light')">
			<div class="tweet-post-box-prompt-theme-admin tweet-post-box-prompt-light-preview">
				<input type="radio" name="tweet_post_box_prompt_theme" value="light" <?php if ( get_option( 'tweet_post_box_prompt_theme', 'light' ) == 'light' ) { echo 'checked'; } ?> />
				
				<h4 class="tweet-post-box-prompt-heading"><?php echo get_option( 'tweet_post_box_prompt_heading', 'Like this post? Tweet it!' ); ?></h4>
				<p>"Your excellent post title" by @<span class="tweet-post-box-prompt-username"><?php echo get_option( 'tweet_post_box_prompt_username' ); ?></span></p>
				<a class="tweet-post-box-prompt-button"><span>Tweet</span></a>
				<a class="tweet-post-box-prompt-close">Close</a>
			</div>
		</div>

		<div id="tweet-post-box-preview-dark" class="tweet-post-box-prompt-theme-admin-select<?php if ( get_option( 'tweet_post_box_prompt_theme' ) == 'dark' ) { echo ' tweet-post-box-preview-active'; } ?>" onclick="tweet_post_box_prompt_admin_theme('dark')">
			<div class="tweet-post-box-prompt-theme-admin tweet-post-box-prompt-dark-preview">
				<input type="radio" name="tweet_post_box_prompt_theme" value="dark" <?php if ( get_option( 'tweet_post_box_prompt_theme' ) == 'dark' ) { echo 'checked'; } ?> />
				
				<h4 class="tweet-post-box-prompt-heading"><?php echo get_option( 'tweet_post_box_prompt_heading', 'Like this post? Tweet it!' ); ?></h4>
				<p>"Your excellent post title" by @<span class="tweet-post-box-prompt-username"><?php echo get_option( 'tweet_post_box_prompt_username' ); ?></span></p>
				<a class="tweet-post-box-prompt-button"><span>Tweet</span></a>
				<a class="tweet-post-box-prompt-close">Close</a>
			</div>
		</div>

		<br><br>
		<hr>

		<input type="hidden" name="action" value="update" />

	    <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" /></p>

	</form>

	<script>
	function tweet_post_box_prompt_admin_theme( theme ) {
		var tweet_post_box_preview = document.getElementById( 'tweet-post-box-preview-' + theme );
		var tweet_post_box_preview_radios = document.getElementsByName( 'tweet_post_box_prompt_theme' );
		var tweet_post_box_preview_sibling,
		    tweet_post_box_preview_check,
		    tweet_post_box_preview_uncheck;

		if ( theme == 'light' ) {
			tweet_post_preview_sibling = document.getElementById( 'tweet-post-box-preview-dark' );
			tweet_post_box_preview_check = tweet_post_box_preview_radios[0];
			tweet_post_box_preview_uncheck = tweet_post_box_preview_radios[1];
		} else {
			tweet_post_preview_sibling = document.getElementById( 'tweet-post-box-preview-light' );
			tweet_post_box_preview_check = tweet_post_box_preview_radios[1];
			tweet_post_box_preview_uncheck = tweet_post_box_preview_radios[0];
		}

		tweet_post_box_preview.className = tweet_post_box_preview.className + ' tweet-post-box-preview-active';
		tweet_post_preview_sibling.className = 'tweet-post-box-prompt-theme-admin-select';
		tweet_post_box_preview_check.setAttribute( 'checked', '1' );
		tweet_post_box_preview_uncheck.removeAttribute( 'checked' );
	}

	var prompt_heading = document.getElementById( 'tweet_post_box_prompt_heading' );
	prompt_heading.onblur = function() {
		var prompt_heading_val = prompt_heading.value;
		for( var els = document.getElementsByTagName( 'h4' ), i = 0; i < els.length; i++ ) {
			if ( els[i].className.indexOf( 'tweet-post-box-prompt-heading' ) > -1 ) {
				els[i].innerHTML = prompt_heading_val;
			}
		}
	}

	var prompt_username = document.getElementById( 'tweet_post_box_prompt_username' );
	prompt_username.onblur = function() {
		var prompt_username_val = prompt_username.value;
		for( var els = document.getElementsByTagName( 'span' ), i = 0; i < els.length; i++ ) {
			if ( els[i].className.indexOf( 'tweet-post-box-prompt-username' ) > -1 ) {
				els[i].innerHTML = prompt_username_val;
			}
		}
	}
	</script>

<?php

}

// save the admin options
function tweet_post_box_prompt_process() { // whitelist options
  register_setting( 'tweet-post-box-prompt-group', 'tweet_post_box_prompt_username' );
  register_setting( 'tweet-post-box-prompt-group', 'tweet_post_box_prompt_heading' );
  register_setting( 'tweet-post-box-prompt-group', 'tweet_post_box_prompt_shortlink' );
  register_setting( 'tweet-post-box-prompt-group', 'tweet_post_box_prompt_theme' );
}
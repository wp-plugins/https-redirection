<?php
/*
Plugin Name: HTTPS Redirection
Plugin URI:  
Description: The plugin HTTPS Redirection allows an automatic redirection to the "HTTPS" version/URL of the site.
Author: Tips and Tricks HQ
Version: 1.1
Author URI: http://www.tipsandtricks-hq.com/
License: GPLv2 or later
*/

/*  © Copyright 2014

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! function_exists( 'add_httpsrdrctn_admin_menu' ) ) {
	function add_httpsrdrctn_admin_menu() {
		add_submenu_page( 'options-general.php', 'HTTPS Redirection', 'HTTPS Redirection', 'manage_options', 'https-redirection', 'httpsrdrctn_settings_page', plugins_url( "images/px.png", __FILE__ ), 1001 );
	}
}


if ( ! function_exists ( 'httpsrdrctn_plugin_init' ) ) {
	function httpsrdrctn_plugin_init() {
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'https_redirection', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists ( 'httpsrdrctn_plugin_admin_init' ) ) {
	function httpsrdrctn_plugin_admin_init() {
 		global $httpsrdrctn_plugin_info;

 		$httpsrdrctn_plugin_info = get_plugin_data( __FILE__, false );

		/* Check version on WordPress */
		httpsrdrctn_version_check();

		/* Call register settings function */
		if ( isset( $_GET['page'] ) && "https-redirection" == $_GET['page'] )
			register_httpsrdrctn_settings();
	}
}

/* Function check if plugin is compatible with current WP version  */
if ( ! function_exists ( 'httpsrdrctn_version_check' ) ) {
	function httpsrdrctn_version_check() {
		global $wp_version, $httpsrdrctn_plugin_info;
		$require_wp		=	"3.5"; /* Wordpress at least requires version */
		$plugin			=	plugin_basename( __FILE__ );
	 	if ( version_compare( $wp_version, $require_wp, "<" ) ) {
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
				wp_die( "<strong>" . $httpsrdrctn_plugin_info['Name'] . " </strong> " . __( 'requires', 'https_redirection' ) . " <strong>WordPress " . $require_wp . "</strong> " . __( 'or higher, that is why it has been deactivated! Please upgrade WordPress and try again.', 'https_redirection') . "<br /><br />" . __( 'Back to the WordPress', 'https_redirection') . " <a href='" . get_admin_url( null, 'plugins.php' ) . "'>" . __( 'Plugins page', 'https_redirection') . "</a>." );
			}
		}
	}
}

/* register settings function */
if ( ! function_exists( 'register_httpsrdrctn_settings' ) ) {
	function register_httpsrdrctn_settings() {
		global $wpmu, $httpsrdrctn_options, $httpsrdrctn_plugin_info;

		$httpsrdrctn_option_defaults = array(
			'https'					=> 0,
			'https_domain'	=> 0,
			'https_pages_array' => array(),
			'plugin_option_version' => $httpsrdrctn_plugin_info["Version"]
		);

		/* Install the option defaults */
		if ( 1 == $wpmu ) {
			if ( ! get_site_option( 'httpsrdrctn_options' ) )
				add_site_option( 'httpsrdrctn_options', $httpsrdrctn_option_defaults, '', 'yes' );
		} else {
			if ( ! get_option( 'httpsrdrctn_options' ) )
				add_option( 'httpsrdrctn_options', $httpsrdrctn_option_defaults, '', 'yes' );
		}

		/* Get options from the database */
		if ( 1 == $wpmu )
			$httpsrdrctn_options = get_site_option( 'httpsrdrctn_options' );
		else
			$httpsrdrctn_options = get_option( 'httpsrdrctn_options' );

		/* Array merge incase this version has added new options */
		if ( ! isset( $httpsrdrctn_options['plugin_option_version'] ) || $httpsrdrctn_options['plugin_option_version'] != $httpsrdrctn_plugin_info["Version"] ) {
			$httpsrdrctn_options = array_merge( $httpsrdrctn_option_defaults, $httpsrdrctn_options );
			$httpsrdrctn_options['plugin_option_version'] = $httpsrdrctn_plugin_info["Version"];
			update_option( 'httpsrdrctn_options', $httpsrdrctn_options );
		}
	}
}

if ( ! function_exists( 'httpsrdrctn_plugin_action_links' ) ) {
	function httpsrdrctn_plugin_action_links( $links, $file ) {
		/* Static so we don't call plugin_basename on every plugin row. */
		static $this_plugin;
		if ( ! $this_plugin )
			$this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="admin.php?page=https-redirection">' . __( 'Settings', 'https_redirection' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}
}
/* End function httpsrdrctn_plugin_action_links */

if ( ! function_exists( 'httpsrdrctn_register_plugin_links' ) ) {
	function httpsrdrctn_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			$links[] = '<a href="admin.php?page=https-redirection">' . __( 'Settings', 'https_redirection' ) . '</a>';
		}
		return $links;
	}
}

/* Function for display htaccess settings page in the admin area */
if ( ! function_exists( 'httpsrdrctn_settings_page' ) ) {
	function httpsrdrctn_settings_page() {
		global $httpsrdrctn_admin_fields_enable, $httpsrdrctn_options;
		//global $wp_rewrite; echo "<pre>"; var_dump($wp_rewrite);
		$error = "";
		/* Save data for settings page */
		if ( isset( $_REQUEST['httpsrdrctn_form_submit'] ) && check_admin_referer( plugin_basename(__FILE__), 'httpsrdrctn_nonce_name' ) ) {
			$httpsrdrctn_options['https'] = isset( $_REQUEST['httpsrdrctn_https'] ) ? $_REQUEST['httpsrdrctn_https'] : 0 ;
			$httpsrdrctn_options['https_domain'] = isset( $_REQUEST['httpsrdrctn_https_domain'] ) ? $_REQUEST['httpsrdrctn_https_domain'] : 0 ;
			
			if( isset( $_REQUEST['httpsrdrctn_https_pages_array'] ) ){
				$httpsrdrctn_options['https_pages_array'] = array();
				//var_dump($httpsrdrctn_options['https_pages_array']);
				foreach( $_REQUEST['httpsrdrctn_https_pages_array'] as $httpsrdrctn_https_page ){
					if( ! empty( $httpsrdrctn_https_page ) && $httpsrdrctn_https_page != '' ){
						$httpsrdrctn_https_page = str_replace( 'https', 'http', $httpsrdrctn_https_page );
						$httpsrdrctn_options['https_pages_array'][] = trim( str_replace( home_url(), '', $httpsrdrctn_https_page ), '/' );
					}
				}
			}

			if ( "" == $error ) {
				/* Update options in the database */
				update_option( 'httpsrdrctn_options', $httpsrdrctn_options, '', 'yes' );
				$message = __( "Settings saved.", 'https_redirection' );
				httpsrdrctn_generate_htaccess();
			}
		}
		/* Display form on the setting page */
		?>
		<div class="wrap">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2><?php _e( 'HTTPS Redirection Settings', 'https_redirection' ); ?></h2>
			<div class="error">
				<p><strong><?php _e( "Notice:", 'https_redirection' ); ?></strong> <?php _e( "It is very important to be extremely attentive when making changes to .htaccess file. This functionality will work if any permalinks except the default ones are set on the Settings -> Permalink page.", 'https_redirection' ); ?></p>
				<p><?php _e( "If after making changes your site stops functioning, please open .htaccess file in the root directory and delete this lines", 'https_redirection' ); ?>:<br />
				&lt;IfModule mod_rewrite.c&gt;<br />
				RewriteEngine On<br />
				RewriteCond %{THE_REQUEST} ^[A-Z]{3,9}\ /(.*)\ HTTP/ [NC]<br />
				RewriteCond %{HTTPS} !=on [NC]<br />
				RewriteRule ^/?(<?php _e( "in this line may be differences", 'https_redirection' ); ?>) [R,L] (or [R=301,QSA,L])<br />
				&lt;/IfModule&gt;<br />
				<?php _e( "Save file. Deactivate the plugin or rename the plugin folder.", 'https_redirection' ); ?></p>
				<p><?php _e( 'The changes will be applied immediately after saving the changes, if you are not sure - do not click the "Save changes" button.', 'https_redirection' ); ?></p>
			</div>
			<div id="httpsrdrctn_settings_notice" class="updated fade" style="display:none"><p><strong><?php _e( "Notice:", 'https_redirection' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'https_redirection' ); ?></p></div>
			<div class="updated fade" <?php if ( ! isset( $_REQUEST['httpsrdrctn_form_submit'] ) || $error != "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>><p><?php echo $error; ?></p></div>
			<form id="httpsrdrctn_settings_form" method="post" action="admin.php?page=https-redirection">
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Automatic redirection to the "HTTPS"', 'https_redirection' ); ?></th>
						<td>
							<label><input type="checkbox" name="httpsrdrctn_https" value="1" <?php if ( '1' == $httpsrdrctn_options['https'] ) echo "checked=\"checked\" "; ?>/></label><br />
							<label <?php if( '0' == $httpsrdrctn_options['https'] ) echo 'class="hidden"'; ?>><input type="radio" name="httpsrdrctn_https_domain" value="1" <?php if ( '1' == $httpsrdrctn_options['https_domain'] ) echo "checked=\"checked\" "; ?>/> The whole domain</label><br />
							<label <?php if( '0' == $httpsrdrctn_options['https'] ) echo 'class="hidden"'; ?>><input type="radio" name="httpsrdrctn_https_domain" value="0" <?php if ( '0' == $httpsrdrctn_options['https_domain'] ) echo "checked=\"checked\" "; ?>/> A few pages</label><br />
							<?php foreach( $httpsrdrctn_options['https_pages_array'] as $https_page ){ ?>
								<span class="<?php if( '1' == $httpsrdrctn_options['https_domain'] || '0' == $httpsrdrctn_options['https'] ) echo 'hidden'; ?>" >
									<?php echo str_replace( "http://", "https://", home_url() ); ?>/<input type="text" name="httpsrdrctn_https_pages_array[]" value="<?php echo $https_page; ?>" /> <span class="rewrite_delete_item">&nbsp;</span> <span class="rewrite_item_blank_error"><?php _e( 'Please, fill field', 'list' ); ?></span><br />
								</span>
							<?php } ?>
							<span class="rewrite_new_item <?php if( '1' == $httpsrdrctn_options['https_domain'] || '0' == $httpsrdrctn_options['https'] ) echo 'hidden'; ?>" >
								<?php echo str_replace( "http://", "https://", home_url() ); ?>/<input type="text" name="httpsrdrctn_https_pages_array[]" value="" /> <span class="rewrite_add_item">&nbsp;</span> <span class="rewrite_item_blank_error"><?php _e( 'Please, fill field', 'list' ); ?></span><br />
							</span>
						</td>
					</tr>
				</table>
				<input type="hidden" name="httpsrdrctn_form_submit" value="submit" />
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
				</p>
				<?php wp_nonce_field( plugin_basename(__FILE__), 'httpsrdrctn_nonce_name' ); ?>
			</form>
		</div>
	<?php }
}

if ( ! function_exists ( 'httpsrdrctn_mod_rewrite_rules' ) ) {
	function httpsrdrctn_mod_rewrite_rules( $rules ) {
		global $httpsrdrctn_options, $wpmu;
		if( empty( $httpsrdrctn_options ) ){
			if ( 1 == $wpmu )
				$httpsrdrctn_options = get_site_option( 'httpsrdrctn_options' );
			else
				$httpsrdrctn_options = get_option( 'httpsrdrctn_options' );
		}
		$home_path = get_home_path();
		if ( ! file_exists( $home_path . '.htaccess' ) ) {
			if( $httpsrdrctn_options['https'] == '1' ){
				$rewrite_https_content = '<IfModule mod_rewrite.c>' . "\n";
				$rewrite_https_content .= 'RewriteEngine On' . "\n";
				if( '1' == $httpsrdrctn_options['https_domain'] ){
					$rewrite_https_content .= 'RewriteCond %{HTTPS} !=on' . "\n";
					$rewrite_https_content .= 'RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R,L]' . "\n";
				}
				elseif( '0' == $httpsrdrctn_options['https_domain'] && ! empty( $httpsrdrctn_options['https_pages_array'] ) ){
					$rewrite_https_content .= 'RewriteCond %{THE_REQUEST} ^[A-Z]{3,9}\ /(.*)\ HTTP/ [NC]' . "\n";
					$rewrite_https_content .= 'RewriteCond %{HTTPS} !=on [NC]' . "\n";
					$rewrite_https_content .= 'RewriteRule ^/?(';

					foreach( $httpsrdrctn_options['https_pages_array'] as $https_page ){
						$rewrite_https_content .= str_replace( '/', '\/', $https_page ) . '|';
					}
					$rewrite_https_content .= trim( $rewrite_https_content, '|' );
					$rewrite_https_content .= ').* https://' . $_SERVER['SERVER_NAME'] . '%{REQUEST_URI}%{QUERY_STRING} [R=301,QSA,L]' . "\n";
				}
				$rewrite_https_content .= "</IfModule>\n";
				$rules = $rewrite_https_content . $rules;
			}
		}
		return $rules;
	}
}

if ( ! function_exists ( 'httpsrdrctn_generate_htaccess' ) ) {
	function httpsrdrctn_generate_htaccess() {
		global $httpsrdrctn_options;
		$home_path = get_home_path();
		$htaccess_file = $home_path . '.htaccess';
		if ( file_exists( $htaccess_file ) ) {
			$handle = fopen( $htaccess_file, "r" );
			if ( $handle ) {
				$previous_line = $content = $current_line = '';
				$rewrite_flag				=	false;
				$write_rewrite_flag		=	false;
			if( $httpsrdrctn_options['https'] == '1' || $httpsrdrctn_options['https'] == '0' ){
					while ( ! feof( $handle ) ) {
						$current_line = fgets( $handle );
						if ( false !== stripos( $current_line, 'RewriteCond %{HTTPS} !=on [NC]' ) ) {
							$rewrite_flag = true;
						} else {
							if ( $rewrite_flag && ! $write_rewrite_flag ) {
								$write_rewrite_flag = true;
								if( $httpsrdrctn_options['https'] == '0' ){
									$current_line = ' ';
								}
								else{
									if( '1' == $httpsrdrctn_options['https_domain'] ){
										$home = trim( trim( trim( home_url(), 'http://' ), 'https://'), '/' );
										if( $_SERVER['SERVER_NAME'] != $home && ! empty( $home ) ){
											$url_segments = explode( '/', $home );
											unset( $url_segments[ array_search( $_SERVER['SERVER_NAME'], $url_segments ) ] );
											$url_segments = implode( '/', $url_segments );
											$url_segments .= '/';
										}
										else{
											$url_segments = '';
										}
										$current_line = 'RewriteRule ^/?(.*) https://%{SERVER_NAME}/'.$url_segments.'$1 [R,L]' . "\n";
									}
									elseif( '0' == $httpsrdrctn_options['https_domain'] ){
										if( ! empty( $httpsrdrctn_options['https_pages_array'] ) ){
											$current_line = 'RewriteRule ^/?(';

											foreach( $httpsrdrctn_options['https_pages_array'] as $https_page ){
												$current_line .= str_replace( '/', '\/', $https_page ) . '|';
											}
											$current_line = trim( $current_line, '|' );
											$current_line .= ').* https://' . $_SERVER['SERVER_NAME'] . '%{REQUEST_URI}%{QUERY_STRING} [R=301,QSA,L]' . "\n";
										}
										else{
											$current_line = ' ';
										}
									}
								}
							}
						}
						$content .= trim( $current_line, "\n" ) . "\n";
					}
					if ( ! $rewrite_flag ) {
						$rewrite_https_content = '<IfModule mod_rewrite.c>' . "\n";
						$rewrite_https_content .= 'RewriteEngine On' . "\n";
						if( '1' == $httpsrdrctn_options['https_domain'] ){
							$rewrite_https_content .= 'RewriteCond %{HTTPS} !=on [NC]' . "\n";
							$home = trim( trim( trim( home_url(), 'http://' ), 'https://'), '/' );
							if( $_SERVER['SERVER_NAME'] != $home && ! empty( $home ) ){
								$url_segments = explode( '/', $home );
								unset( $url_segments[ array_search( $_SERVER['SERVER_NAME'], $url_segments ) ] );
								$url_segments = implode( '/', $url_segments );
								$url_segments .= '/';
							}
							else{
								$url_segments = '';
							}
							$rewrite_https_content .= 'RewriteRule ^/?(.*) https://%{SERVER_NAME}/'.$url_segments.'$1 [R,L]' . "\n";
						}
						elseif( '0' == $httpsrdrctn_options['https_domain'] ){
							$rewrite_https_content .= 'RewriteCond %{THE_REQUEST} ^[A-Z]{3,9}\ /(.*)\ HTTP/ [NC]' . "\n";
							$rewrite_https_content .= 'RewriteCond %{HTTPS} !=on [NC]' . "\n";
							if( ! empty( $httpsrdrctn_options['https_pages_array'] ) ){
								$rewrite_https_content .= 'RewriteRule ^/?(';

								foreach( $httpsrdrctn_options['https_pages_array'] as $https_page ){
									$rewrite_https_content .= str_replace( '/', '\/', $https_page ) . '|';
								}
								$rewrite_https_content = trim( $rewrite_https_content, '|' );
								$rewrite_https_content .= ').* https://' . $_SERVER['SERVER_NAME'] . '%{REQUEST_URI}%{QUERY_STRING} [R=301,QSA,L]' . "\n";
							}
							else{
								$rewrite_https_content .= ' ' . "\n";
							}
						}
						$rewrite_https_content .= "</IfModule>" . "\n";
						$content = $rewrite_https_content . "\n" . $content;
					}
					$temp_file = tempnam( '/tmp','allow_' );
					$fp = fopen( $temp_file, 'w' );
					fwrite( $fp, $content );
					fclose( $fp );
					rename( $temp_file, $htaccess_file );
				}
			}
			fclose( $handle );
		} else {
			/**/
		}
	}
}

if ( ! function_exists ( 'httpsrdrctn_admin_head' ) ) {
	function httpsrdrctn_admin_head() {
		if ( isset( $_REQUEST['page'] ) && 'https-redirection' == $_REQUEST['page'] ) {
			wp_enqueue_style( 'httpsrdrctn_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			wp_enqueue_script( 'httpsrdrctn_script', plugins_url( 'js/script.js', __FILE__ ) );
		}
	}
}

/* Function for delete delete options */
if ( ! function_exists ( 'httpsrdrctn_delete_options' ) ) {
	function httpsrdrctn_delete_options() {
		delete_option( 'httpsrdrctn_options' );
		delete_site_option( 'httpsrdrctn_options' );
	}
}

add_action( 'admin_menu', 'add_httpsrdrctn_admin_menu' );
add_action( 'init', 'httpsrdrctn_plugin_init' );
add_action( 'admin_init', 'httpsrdrctn_plugin_admin_init' );
add_action( 'admin_enqueue_scripts', 'httpsrdrctn_admin_head' );

/* Adds "Settings" link to the plugin action page */
add_filter( 'plugin_action_links', 'httpsrdrctn_plugin_action_links', 10, 2 );
/* Additional links on the plugin page */
add_filter( 'plugin_row_meta', 'httpsrdrctn_register_plugin_links', 10, 2 );
add_filter( 'mod_rewrite_rules', 'httpsrdrctn_mod_rewrite_rules' );

register_uninstall_hook( __FILE__, 'httpsrdrctn_delete_options' );

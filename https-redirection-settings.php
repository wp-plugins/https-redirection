<?php

/* Function for display htaccess settings page in the admin area */

function httpsrdrctn_settings_page() {
    global $httpsrdrctn_admin_fields_enable, $httpsrdrctn_options;
    //global $wp_rewrite; echo "<pre>"; var_dump($wp_rewrite);
    $error = "";
    /* Save data for settings page */
    if (isset($_REQUEST['httpsrdrctn_form_submit']) && check_admin_referer(plugin_basename(__FILE__), 'httpsrdrctn_nonce_name')) {
        $httpsrdrctn_options['https'] = isset($_REQUEST['httpsrdrctn_https']) ? $_REQUEST['httpsrdrctn_https'] : 0;
        $httpsrdrctn_options['https_domain'] = isset($_REQUEST['httpsrdrctn_https_domain']) ? $_REQUEST['httpsrdrctn_https_domain'] : 0;
        $httpsrdrctn_options['force_resources'] = isset($_REQUEST['httpsrdrctn_force_resources']) ? $_REQUEST['httpsrdrctn_force_resources'] : 0;

        if (isset($_REQUEST['httpsrdrctn_https_pages_array'])) {
            $httpsrdrctn_options['https_pages_array'] = array();
            //var_dump($httpsrdrctn_options['https_pages_array']);
            foreach ($_REQUEST['httpsrdrctn_https_pages_array'] as $httpsrdrctn_https_page) {
                if (!empty($httpsrdrctn_https_page) && $httpsrdrctn_https_page != '') {
                    $httpsrdrctn_https_page = str_replace('https', 'http', $httpsrdrctn_https_page);
                    $httpsrdrctn_options['https_pages_array'][] = trim(str_replace(home_url(), '', $httpsrdrctn_https_page), '/');
                }
            }
        }

        if ("" == $error) {
            /* Update options in the database */
            update_option('httpsrdrctn_options', $httpsrdrctn_options, '', 'yes');
            $message = __("Settings saved.", 'https_redirection');
            $httpsrdrctn_obj = new HTTPSRDRCTN_RULES();
            $httpsrdrctn_obj->write_to_htaccess();
            //httpsrdrctn_generate_htaccess();
        }
    }
    /* Display form on the setting page */
?>
        <div class="wrap">
            <div class="icon32 icon32-bws" id="icon-options-general"></div>
            <h2><?php _e('HTTPS Redirection Settings', 'https_redirection'); ?></h2>
<?php if ( get_option('permalink_structure') ) { ?>
            <div class="error">
                <p><strong><?php _e("Notice:", 'https_redirection'); ?></strong> <?php _e("It is very important to be extremely attentive when making changes to .htaccess file.", 'https_redirection'); ?></p>
                <p><?php _e("If after making changes your site stops functioning, please open .htaccess file in the root directory of the WordPress install and delete everything between the following two lines", 'https_redirection'); ?>:</p>
                <p style="border: 1px solid #ccc; padding: 10px;">
                    # BEGIN HTTPS Redirection Plugin<br />
                    # END HTTPS Redirection Plugin                  
                </p>
                <p><?php _e("Save file. Deactivate the plugin or rename the plugin folder.", 'https_redirection'); ?></p>
                
                <p><?php _e('The changes will be applied immediately after saving the changes, if you are not sure - do not click the "Save changes" button.', 'https_redirection'); ?></p>
            </div>
            <div id="httpsrdrctn_settings_notice" class="updated fade" style="display:none"><p><strong><?php _e("Notice:", 'https_redirection'); ?></strong> <?php _e("The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'https_redirection'); ?></p></div>
            <div class="updated fade" <?php if (!isset($_REQUEST['httpsrdrctn_form_submit']) || $error != "") echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
            <div class="error" <?php if ("" == $error) echo "style=\"display:none\""; ?>><p><?php echo $error; ?></p></div>
            <form id="httpsrdrctn_settings_form" method="post" action="">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Automatic redirection to the "HTTPS"', 'https_redirection'); ?></th>
                        <td>
                            <label><input type="checkbox" name="httpsrdrctn_https" value="1" <?php if ('1' == $httpsrdrctn_options['https']) echo "checked=\"checked\" "; ?>/></label><br />
                            <p class="description">Use this option to make your webpage(s) load in HTTPS version only. If someone enters a non-https URL in the browser's address bar then the plugin will automatically redirect to the HTTPS version of that URL.</p>

                            <br />
                            <p>You can apply a force HTTPS redirection on your entire domain or just a few pages.</p>
                            <label <?php if ('0' == $httpsrdrctn_options['https']) echo 'class="hidden"'; ?>><input type="radio" name="httpsrdrctn_https_domain" value="1" <?php if ('1' == $httpsrdrctn_options['https_domain']) echo "checked=\"checked\" "; ?>/> The whole domain</label><br />
                            <label <?php if ('0' == $httpsrdrctn_options['https']) echo 'class="hidden"'; ?>><input type="radio" name="httpsrdrctn_https_domain" value="0" <?php if ('0' == $httpsrdrctn_options['https_domain']) echo "checked=\"checked\" "; ?>/> A few pages</label><br />
    <?php foreach ($httpsrdrctn_options['https_pages_array'] as $https_page) { ?>
                                    <span class="<?php if ('1' == $httpsrdrctn_options['https_domain'] || '0' == $httpsrdrctn_options['https']) echo 'hidden'; ?>" >
        <?php echo str_replace("http://", "https://", home_url()); ?>/<input type="text" name="httpsrdrctn_https_pages_array[]" value="<?php echo $https_page; ?>" /> <span class="rewrite_delete_item">&nbsp;</span> <span class="rewrite_item_blank_error"><?php _e('Please, fill field', 'list'); ?></span><br />
                                    </span>
    <?php } ?>
                            <span class="rewrite_new_item <?php if ('1' == $httpsrdrctn_options['https_domain'] || '0' == $httpsrdrctn_options['https']) echo 'hidden'; ?>" >
    <?php echo str_replace("http://", "https://", home_url()); ?>/<input type="text" name="httpsrdrctn_https_pages_array[]" value="" /> <span class="rewrite_add_item">&nbsp;</span> <span class="rewrite_item_blank_error"><?php _e('Please, fill field', 'list'); ?></span><br />
                            </span>                                                        
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Force resources to use HTTPS URL', 'https_redirection'); ?></th>
                        <td>
                            <label><input type="checkbox" name="httpsrdrctn_force_resources" value="1" <?php if (isset($httpsrdrctn_options['force_resources']) && $httpsrdrctn_options['force_resources'] == '1') echo "checked=\"checked\" "; ?>/></label><br />
                            <p class="description">When checked, the plugin will force load HTTPS URL for any static resources in your content. Example: if you have have an image embedded in a post with a NON-HTTPS URL, this option will change that to a HTTPS URL.</p>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="httpsrdrctn_form_submit" value="submit" />
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                </p>
    <?php wp_nonce_field(plugin_basename(__FILE__), 'httpsrdrctn_nonce_name'); ?>
            </form>
<?php } else { ?>
<div class="error">
<p><?php _e('HTTPS redirection only works if you have pretty permalinks enabled.', 'https_redirection'); ?></p>
<p><?php _e('To enable pretty permalinks go to <em>Settings > Permalinks</em> and select any option other than "default".', 'https_redirection'); ?></p>
<p><a href="options-permalink.php">Enable Permalinks</a></p>
</div>
<?php } ?>
        </div>
    <?php
}

=== HTTPS Redirection ===
Contributors: Tips and Tricks HQ
Donate link: http://www.tipsandtricks-hq.com/development-center
Tags: redirection, https, automatic redirection, htaccess, ssl, https redirection, ssl certificate, secure page, secure
Requires at least: 3.5
Tested up to: 3.9.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin HTTPS Redirection allows an automatic redirection to the "HTTPS" version/URL of the site.

== Description ==

After you install SSL certificate on your site you want to use the "HTTPS" URL of your webpages. 

This plugin will help you automatically setup a redirection to the https version of an URL when anyone tries to access the non-https version.

Lets say for example, you want to use HTTPS URL for the following page on your site:

example.com/checkout

This plugin will enforce that so if anyone uses an URL like the following in the browser's address bar:
http://www.example.com/checkout 

It will automatically redirect to the following HTTPS version of the page:
https://www.example.com/checkout

So you are always forcing the visitor to view the HTTPS version of the page or site in question.

= Features =

* Actions: Do an auto redirect for the whole domain. So every URL will be redirected to the HTTPS version automatically.
* Actions: Do an auto redirect for a few pages. The user can enter the URLs that will be auto redirected to the HTTPS version.

== Installation ==

1. Upload `https-redirection` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Plugin settings are located in 'Settings', 'HTTPS Redirection'.

== Frequently Asked Questions ==

= How will the plugin work with the existing .htaccess file?=

If the file exists, the plugin will update existing .htaccess file.

= What should I do if the .htaccess file does not exist? =

The plugin will store the settings in the database and add all the necessary conditions to the settings of WordPress automatically.

= What should I do if after making changes in the .htaccess file with the help of the plugin my site stops working? =

The.htaccess is located in the site root. With your FTP program or via Сpanel go to the site root, open the .htaccess file and delete the necessary strings manually.
Please make use of the following information: http://codex.wordpress.org/FTP_Clients

= How to use the other language files with the HTTPS Redirection? = 

Here is an example for German language files.

1. In order to use another language for WordPress it is necessary to set the WP version to the required language and in configuration wp file - `wp-config.php` in the line `define('WPLANG', '');` write `define('WPLANG', 'de_DE');`. If everything is done properly the admin panel will be in German.

2. Make sure that there are files `de_DE.po` and `de_DE.mo` in the plugin (the folder languages in the root of the plugin).

3. If there are no such files it will be necessary to copy other files from this folder (for example, for Russian or Italian language) and rename them (you should write `de_DE` instead of `ru_RU` in the both files).

4. The files are edited with the help of the program Poedit - http://www.poedit.net/download.php - please load this program, install it, open the file with the help of this program (the required language file) and for each line in English you should write translation in German.

5. If everything has been done properly all the lines will be in German in the admin panel and on frontend.

== Screenshots ==

1. Plugin settings page.

== Changelog ==

= v1.0 =
* First commit to WordPress repository

== Upgrade Notice ==

None
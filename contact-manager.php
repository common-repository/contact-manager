<?php
/*
Plugin Name: Contact Manager
Plugin URI: https://www.kleor.com/contact-manager/
Description: Allows you to create and manage your contact forms and messages.
Version: 8.6.4
Author: Kleor
Author URI: https://www.kleor.com
Text Domain: contact-manager
Domain Path: /languages
License: GPL2
*/

/* 
Copyright 2012 Kleor (https://www.kleor.com)

This program is a free software. You can redistribute it and/or 
modify it under the terms of the GNU General Public License as 
published by the Free Software Foundation, either version 2 of 
the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, 
but without any warranty, without even the implied warranty of 
merchantability or fitness for a particular purpose. See the 
GNU General Public License for more details.
*/


if (!defined('ABSPATH')) { exit(); }
define('CONTACT_MANAGER_PATH', plugin_dir_path(__FILE__));
define('CONTACT_MANAGER_URL', plugin_dir_url(__FILE__));
define('CONTACT_MANAGER_FOLDER', substr(plugin_basename(__FILE__), 0, -strlen('/contact-manager.php')));
if (!defined('CONTACT_MANAGER_CUSTOM_FILES_PATH')) { define('CONTACT_MANAGER_CUSTOM_FILES_PATH', substr(CONTACT_MANAGER_PATH, 0, -(strlen(CONTACT_MANAGER_FOLDER) + 1)).'contact-manager-custom-files/'); }
define('CONTACT_MANAGER_CUSTOM_FILES_URL', site_url().'/'.substr(CONTACT_MANAGER_CUSTOM_FILES_PATH, strlen(ABSPATH)));
$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
define('CONTACT_MANAGER_VERSION', $plugin_data['Version']);
if (!function_exists('kleor_add_option')) { include_once contact_path('libraries/options-functions.php'); }
if (!defined('HOME_URL')) { define('HOME_URL', home_url()); }
if (!defined('ROOT_URL')) { $url = explode('/', str_replace('//', '||', HOME_URL)); define('ROOT_URL', str_replace('||', '//', $url[0])); }
if (!defined('HOME_PATH')) { $path = substr(HOME_URL, strlen(ROOT_URL)); define('HOME_PATH', ($path == '' ? '/' : $path)); }
if (!defined('UTC_OFFSET')) { define('UTC_OFFSET', kleor_get_option('gmt_offset')); }
if (!defined('CONTACT_MANAGER_UNAUTHORIZED_EXTENSIONS')) { define('CONTACT_MANAGER_UNAUTHORIZED_EXTENSIONS', 'phar,php,php0,php1,php2,php3,php4,php5,php6,php7,php8,php9,phps,php-s,pht,phtml'); }

function contact_path($file) { return (file_exists(CONTACT_MANAGER_CUSTOM_FILES_PATH.$file) ? CONTACT_MANAGER_CUSTOM_FILES_PATH : CONTACT_MANAGER_PATH).$file; }

function contact_url($file) { return (file_exists(CONTACT_MANAGER_CUSTOM_FILES_PATH.$file) ? CONTACT_MANAGER_CUSTOM_FILES_URL : CONTACT_MANAGER_URL).$file; }

if (!function_exists('kleor_fix_url')) { include_once contact_path('libraries/formatting-functions.php'); }
if (!function_exists('kleor_do_shortcode')) { include_once contact_path('libraries/shortcodes-functions.php'); }
if (is_admin()) { include_once contact_path('admin.php'); }

function install_contact_manager($context = '') { include contact_path('includes/install.php'); }

function activate_contact_manager() { install_contact_manager('activation'); }

register_activation_hook(__FILE__, 'activate_contact_manager');

global $wpdb;
$contact_manager_options = (array) kleor_get_option('contact_manager');

kleor_fix_url();
if (!is_admin()) {
@session_start();
$key = 'personal_informations_'.$_SERVER['REMOTE_ADDR'];
$_SESSION[$key] = (array) (isset($_SESSION[$key]) ? $_SESSION[$key] : array());
include contact_path('libraries/personal-informations.php');
foreach ($personal_informations as $field) {
if (isset($_GET[$field])) { $_SESSION[$key][$field] = $_GET[$field]; }
elseif (isset($_GET[str_replace('_', '-', $field)])) { $_SESSION[$key][$field] = $_GET[str_replace('_', '-', $field)]; } }
if ((isset($_SERVER['HTTP_CF_IPCOUNTRY'])) && (!isset($_SESSION[$key]['country_code']))) {
$_SESSION[$key]['country_code'] = substr(preg_replace('/[^A-Z]/', '', strtoupper($_SERVER['HTTP_CF_IPCOUNTRY'])), 0, 2); }
session_write_close(); }


function add_contact_form_in_posts($content) { include contact_path('includes/add-contact-form-in-posts.php'); return $content; }

foreach (array('get_the_content', 'the_content') as $function) { add_filter($function, 'add_contact_form_in_posts'); }


function add_message($message) { include contact_path('includes/add-message.php'); }


function contact_autoresponders() { include contact_path('includes/autoresponders.php'); return $autoresponders; }


function contact_sort_array_by_keys($array) {
$formatted_keys = array(); foreach ($array as $key => $value) { $formatted_keys[$key] = kleor_format_nice_name($key); }
asort($formatted_keys);
$sorted_array = array(); foreach ($formatted_keys as $key => $value) { $sorted_array[$key] = $array[$key]; }
return $sorted_array; }

add_filter('contact_autoresponders', 'contact_sort_array_by_keys', 100);


function contact_cron() { include contact_path('includes/cron.php'); }

if ((!defined('KLEOR_DEMO')) || (KLEOR_DEMO == false)) { add_action('init', 'contact_cron'); }


function contact_data($atts) { include contact_path('includes/data.php'); return $data; }


function contact_decimals_data($decimals, $data) { include contact_path('includes/decimals-data.php'); return $data; }


function contact_decrypt_url($url) { $action = 'decrypt'; include contact_path('includes/crypt-url.php'); return $url; }


function contact_encrypt_url($url) { $action = 'encrypt'; include contact_path('includes/crypt-url.php'); return $url; }


function contact_excerpt($data, $length = 80) {
$data = html_entity_decode((string) $data);
if (strlen($data) > $length) { $data = substr($data, 0, ($length - 4)).' […]'; }
return $data; }


function contact_filter_data($filter, $data) { include contact_path('includes/filter-data.php'); return $data; }


function contact_format_data($field, $data) { include contact_path('includes/format-data.php'); return $data; }


function contact_forms_categories_list($id) { include contact_path('includes/categories-list.php'); return $list; }


function contact_hash($string) { include contact_path('includes/hash.php'); return $hash; }


function contact_i18n($string) { load_contact_textdomain(); return __(__($string), 'contact-manager'); }


function contact_init_instructions() {
if ((isset($_GET['plugin'])) && ($_GET['plugin'] == 'contact-manager') && ((isset($_GET['action'])) || (isset($_GET['url'])))) {
if ((isset($_GET['action'])) && ($_GET['action'] == 'preview')) { include contact_path('includes/preview.php'); }
else { include contact_path('index.php'); exit(); } } }

add_action('init', 'contact_init_instructions', 1, 0);


function contact_user_data($atts) { include contact_path('includes/user-data.php'); return $data; }


function contact_item_data($type, $atts) { include contact_path('includes/item-data.php'); return $data; }


function contact_form_data($atts) {
if ((is_array($atts)) && (!isset($atts[0]))) { include_once contact_path('forms.php'); return contact_form($atts); }
elseif ((is_array($atts)) && (!isset($atts['id'])) && (isset($atts['category']))) { return contact_form_category_data($atts); }
else { return contact_item_data('contact_form', $atts); } }


function contact_form_category_data($atts) {
if ((is_array($atts)) && (isset($atts['category']))) { $atts['id'] = $atts['category']; }
return contact_item_data('contact_form_category', $atts); }


function message_data($atts) {
return contact_item_data('message', $atts); }


function contact_mail($sender, $receiver, $subject, $body, $attachments = array()) { include contact_path('includes/mail.php'); }


function contact_mysqli_connect() { include contact_path('includes/mysqli-connect.php'); return $link; }


function contact_sql_array($table, $array) { include contact_path('includes/sql-array.php'); return $sql; }


function load_contact_textdomain($domain = '') {
$domain = 'contact-manager'.($domain == '' ? '' : '-'.$domain);
$file = $domain.'-'.apply_filters('plugin_locale', get_locale(), $domain).'.mo';
if (load_textdomain($domain, contact_path('languages/'.$file))) { return true; }
else { return load_textdomain($domain, WP_LANG_DIR.'/plugins/'.$file); } }


$tags = array();
foreach (array('contact-content', 'contact-counter', 'contact-form-counter') as $tag) {
$function = function($atts, $content) use($tag) { include_once contact_path("shortcodes.php"); $function2 = str_replace('-', '_', $tag); return @$function2($atts, $content); };
for ($i = 0; $i < 4; $i++) { $tags[] = $tag.($i == 0 ? '' : $i); add_shortcode($tag.($i == 0 ? '' : $i), $function); } }
$tags[] = 'user'; add_shortcode('user', 'contact_user_data');
$tags[] = 'contact-manager'; add_shortcode('contact-manager', 'contact_data');
foreach (array(
'contact-form-category',
'contact-form',
'message') as $tag) { $tags[] = $tag; add_shortcode($tag, str_replace('-', '_', $tag).'_data'); }
$tags[] = 'sender'; add_shortcode('sender', 'message_data');
$contact_manager_shortcodes = $tags;


function replace_contact_manager_shortcodes($data) { include contact_path('includes/replace-shortcodes.php'); return $data; }

add_filter('wp_insert_post_data', 'replace_contact_manager_shortcodes', 10, 1);


foreach (array(
'get_the_excerpt',
'get_the_title',
'single_post_title',
'the_excerpt',
'the_excerpt_rss',
'the_title',
'the_title_attribute',
'the_title_rss',
'widget_text',
'widget_title') as $function) { add_filter($function, 'do_shortcode'); }
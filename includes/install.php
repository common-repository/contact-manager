<?php if (!defined('ABSPATH')) { exit(); }
global $wpdb;
include_once ABSPATH.'wp-admin/includes/upgrade.php';
$results = $wpdb->query("SET @@innodb_strict_mode = OFF");
$charset_collate = '';
if (!empty($wpdb->charset)) { $charset_collate .= 'DEFAULT CHARACTER SET '.$wpdb->charset; }
if (!empty($wpdb->collate)) { $charset_collate .= ' COLLATE '.$wpdb->collate; }
include contact_path('tables.php');
foreach ($tables as $table_slug => $table) {
$list = ''; foreach ($table as $key => $value) { $list .= "
".$key." ".$value['type'].(strstr($value['type'], 'int') ? " unsigned" : "")." ".($key == "id" ? "auto_increment" : "NOT NULL").","; }
$sql = "CREATE TABLE ".$wpdb->prefix."contact_manager_".$table_slug." (".$list."
PRIMARY KEY  (id)) $charset_collate;"; dbDelta($sql); }

$cron = (array) kleor_get_option('contact_manager_cron');
$version = (isset($cron['first_installation']) ? $cron['first_installation']['version'] : '');
if (($version != '') && (version_compare($version, '8.0', '<'))) {
include contact_path('libraries/questions.php');
add_option('contact_manager_answers', $default_answers); }

$first_installation = (!kleor_get_option('contact_manager'));
load_contact_textdomain();
include contact_path('initial-options.php');
$overwrited_options = array('menu_title_'.$lang, 'meta_box_'.$lang, 'pages_titles_'.$lang, 'version');
foreach ($initial_options as $key => $value) {
$_key = ($key == '' ? '' : '_'.$key);
if (is_array($value)) {
$options = (array) kleor_get_option('contact_manager'.$_key);
$current_options = $options;
if ((isset($options[0])) && ($options[0] === false)) { unset($options[0]); }
foreach ($value as $option => $initial_value) {
if ((!isset($options[$option])) || ($options[$option] == '') || (in_array($option, $overwrited_options))) { $options[$option] = $initial_value; } }
if ($options != $current_options) { kleor_update_option('contact_manager'.$_key, $options); } }
else { kleor_add_option(substr('contact_manager'.$_key, 0, 64), $value, in_array($key, array('', 'cron'))); } }

$wp_roles = new WP_Roles();
foreach ($wp_roles->role_objects as $key => $role) {
if (($key == 'administrator') || ($role->has_cap('activate_plugins'))) {
foreach (array('manage', 'view') as $string) { $role->add_cap($string.'_contact_manager'); } } }

$current_time = time();

if ((!defined('KLEOR_DEMO')) || (KLEOR_DEMO == false)) {
if ($first_installation) {
$form = array(
'name' => __('Contact', 'contact-manager'),
'date' => date('Y-m-d H:i:s', $current_time + 3600*UTC_OFFSET),
'date_utc' => date('Y-m-d H:i:s', $current_time));
$sql = contact_sql_array($tables['forms'], $form);
$keys_list = ''; $values_list = '';
foreach ($tables['forms'] as $key => $value) { if ($key != 'id') { $keys_list .= $key.","; $values_list .= $sql[$key].","; } }
$results = $wpdb->query("INSERT INTO ".$wpdb->prefix."contact_manager_forms (".substr($keys_list, 0, -1).") VALUES(".substr($values_list, 0, -1).")"); } }

if (($context == 'activation') || (!isset($cron['previous_activation'])) || ($cron['previous_activation']['version'] == '')) {
$cron['previous_activation'] = array('version' => CONTACT_MANAGER_VERSION, 'timestamp' => $current_time); }
if ((!isset($cron['first_installation'])) || ($cron['first_installation']['version'] == '')) {
$cron['first_installation'] = array('version' => CONTACT_MANAGER_VERSION, 'timestamp' => $current_time); }
if ((!isset($cron['previous_installation'])) || ($cron['previous_installation']['version'] != CONTACT_MANAGER_VERSION)) {
$cron['previous_installation'] = array('version' => CONTACT_MANAGER_VERSION, 'number' => 1); }
else { $cron['previous_installation']['number'] = $cron['previous_installation']['number'] + 1; }
$cron['previous_installation']['timestamp'] = $current_time;
kleor_update_option('contact_manager_cron', $cron);
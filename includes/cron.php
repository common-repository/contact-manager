<?php if (!defined('ABSPATH')) { exit(); }
$cron = kleor_get_option('contact_manager_cron');
if ($cron) {
$current_time = time();
$installation = (array) $cron['previous_installation'];
if ($installation['version'] != CONTACT_MANAGER_VERSION) {
$cron['previous_installation'] = array('version' => CONTACT_MANAGER_VERSION, 'number' => 0, 'timestamp' => $current_time); }
elseif (($installation['number'] < 12) && (($current_time - $installation['timestamp']) >= pow(2, $installation['number'] + 2))) {
$cron['previous_installation']['timestamp'] = $current_time; }
if ($cron['previous_installation'] != $installation) { kleor_update_option('contact_manager_cron', $cron); install_contact_manager(); } }
else { install_contact_manager(); }
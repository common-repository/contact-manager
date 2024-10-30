<?php if (!defined('ABSPATH')) { exit(); }
global $contact_manager_options;
if ((empty($contact_manager_options)) || (!is_array($contact_manager_options)) || (!isset($contact_manager_options['version']))) {
$contact_manager_options = (array) kleor_get_option('contact_manager');
$GLOBALS['contact_manager_options'] = $contact_manager_options; }
if (is_string($atts)) { $field = $atts; $decimals = ''; $default = ''; $filter = ''; $formatting = 'yes'; $part = 0; }
else {
$atts = array_map('kleor_do_shortcode_in_attribute', (array) $atts);
$field = (isset($atts[0]) ? $atts[0] : '');
foreach (array('decimals', 'default', 'filter') as $key) {
$$key = (isset($atts[$key]) ? $atts[$key] : '');
if (isset($atts[$key])) { unset($atts[$key]); } }
$formatting = (((isset($atts['formatting'])) && ($atts['formatting'] == 'no')) ? 'no' : 'yes');
$part = (int) (isset($atts['part']) ? preg_replace('/[^0-9]/', '', $atts['part']) : 0); }
$field = str_replace('-', '_', kleor_format_nice_name($field));
if (($field == 'code') || (substr($field, -10) == 'email_body') || (substr($field, -19) == 'custom_instructions')) {
$data = kleor_get_option(substr('contact_manager_'.$field, 0, 64)); }
else { $data = (isset($contact_manager_options[$field]) ? $contact_manager_options[$field] : ''); }
if ($part > 0) { $data = explode(',', $data); $data = (isset($data[$part - 1]) ? trim($data[$part - 1]) : ''); }
$data = (string) ($formatting == 'yes' ? kleor_do_shortcode($data) : $data);
if (($data === '') && (function_exists('commerce_data'))) {
include contact_path('libraries/api-fields.php');
if (in_array($field, $api_fields)) { $data = commerce_data($atts); } }
if ($data === '') { $data = $default; }
if ($formatting == 'yes') {
$data = contact_format_data($field, $data);
if ($data === '') { $data = $default; } }
$data = contact_filter_data($filter, $data);
$data = contact_decimals_data($decimals, $data);
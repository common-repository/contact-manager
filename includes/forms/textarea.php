<?php if (!defined('ABSPATH')) { exit(); }
$form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
$atts = kleor_shortcode_atts(array(
0 => 'content',
'cols' => '60',
'onblur' => '',
'onkeyup' => '',
'required' => 'no',
'rows' => '15'), $atts);
$markup = '';
$name = str_replace('-', '_', kleor_format_nice_name($atts[0]));
if (!isset($atts['disabled'])) { $GLOBALS[$prefix.'fields'][] = $name; }
$main_name = (((substr($name, 0, 8) == 'confirm_') && (in_array(substr($name, 8), $GLOBALS[$prefix.'fields']))) ? substr($name, 8) : $name);
if ($main_name != $name) { $GLOBALS[$prefix.'confirmed_fields'][] = $main_name; }
if ((in_array($name, $GLOBALS[$prefix.'required_fields'])) && ($atts['required'] != 'required')) { $atts['required'] = 'yes'; }
if ($name == 'email_address') {
foreach (array('onblur', 'onkeyup') as $key) { if ($atts[$key] == '') { $atts[$key] = "this.value = kleor_format_email_address(this.value);"; } }
if (isset($_POST[$prefix.'submit'])) {
if ((isset($_POST[$prefix.$name])) && ($_POST[$prefix.$name] != '') && ((!strstr($_POST[$prefix.$name], '@')) || (!strstr($_POST[$prefix.$name], '.')))) {
$GLOBALS[$prefix.$name.'_error'] = 'invalid_email_address'; } } }
if ((!isset($_POST[$prefix.'submit'])) && ((!isset($_POST[$prefix.$name])) || ($_POST[$prefix.$name] == ''))) { $_POST[$prefix.$name] = kleor_do_shortcode($content); }
foreach (array($name, str_replace('_', '-', $name)) as $key) {
if (((!isset($_POST[$prefix.$name])) || ($_POST[$prefix.$name] == '')) && (isset($_GET[$key]))) { $_POST[$prefix.$name] = htmlspecialchars($_GET[$key]); } }
if ((!isset($_POST[$prefix.'submit'])) && ((!isset($_POST[$prefix.$name])) || ($_POST[$prefix.$name] == ''))
 && (function_exists('current_user_can')) && (!current_user_can('edit_pages')) && (!current_user_can('manage_options'))) {
include contact_path('libraries/personal-informations.php');
if (in_array($name, $personal_informations)) {
if ((function_exists('affiliation_session')) && (affiliation_session()) && (affiliate_data($name) != '')) { $_POST[$prefix.$name] = affiliate_data($name); }
elseif ((function_exists('commerce_session')) && (commerce_session()) && (client_data($name) != '')) { $_POST[$prefix.$name] = client_data($name); }
elseif ((function_exists('membership_session')) && (membership_session()) && (member_data($name) != '')) { $_POST[$prefix.$name] = member_data($name); }
elseif ((function_exists('is_user_logged_in')) && (is_user_logged_in())) { $_POST[$prefix.$name] = contact_user_data($name); }
elseif (isset($_SESSION['personal_informations_'.$_SERVER['REMOTE_ADDR']][$name])) { $_POST[$prefix.$name] = htmlspecialchars($_SESSION['personal_informations_'.$_SERVER['REMOTE_ADDR']][$name]); } } }
if (isset($_POST[$prefix.'submit'])) {
if (($name != $main_name) && (isset($_POST[$prefix.$name])) && (isset($_POST[$prefix.$main_name])) && ($_POST[$prefix.$name] != $_POST[$prefix.$main_name])) { $GLOBALS[$prefix.$name.'_error'] = 'invalid_field'; }
if ((isset($_POST[$prefix.$name])) && ($_POST[$prefix.$name] != '')) {
if ((isset($atts['pattern'])) && ($atts['pattern'] != '')
 && (!in_array($_POST[$prefix.$name], preg_grep('#'.str_replace('#', '\#', $atts['pattern']).'#', array($_POST[$prefix.$name]))))) { $GLOBALS[$prefix.$name.'_error'] = 'invalid_field'; } }
elseif (in_array($atts['required'], array('required', 'yes'))) { $GLOBALS[$prefix.$name.'_error'] = 'unfilled_field'; } }
if (((!isset($GLOBALS['form_focus'])) || ($GLOBALS['form_focus'] == '')) && ((!isset($_POST[$prefix.$name])) || ($_POST[$prefix.$name] == ''))) { $GLOBALS['form_focus'] = $prefix.$name; }
foreach ($atts as $key => $value) {
switch ($key) {
case 'required': if (in_array($value, array('required', 'yes'))) {
$GLOBALS[$prefix.'required_fields'][] = $name; if ($value == $key) { $markup .= ' '.$key.'="'.$key.'"'; } } break;
default: if ((!in_array($key, array('id', 'name'))) && (is_string($key)) && ($value != '')) { $c = (strstr($value, '"') ? "'" : '"'); $markup .= ' '.$key.'='.$c.$value.$c; } } }
if (isset($GLOBALS[$prefix.$name.'_error'])) { $GLOBALS['form_error'] = 'yes'; }
$content = '<textarea name="'.$prefix.$name.'" id="'.$prefix.$name.'"'.$markup.'>'.(isset($_POST[$prefix.$name]) ? str_ireplace('</textarea>', '', $_POST[$prefix.$name]) : '').'</textarea>';
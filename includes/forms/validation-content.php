<?php if (!defined('ABSPATH')) { exit(); }
$atts = array_map('kleor_do_shortcode_in_attribute', (array) $atts);
extract(shortcode_atts(array('filter' => ''), $atts));
$form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
$content = explode('[other]', kleor_do_shortcode($content));
if (!isset($_POST[$prefix.'submit'])) { $n = 2; }
elseif ((isset($GLOBALS['form_error'])) && ($GLOBALS['form_error'] == 'yes')) { $n = 1; }
else { $n = 0; }
if (!isset($content[$n])) { $content[$n] = ''; }
$content = contact_filter_data($filter, $content[$n]);
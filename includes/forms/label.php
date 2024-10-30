<?php if (!defined('ABSPATH')) { exit(); }
$form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
$atts = kleor_shortcode_atts(array(0 => 'email_address'), $atts);
$markup = '';
foreach ($atts as $key => $value) {
if (($key != 'for') && (is_string($key)) && ($value != '')) { $c = (strstr($value, '"') ? "'" : '"'); $markup .= ' '.$key.'='.$c.$value.$c; } }
$name = str_replace('-', '_', kleor_format_nice_name($atts[0]));
$content = '<label for="'.$prefix.$name.'"'.$markup.'>'.kleor_do_shortcode($content).'</label>';
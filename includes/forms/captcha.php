<?php if (!defined('ABSPATH')) { exit(); }
$form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
$atts = kleor_shortcode_atts(array(
'class' => 'captcha',
'theme' => contact_data('default_'.(((isset($atts['type'])) && ($atts['type'] == 'hcaptcha')) ? 'h' : 're').'captcha_theme'),
'type' => contact_data('default_captcha_type')), $atts);
if ((isset($atts['answer'])) && (isset($atts['question']))) { $atts['type'] = 'question'; }
$markup = '';
foreach ($atts as $key => $value) {
if ((!in_array($key, array('answer', 'question', 'theme', 'type'))) && (is_string($key)) && ($value != '')) { $c = (strstr($value, '"') ? "'" : '"'); $markup .= ' '.$key.'='.$c.$value.$c; } }
if (($atts['type'] != 'recaptcha3') && (substr($atts['type'], 0, 9) == 'recaptcha')) { $atts['type'] = 'recaptcha'; }
if (in_array($atts['type'], array('recaptcha', 'recaptcha3'))) {
$GLOBALS[$prefix.'recaptcha'] = true;
$v = ($atts['type'] == 'recaptcha' ? '' : '3');
$GLOBALS[$prefix.'recaptcha_version'] = $v;
foreach (array('public', 'private') as $string) {
if (!defined('RECAPTCHA'.$v.'_'.strtoupper($string).'_KEY')) {
$key = contact_data('recaptcha'.$v.'_'.$string.'_key');
define('RECAPTCHA'.$v.'_'.strtoupper($string).'_KEY', $key); } }
add_action('wp_footer', function() use($v) { echo '<script src="https://www.google.com/recaptcha/api.js?'.($v == '3' ? 'render='.RECAPTCHA3_PUBLIC_KEY : 'hl='.strtolower(substr(get_locale(), 0, 2))).'" async defer></script>'; });
$content = ($v == '3' ? '<div><input type="hidden" name="g-recaptcha-response" id="'.$prefix.'recaptcha" /></div>'
 : '<div class="g-recaptcha" data-sitekey="'.RECAPTCHA_PUBLIC_KEY.'" data-theme="'.$atts['theme'].'"></div>'); }
elseif ($atts['type'] == 'hcaptcha') {
$GLOBALS[$prefix.'hcaptcha'] = true;
foreach (array('public', 'private') as $string) {
if (!defined('HCAPTCHA_'.strtoupper($string).'_KEY')) {
$key = contact_data('hcaptcha_'.$string.'_key');
define('HCAPTCHA_'.strtoupper($string).'_KEY', $key); } }
add_action('wp_footer', function() { echo '<script src="https://js.hcaptcha.com/1/api.js?hl='.strtolower(substr(get_locale(), 0, 2)).'" async defer></script>'; });
$content = '<div class="h-captcha" data-sitekey="'.HCAPTCHA_PUBLIC_KEY.'" data-theme="'.$atts['theme'].'"></div>'; }
else {
switch ($atts['type']) {
case 'arithmetic':
load_contact_textdomain();
include contact_path('libraries/captchas.php');
$m = mt_rand(0, 15);
$n = mt_rand(0, 15);
$string = $captchas_numbers[$m].' + '.$captchas_numbers[$n];
$valid_captcha = $m + $n; break;
case 'question':
$string = (isset($atts['question']) ? $atts['question'] : '');
$valid_captcha = (isset($atts['answer']) ? $atts['answer'] : ''); break;
case 'reversed-string':
include contact_path('libraries/captchas.php');
$n = mt_rand(5, 12);
$string = '';
for ($i = 0; $i < $n; $i++) { $string .= $captchas_letters[mt_rand(0, 25)]; }
$valid_captcha = strrev($string); break; }
$GLOBALS[$prefix.'valid_captcha'] = $valid_captcha;
$content = '<label for="'.$prefix.'captcha"><span'.$markup.'>'.$string.'</span></label>
<input type="hidden" name="'.$prefix.'valid_captcha" value="'.contact_hash($valid_captcha).'" />'; }
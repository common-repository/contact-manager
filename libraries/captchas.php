<?php if (!defined('ABSPATH')) { exit(); }
$captchas_types = array(
'arithmetic' => __('Arithmetic operation', 'contact-manager'),
'question' => __('Question', 'contact-manager'),
'recaptcha' => 'reCAPTCHA v2',
'recaptcha3' => 'reCAPTCHA v3',
'reversed-string' => __('Reversed string', 'contact-manager'));

$hcaptcha_themes = $recaptcha_themes = array('dark' => 'Dark', 'light' => 'Light');

$captchas_numbers = array(
0 => __('zero', 'contact-manager'),
1 => __('one', 'contact-manager'),
2 => __('two', 'contact-manager'),
3 => __('three', 'contact-manager'),
4 => __('four', 'contact-manager'),
5 => __('five', 'contact-manager'),
6 => __('six', 'contact-manager'),
7 => __('seven', 'contact-manager'),
8 => __('eight', 'contact-manager'),
9 => __('nine', 'contact-manager'),
10 => __('ten', 'contact-manager'),
11 => __('eleven', 'contact-manager'),
12 => __('twelve', 'contact-manager'),
13 => __('thirteen', 'contact-manager'),
14 => __('fourteen', 'contact-manager'),
15 => __('fifteen', 'contact-manager'));

$captchas_letters = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
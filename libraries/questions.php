<?php if (!defined('ABSPATH')) { exit(); }
$questions = array(
'website' => array('default_answer' => true, 'label' => __('Do you want to ask internet users the address of their website?', 'contact-manager')),
'attachment' => array('default_answer' => true, 'label' => __('Do you want to allow them to download an attachment?', 'contact-manager')),
'copy' => array('default_answer' => true, 'label' => __('Do you want to allow them to receive a copy of the message they just sent?', 'contact-manager')),
'captcha' => array('default_answer' => false, 'label' => __('Do you want to put a CAPTCHA in your forms to fight against SPAM?', 'contact-manager')),
'messages_registration' => array('default_answer' => false, 'label' => __('Do you want to save the messages in your database?', 'contact-manager')),
'contact_page' => array('default_answer' => false, 'label' => __('Do you want me to create a contact page for you?', 'contact-manager')));

$default_answers = array(); foreach ($questions as $key => $value) { $default_answers[$key] = $value['default_answer']; }
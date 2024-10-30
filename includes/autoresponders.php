<?php if (!defined('ABSPATH')) { exit(); }
$autoresponders = apply_filters('contact_autoresponders', array(
'AWeber' => array('function' => 'contact_subscribe_to_aweber', 'display_function' => 'contact_display_aweber', 'update_function' => 'contact_update_aweber'),
'CyberMailing' => array('function' => 'contact_subscribe_to_cybermailing', 'display_function' => 'contact_display_cybermailing'),
'GetResponse' => array('function' => 'contact_subscribe_to_getresponse', 'display_function' => 'contact_display_getresponse'),
'MailChimp' => array('function' => 'contact_subscribe_to_mailchimp', 'display_function' => 'contact_display_mailchimp'),
'MailerLite' => array('function' => 'contact_subscribe_to_mailerlite', 'display_function' => 'contact_display_mailerlite'),
'Sendinblue' => array('function' => 'contact_subscribe_to_sendinblue', 'display_function' => 'contact_display_sendinblue'),
'Sendy' => array('function' => 'contact_subscribe_to_sendy', 'display_function' => 'contact_display_sendy', 'update_function' => 'contact_update_sendy'),
'SG Autorépondeur' => array('function' => 'contact_subscribe_to_sg_autorepondeur', 'display_function' => 'contact_display_sg_autorepondeur')));
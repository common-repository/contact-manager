<?php if (!defined('ABSPATH')) { exit(); }
extract(apply_filters('contact_mail', compact('sender', 'receiver', 'subject', 'body', 'attachments')));
$html = ((strstr($body, '</')) || (strstr($body, '/>')));
foreach (array('sender', 'receiver', 'subject', 'body') as $field) {
$$field = str_replace(array("\\t", '\\', '&#91;', '&#93;'), array('	', '', '[', ']'), str_replace(array("\\r\\n", "\\n", "\\r"), '
', ((($html) && ($field == 'body')) ? $$field : str_replace(array('&lt;', '&gt;'), array('<', '>'), $$field)))); }
$sender = str_ireplace(array('reply-to:', 'reply-to :', 'replyto:', 'replyto :'), 'Reply-To:', $sender);
$array = explode('Reply-To:', $sender);
$from = $array[0]; $reply_to = (isset($array[1]) ? $array[1] : '');
foreach (array('from', 'reply_to') as $field) {
$$field = trim($$field);
while (substr($$field, 0, 1) == ',') { $$field = substr($$field, 1); }
while (substr($$field, -1) == ',') { $$field = substr($$field, 0, -1); }
$$field = trim($$field); }
$headers = 'From: '.$from.($reply_to != "" ? "\r\nReply-To: ".$reply_to : "").($html ? "\r\nContent-type: text/html" : "");
if (!wp_mail($receiver, $subject, $body, $headers, $attachments)) { mail($receiver, $subject, $body, $headers); }
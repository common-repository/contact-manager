<?php if (!defined('ABSPATH')) { exit(); }
if ((function_exists('current_user_can')) && (function_exists('user_can'))) {
if ((!current_user_can('view_contact_manager')) && (!user_can($data['post_author'], 'view_contact_manager'))) {
global $contact_manager_shortcodes;
foreach ((array) $contact_manager_shortcodes as $tag) {
foreach (array('post_content', 'post_content_filtered', 'post_excerpt', 'post_title') as $key) {
$data[$key] = str_replace(array('['.$tag, $tag.']'), array('&#91;'.$tag, $tag.'&#93;'), $data[$key]); } } }
if ((!current_user_can('list_users')) && (!user_can($data['post_author'], 'list_users'))) {
foreach (array('post_content', 'post_content_filtered', 'post_excerpt', 'post_title') as $key) {
$data[$key] = str_replace('[user', '&#91;user', $data[$key]); } } }
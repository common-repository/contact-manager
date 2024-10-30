<?php function contact_preview_page($posts) {
global $wp_query, $wpdb;
@session_start(); session_write_close();
$key = 'contact_preview_variables';
if ((!isset($_SESSION[$key])) || (!is_array($_SESSION[$key]))) { $_SESSION[$key] = array(); }
if ((isset($_GET['field'])) && ($_GET['field'] == 'code')) {
$GLOBALS['action'] = 'contact_preview';
load_contact_textdomain();
$GLOBALS['contact_form_id'] = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
if ($GLOBALS['contact_form_id'] > 0) { $GLOBALS['contact_form_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_forms WHERE id = ".$GLOBALS['contact_form_id'], OBJECT); }
else {
$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_forms ORDER BY displays_count DESC", OBJECT);
if ($item) { $GLOBALS['contact_form_id'] = $item->id; $GLOBALS['contact_form_data'] = (array) $item; }
else { $GLOBALS['contact_form_id'] = 1; $GLOBALS['contact_form_data'] = array('id' => 1, 'name' => 'Test'); } }
$_SESSION[$key]['code'] = (string) (isset($_SESSION[$key]['code']) ? $_SESSION[$key]['code'] : '');
$post = (object) array();
$post->post_title = __('Preview Virtual Page', 'contact-manager');
$post->post_content = '<p style="color: #008000;"><strong><em>'.__('As this is only a preview, the submit button doesn\'t work.', 'contact-manager').'</em></strong></p>
<div>'.do_shortcode('[contact-form id='.$GLOBALS['contact_form_id'].']').'</div>';
$wp_query->post = $post;
$wp_query->posts = array($post);
$wp_query->is_page = true;
$wp_query->is_singular = true;
$wp_query->is_single = false;
$wp_query->is_posts_page = false;
$wp_query->is_home = false;
$wp_query->is_archive = false;
$wp_query->is_category = false;
unset($wp_query->query['error']);
$wp_query->query_vars['error'] = '';
$wp_query->is_404 = false;
return array($post); } }

if (current_user_can('view_contact_manager')) { add_filter('the_posts', 'contact_preview_page'); }
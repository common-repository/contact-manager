<?php if ((isset($_GET['action'])) && (in_array($_GET['action'], array('search-automatic-display-form-id', 'search-form-id'))) && (isset($_GET['search'])) && ($_GET['search'] != '')) {
if (!headers_sent()) { header('Content-type: text/plain'); }
if (function_exists('mysqli_connect')) {
if ((!isset($_GET['lang'])) || ($_GET['lang'] == '')) { $_GET['lang'] = 'en'; }
include dirname(__FILE__).'/includes/mysqli-connect.php';
if ((isset($_GET['key'])) && ($_GET['key'] == md5(DB_NAME.DB_PASSWORD)) && (isset($link)) && ($link)) {
$field = str_replace('-', '_', substr($_GET['action'], 7));
$keys = array('name', 'description', 'keywords');
$search = str_replace("'", "''", $_GET['search']);
$search_criteria = ''; foreach ($keys as $key) { $search_criteria .= " OR ".$key." LIKE '%".$search."%'"; }
$blog_id = (int) (isset($_GET['blog_id']) ? $_GET['blog_id'] : 1);
$result = mysqli_query($link, "SELECT id,".implode(",", $keys)." FROM ".WPDB_PREFIX.($blog_id > 1 ? $blog_id.'_' : '')."contact_manager_forms WHERE (".substr($search_criteria, 4).") ORDER BY ".$keys[0]." ASC");
if (($result) && ($result->num_rows > 0) && ($result->num_rows <= 10)) {
$items = array(); while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) { $items[] = $row; }
$ids = array(); foreach ($items as $item) { $ids[] = $item['id']; }
$value = (int) (isset($_GET['value']) ? $_GET['value'] : 0);
if (!in_array($value, $ids)) { $value = $ids[0]; }
if (!function_exists('contact_excerpt')) {
function contact_excerpt($data, $length = 80) {
$data = html_entity_decode((string) $data);
if (strlen($data) > $length) { $data = substr($data, 0, ($length - 4)).' […]'; }
return $data; } }
$selector = '<select name="'.$field.'" id="'.$field.'" data-empty="no" data-changed="yes" onchange="kleor_update_form(this.form);">';
foreach ($items as $item) {
$selector .= '<option value="'.$item['id'].'"'.($value == $item['id'] ? ' selected="selected"' : '').'>'.$item['id'].' ('.htmlspecialchars(contact_excerpt($item[$keys[0]], 50)).')</option>'."\n"; }
$selector .= '</select>';
echo $selector; } }
if (isset($link)) { mysqli_close($link); } } }

elseif ((isset($_GET['action'])) && ($_GET['action'] == 'set-preview-variables')) { @session_start(); $_SESSION['contact_preview_variables'] = array_map('strval', $_POST); session_write_close(); }

elseif ((isset($_GET['action'])) || (isset($_GET['url']))) {
if (function_exists('contact_data')) {
if (isset($_GET['action'])) {
switch ($_GET['action']) {
case 'export-data':
$id = (int) preg_replace('/[^0-9]/', '', (isset($_GET['id']) ? $_GET['id'] : 0));
$type = kleor_format_nice_name((isset($_GET['type']) ? $_GET['type'] : ''));
if (($type == 'message') && ($id > 0) && (isset($_GET['key'])) && ($_GET['key'] == md5($type.$id.AUTH_KEY))) {
global $wpdb;
$file = "";
include contact_path('tables.php');
$GLOBALS[$type.'_id'] = $id;
$GLOBALS[$type.'_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_messages WHERE id = $id", OBJECT);
foreach ($tables['messages'] as $key => $value) {
if (!in_array($key, array(
'id',
'referrer',
'commission_amount',
'commission_status',
'commission_payment_date',
'commission_payment_date_utc',
'referrer2',
'commission2_amount',
'commission2_status',
'commission2_payment_date',
'commission2_payment_date_utc'))) {
$file .= $key."\t".str_replace(array("\t", "\n", "\r"), " ", $GLOBALS[$type.'_data'][$key])."\n"; } }
header("Content-type: text/csv");
header("Content-disposition: attachment; filename=message-".$_GET['key'].".csv");
print $file;
exit; } break;
case 'install': install_contact_manager(); break;
case 'update-form':
if (!headers_sent()) { header('Content-type: text/plain'); }
if ((isset($_GET['page'])) && (isset($_GET['key'])) && ($_GET['key'] == md5(AUTH_KEY))) {
if (current_user_can('view_contact_manager')) {
foreach (array('admin.php', 'admin-pages-functions.php') as $file) { include_once contact_path($file); }
$GLOBALS['action'] = 'update_admin_page_form';
function contact_update_admin_page_form() {
global $wpdb; $error = '';
$back_office_options = (array) kleor_get_option('contact_manager_back_office');
extract(contact_manager_pages_links_markups($back_office_options));
$current_time = (int) (isset($_GET['time']) ? $_GET['time'] : time());
$current_date = date('Y-m-d H:i:s', $current_time + 3600*UTC_OFFSET);
$current_date_utc = date('Y-m-d H:i:s', $current_time);
$admin_page = ($_GET['page'] == 'contact-manager' ? 'options' : str_replace('-', '_', str_replace('contact-manager-', '', kleor_format_nice_name($_GET['page']))));
$is_category = (strstr($admin_page, 'category'));
foreach (array('default_options_select_fields', 'ids_fields', 'other_options') as $variable) {
$$variable = (array) (isset($_POST[$variable]) ? $_POST[$variable] : array()); }
foreach ($_POST as $key => $value) { if (is_string($value)) { $_POST[$key] = stripslashes($value); } }
$_POST['update_fields'] = 'yes'; if (isset($_POST['submit'])) { unset($_POST['submit']); }
foreach (array('admin-pages.php', 'tables.php') as $file) { include contact_path($file); }
include contact_path('includes/update-form.php');
echo json_encode(array_map('strval', $values)); }
contact_update_admin_page_form(); } } break;
case 'update-options':
if ((isset($_GET['page'])) && (isset($_GET['key'])) && ($_GET['key'] == md5(AUTH_KEY))) {
$option_name = str_replace('-', '_', kleor_format_nice_name($_GET['page']));
$options = (array) kleor_get_option($option_name);
if (!current_user_can('manage_contact_manager')) {
if ((isset($_SESSION[$option_name.'_'.$_SERVER['REMOTE_ADDR']])) && (is_array($_SESSION[$option_name.'_'.$_SERVER['REMOTE_ADDR']]))) { $options = $_SESSION[$option_name.'_'.$_SERVER['REMOTE_ADDR']]; }
else { @session_start(); $_SESSION[$option_name.'_'.$_SERVER['REMOTE_ADDR']] = $options; session_write_close(); } }
if ($options) {
foreach ($options as $key => $value) { if (isset($_GET[$key])) { $options[$key] = stripslashes($_GET[$key]); } }
if (current_user_can('manage_contact_manager')) { kleor_update_option($option_name, $options); }
else { @session_start(); $_SESSION[$option_name.'_'.$_SERVER['REMOTE_ADDR']] = $options; session_write_close(); } } } break;
default: if (!headers_sent()) { header('Location: '.HOME_URL); exit(); } } }
elseif (isset($_GET['url'])) {
$url = contact_decrypt_url($_SERVER['REQUEST_URI']);
if (!headers_sent()) { header('Location: '.str_replace(array("'", '"', ';'), '', $url)); exit(); } } }
elseif (!headers_sent()) { header('Location: /'); exit(); } }
elseif (!headers_sent()) { header('Location: /'); exit(); }
<?php if (!defined('ABSPATH')) { exit(); }
global $wpdb; $error = '';
$back_office_options = (array) kleor_get_option('contact_manager_back_office');
extract(contact_manager_pages_links_markups($back_office_options));
$current_time = time();
$current_date = date('Y-m-d H:i:s', $current_time + 3600*UTC_OFFSET);
$current_date_utc = date('Y-m-d H:i:s', $current_time);
$admin_page = 'message';

if ((isset($_GET['id'])) && (isset($_GET['action'])) && ($_GET['action'] == 'delete')) {
if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!current_user_can('manage_contact_manager')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
$message_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_messages WHERE id = ".$_GET['id'], OBJECT);
$GLOBALS['message_data'] = (array) $message_data;
$GLOBALS['referrer'] = $GLOBALS['message_data']['referrer'];
$GLOBALS['contact_form_id'] = $GLOBALS['message_data']['form_id'];
$results = $wpdb->query("DELETE FROM ".$wpdb->prefix."contact_manager_messages WHERE id = ".$_GET['id']);
if ($message_data->form_id > 0) {
$contact_form_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_forms WHERE id = ".$message_data->form_id, OBJECT);
if ($contact_form_data) {
$GLOBALS['contact_form_data'] = (array) $contact_form_data;
$messages_count = $contact_form_data->messages_count - 1;
if ($messages_count < 0) { $messages_count = 0; }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET messages_count = ".$messages_count." WHERE id = ".$contact_form_data->id);
foreach (array('', $GLOBALS['contact_form_id']) as $string) {
$GLOBALS['contact_form'.$string.'_data'] = (array) (isset($GLOBALS['contact_form'.$string.'_data']) ? $GLOBALS['contact_form'.$string.'_data'] : array());
$GLOBALS['contact_form'.$string.'_data']['messages_count'] = $messages_count; } } }
if ((!defined('KLEOR_DEMO')) || (KLEOR_DEMO == false)) {
if (contact_data('message_removal_custom_instructions_executed') == 'yes') {
$instructions = kleor_format_instructions(contact_data('message_removal_custom_instructions'));
if (substr($instructions, -4) == '.php') {
$instructions = ABSPATH.str_replace(site_url().'/', '', $instructions);
if (file_exists($instructions)) { include $instructions; } }
else { eval($instructions); } } } } } ?>
<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if (isset($_POST['submit'])) {
echo '<div class="updated-notice"><p><strong>'.__('Message deleted.', 'contact-manager').'</strong></p></div>
<script>setTimeout(\'window.location = "admin.php?page=contact-manager-messages"\', 2000);</script>'; } ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<?php if (!isset($_POST['submit'])) { ?>
<form method="post" name="<?php echo esc_attr($_GET['page']); ?>" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<div class="alignleft actions">
<p style="font-size: 1.2em;"><strong style="color: #c00000;"><?php _e('Do you really want to permanently delete this message?', 'contact-manager'); ?></strong></p>
<p><input type="submit" class="button-secondary" name="submit" id="submit" value="<?php _e('Yes', 'contact-manager'); ?>" />
<span class="description"><?php _e('This action is irreversible.', 'contact-manager'); ?></span></p>
</div>
<div class="clear"></div>
</form><?php } ?>
</div>
</div><?php }

else {
include contact_path('admin-pages.php'); include contact_path('tables.php');
foreach ($tables['messages'] as $key => $value) { if (!isset($_POST[$key])) { $_POST[$key] = ''; } }
if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!current_user_can('manage_contact_manager')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
foreach ($_POST as $key => $value) {
if (is_string($value)) { $_POST[$key] = stripslashes(html_entity_decode(str_replace(array('&nbsp;', '&#91;', '&#93;'), array(' ', '&amp;#91;', '&amp;#93;'), $value))); } }
$back_office_options = update_contact_manager_back_office($back_office_options, 'message');
include contact_path('includes/update-form.php'); } }

if (isset($_GET['id'])) {
$message_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_messages WHERE id = ".$_GET['id'], OBJECT);
if ($message_data) {
$GLOBALS['message_data'] = (array) $message_data;
$GLOBALS['message_id'] = $message_data->id;
foreach ($message_data as $key => $value) { if ((!isset($_POST[$key])) || (!isset($_POST[$key.'_error']))) { $_POST[$key] = $value; } }
foreach (array('subject', 'content') as $field) { $_POST[$field] = str_replace(array('&lt;', '&gt;', '&#91;', '&#93;'), array('<', '>', '[', ']'), $_POST[$field]); } }
elseif (!headers_sent()) { header('Location: admin.php?page=contact-manager-messages'); exit(); }
else { echo '<script>window.location = "admin.php?page=contact-manager-messages";</script>'; } }
else { $GLOBALS['message_id'] = 0; $GLOBALS['message_data'] = array(); }

foreach ($_POST as $key => $value) {
if (is_string($value)) {
$_POST[$key] = str_replace(array('&amp;amp;', '&amp;apos;', '&amp;quot;'), array('&amp;', '&apos;', '&quot;'), htmlspecialchars(stripslashes($value)));
if (($value == '0000-00-00 00:00:00') && ((substr($key, -4) == 'date') || (substr($key, -8) == 'date_utc'))) { $_POST[$key] = ''; } } }
$undisplayed_modules = (array) $back_office_options['message_page_undisplayed_modules'];
foreach (array('ids_fields', 'urls_fields') as $variable) { $$variable = array(); }
if (function_exists('commerce_data')) { $currency_code = commerce_data('currency_code'); }
else { $commerce_manager_options = array_merge((array) kleor_get_option('commerce_manager'), (array) kleor_get_option('commerce_manager_client_area'));
$currency_code = (isset($commerce_manager_options['currency_code']) ? kleor_do_shortcode($commerce_manager_options['currency_code']) : ''); } ?>

<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if ((isset($updated)) && ($updated)) {
echo '<div class="updated-notice"><p><strong>'.(isset($_GET['id']) ? __('Message updated.', 'contact-manager') : __('Message saved.', 'contact-manager')).'</strong></p></div>
'.(isset($_GET['id']) ? '' : '<script>setTimeout(\'window.location = "admin.php?page=contact-manager-messages"\', 2000);</script>'); } ?>
<form method="post" name="<?php echo esc_attr($_GET['page']); ?>" id="<?php echo esc_attr($_GET['page']); ?>" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<?php contact_manager_pages_summary($back_office_options); ?>

<div class="postbox" id="general-informations-module"<?php if (in_array('general-informations', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="general-informations"><strong><?php echo $modules['message']['general-informations']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<?php if (isset($_GET['id'])) { echo '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="id">'.__('ID', 'contact-manager').'</label></strong></th>
<td><input type="text" name="id" id="id" size="10" value="'.$_GET['id'].'" disabled="disabled" /> <span class="description">'.__('The ID can not be changed.', 'contact-manager').'</span>
<span id="id-links">'.contact_manager_pages_field_links($back_office_options, 'id', $_GET['id']).'</span></td></tr>'; } ?>
<?php $_POST['form_id'] = (int) $_POST['form_id'];
if ($_POST['form_id'] == 0) { $item = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."contact_manager_forms ORDER BY messages_count DESC", OBJECT); if ($item) { $_POST['form_id'] = $item->id; } }
$selector = ''; $items = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."contact_manager_forms ORDER BY name ASC", OBJECT);
if (count($items) <= 10) {
$selector = '<select name="form_id" id="form_id" onchange="this.setAttribute(\'data-changed\', \'yes\'); kleor_update_form(this.form);">';
foreach ($items as $item) {
$selector .= '<option value="'.$item->id.'"'.($_POST['form_id'] == $item->id ? ' selected="selected"' : '').'>'.$item->id.' ('.htmlspecialchars(contact_excerpt(contact_form_data(array(0 => 'name', 'id' => $item->id)), 50)).')</option>'."\n"; }
$selector .= '</select>';
if (!strstr($selector, 'selected="selected"')) { $selector = ''; } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="form_id"><?php _e('Form ID', 'contact-manager'); ?></label></strong></th>
<td><span id="form_id_selector"><?php if ($selector != '') { echo $selector; } else { ?><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="form_id" id="form_id" rows="1" cols="25" onkeyup="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>if (this.value != '') { kleor_update_form(this.form); }" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['form_id']; ?></textarea>
<span class="description" style="vertical-align: 25%;" id="form-id-description"><?php echo contact_manager_pages_field_description('form_id', $_POST['form_id']); ?></span><?php } ?></span>
<span id="form-id-links"><?php $ids_fields[] = 'form_id'; echo contact_manager_pages_field_links($back_office_options, 'form_id', $_POST['form_id']); ?></span>
<?php if ($selector == '') { ?><br /><textarea style="margin-top: 0.5em; padding: 0 0.25em; height: 1.75em; width: 75%;" name="form_id_search" id="form_id_search" rows="1" cols="75" onkeyup="if (this.value != '') { search_form_id(this.form); }" onchange="search_form_id(this.form);" placeholder="<?php _e('Search a form (name, description or keywords)', 'contact-manager'); ?>"></textarea>
<?php } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="receiver"><?php _e('Receiver', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="receiver" id="receiver" rows="1" cols="75" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['receiver']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="subject"><?php _e('Subject', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="subject" id="subject" rows="1" cols="75" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['subject']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="content"><?php _e('Content', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="content" id="content" rows="10" cols="75" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['content']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="keywords"><?php _e('Keywords', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="keywords" id="keywords" rows="1" cols="75" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['keywords']; ?></textarea><br />
<span class="description"><?php _e('Separate the keywords with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="date"><?php _e('Date', 'contact-manager'); ?></label></strong></th>
<td><input class="date-pick" type="text" name="date" id="date" size="20" value="<?php echo ($_POST['date'] != '' ? $_POST['date'] : $current_date); ?>" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);" /></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="sender-module"<?php if (in_array('sender', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="sender"><strong><?php echo $modules['message']['sender']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<?php if (isset($_GET['id'])) { echo '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><a href="'.htmlspecialchars(HOME_URL.'?plugin=contact-manager&action=export-data&type=message&id='.$_GET['id'].'&key='.md5('message'.$_GET['id'].AUTH_KEY)).'">'.__('Download personal data', 'contact-manager').'</a></td></tr>'; } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="first_name"><?php _e('First name', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="first_name" id="first_name" rows="1" cols="50" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['first_name']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="last_name"><?php _e('Last name', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="last_name" id="last_name" rows="1" cols="50" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['last_name']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="email_address"><?php _e('Email address', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="email_address" id="email_address" rows="1" cols="50" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['email_address']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="website_name"><?php _e('Website name', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="website_name" id="website_name" rows="1" cols="50" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['website_name']; ?></textarea> 
<span id="website-name-link"><?php $url = htmlspecialchars(message_data(array(0 => 'website_url', 'part' => 1, 'id' => (isset($_GET['id']) ? $_GET['id'] : 0)))); if ($url != '') { ?><a style="vertical-align: 25%;" <?php echo $urls_fields_links_markup; ?> href="<?php echo $url; ?>"><?php _e('Link', 'contact-manager'); ?></a><?php } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="website_url"><?php _e('Website URL', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="website_url" id="website_url" rows="1" cols="75" onkeyup="kleor_update_links(this.form);" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_links(this.form); kleor_update_form(this.form);"><?php echo $_POST['website_url']; ?></textarea> 
<span id="website-url-link"><?php $urls_fields[] = 'website_url'; $url = htmlspecialchars(message_data(array(0 => 'website_url', 'part' => 1, 'id' => (isset($_GET['id']) ? $_GET['id'] : 0)))); if ($url != '') { ?><a style="vertical-align: 25%;" <?php echo $urls_fields_links_markup; ?> href="<?php echo $url; ?>"><?php _e('Link', 'contact-manager'); ?></a><?php } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="address"><?php _e('Address', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="address" id="address" rows="1" cols="50" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['address']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="postcode"><?php _e('Postcode', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="postcode" id="postcode" rows="1" cols="50" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['postcode']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="town"><?php _e('Town', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="town" id="town" rows="1" cols="50" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['town']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="country"><?php _e('Country', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="country" id="country" rows="1" cols="50" onkeyup="kleor_update_country_code(this.form);" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_country_code(this.form); kleor_update_form(this.form);"><?php echo $_POST['country']; ?></textarea>
<span style="vertical-align: 25%; margin-left: 2em;"><label style="font-weight: bold;" for="country_code"><?php _e('Country code', 'contact-manager'); ?></label> <select style="font-family: Consolas, Monaco, monospace; margin-left: 0.5em; max-width: 10em;" name="country_code" id="country_code" onkeyup="kleor_update_country(this.form);" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_country(this.form); kleor_update_form(this.form);">
<?php include contact_path('libraries/sorted-countries.php');
$countries_list = '<option value="">--</option>'."\n";
foreach ($countries as $country_code => $country) { $countries_list .= '<option style="max-width: 20em;" value="'.$country_code.'"'.($_POST['country_code'] == $country_code ? ' selected="selected"' : '').'>'.$country_code.' ('.$country.')</option>'."\n"; }
echo $countries_list; ?></select></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="phone_number"><?php _e('Phone number', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="phone_number" id="phone_number" rows="1" cols="50" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['phone_number']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="ip_address"><?php _e('IP address', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="ip_address" id="ip_address" rows="1" cols="50" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['ip_address']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="user_agent"><?php _e('Browser', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="user_agent" id="user_agent" rows="1" cols="75" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo $_POST['user_agent']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="referring_url"><?php _e('Referring URL', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="referring_url" id="referring_url" rows="1" cols="75" onkeyup="kleor_update_links(this.form);" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>if (this.value == '') { this.form.referring_url_emptied.value = 'yes'; } kleor_update_links(this.form); kleor_update_form(this.form);"><?php echo $_POST['referring_url']; ?></textarea><input type="hidden" name="referring_url_emptied" value="no" /> 
<span id="referring-url-link"><?php $urls_fields[] = 'referring_url'; $url = htmlspecialchars(message_data(array(0 => 'referring_url', 'part' => 1, 'id' => (isset($_GET['id']) ? $_GET['id'] : 0)))); if ($url != '') { ?><a style="vertical-align: 25%;" <?php echo $urls_fields_links_markup; ?> href="<?php echo $url; ?>"><?php _e('Link', 'contact-manager'); ?></a><?php } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="custom-fields-module"<?php if (in_array('custom-fields', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="custom-fields"><strong><?php echo $modules['message']['custom-fields']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager-back-office#message-page-custom-fields"><?php _e('Click here to add a new custom field.', 'contact-manager'); ?></a>
<a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#custom-fields"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php $custom_fields = (array) $back_office_options['message_page_custom_fields'];
$item_custom_fields = (array) unserialize(htmlspecialchars_decode($_POST['custom_fields']));
foreach ($custom_fields as $key => $value) { $custom_fields[$key] = kleor_do_shortcode($value); }
asort($custom_fields); $content = ''; foreach ($custom_fields as $key => $value) {
$field_value = (isset($item_custom_fields[$key]) ? $item_custom_fields[$key] : '');
if ((strlen($field_value) > 75) || (strstr($field_value, '
'))) { $rows = 3; } else { $rows = 1; }
$urls_fields[] = 'custom_field_'.$key; $applied_value = message_data(array(0 => 'custom_field_'.$key, 'part' => 1, 'id' => (isset($_GET['id']) ? $_GET['id'] : 0)));
$content .= '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="custom_field_'.$key.'">'.htmlspecialchars($value).'</label></strong></th>
<td><textarea style="padding: 0 0.25em; '.($rows == 1 ? 'height: 1.75em; ' : '').'width: 75%;" name="custom_field_'.$key.'" id="custom_field_'.$key.'" rows="'.$rows.'" cols="75" onkeyup="kleor_update_links(this.form);" onchange="'.(!isset($_GET['id']) ? "this.setAttribute('data-changed', 'yes'); " : "").'kleor_update_links(this.form); kleor_update_form(this.form);">'.htmlspecialchars($field_value).'</textarea>
<span id="custom-field-'.str_replace('_', '-', $key).'-link">'.(((!strstr($applied_value, ' ')) && (substr($applied_value, 0, 4) == 'http')) ? '<a style="vertical-align: 25%;" '.$urls_fields_links_markup.' href="'.htmlspecialchars($applied_value).'">'.__('Link', 'contact-manager').'</a>' : '').'</span></td></tr>'; }
echo $content; if ($content == '') { echo '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td>'.__('You have no custom field currently.', 'contact-manager').'</td></tr>'; } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="affiliation-module"<?php if (in_array('affiliation', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="affiliation"><strong><?php echo $modules['message']['affiliation']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('affiliation_data')) { ?>
<a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#affiliation"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a>
<?php } else { echo str_replace('<a', '<a '.$documentations_links_markup, __('To use affiliation, you must have installed and activated <a href="https://www.kleor.com/affiliation-manager/">Affiliation Manager</a>.', 'contact-manager')); } ?></span></td></tr>
</tbody></table>
<div id="level-1-commission-module"<?php if (in_array('level-1-commission', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="level-1-commission"><strong><?php echo $modules['message']['affiliation']['modules']['level-1-commission']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('The level 1 commission is awarded to the affiliate who referred the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/affiliation-manager/documentation/#commissions-levels"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="referrer"><?php _e('Referrer', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="referrer" id="referrer" rows="1" cols="50" onkeyup="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>if (this.value == '') { this.form.referrer_emptied.value = 'yes'; } kleor_update_form(this.form);" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>if (this.value == '') { this.form.referrer_emptied.value = 'yes'; } kleor_update_form(this.form);"><?php echo $_POST['referrer']; ?></textarea><input type="hidden" name="referrer_emptied" value="no" />
<span class="description" style="vertical-align: 25%;"><?php _e('Affiliate who referred this message (ID, login name or email address)', 'contact-manager'); ?></span> 
<span id="referrer-links"><?php $ids_fields[] = 'referrer'; echo contact_manager_pages_field_links($back_office_options, 'referrer', $_POST['referrer']); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission_amount"><?php _e('Amount', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="commission_amount" id="commission_amount" rows="1" cols="25" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo number_format((float) $_POST['commission_amount'], 2, '.', ''); ?></textarea> <span style="vertical-align: 25%;"><?php echo $currency_code; ?></span> 
<?php if (!isset($_GET['id'])) { echo '<span class="description noscript" style="vertical-align: 25%;">'.__('Leave this field blank to automatically calculate the amount.', 'contact-manager').'</span>'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="commission_status" id="commission_status" onchange="if (this.value == 'paid') { document.getElementById('commission-payment-date').style.display = ''; } else { document.getElementById('commission-payment-date').style.display = 'none'; } <?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);">
<option value=""<?php if ($_POST['commission_status'] == '') { echo ' selected="selected"'; } ?>><?php _e('None', 'contact-manager'); ?></option>
<option value="unpaid"<?php if ($_POST['commission_status'] == 'unpaid') { echo ' selected="selected"'; } ?>><?php _e('Unpaid', 'contact-manager'); ?></option>
<option value="paid"<?php if ($_POST['commission_status'] == 'paid') { echo ' selected="selected"'; } ?>><?php _e('Paid', 'contact-manager'); ?></option>
</select><?php if (isset($_GET['id'])) { echo '<input type="hidden" name="old_commission_status" value="'.$_POST['commission_status'].'" />'; } ?></td></tr>
<tr id="commission-payment-date" style="<?php if ($_POST['commission_status'] != 'paid') { echo 'display: none; '; } ?>vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission_payment_date"><?php _e('Payment date', 'contact-manager'); ?></label></strong></th>
<td><input class="date-pick" type="text" name="commission_payment_date" id="commission_payment_date" size="20" value="<?php echo $_POST['commission_payment_date']; ?>" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);" />
<?php if ($_POST['commission_payment_date'] == '') { echo '<span class="description noscript"><br />'.__('Leave this field blank if the commission is not paid, or for the current date if the commission is paid.', 'contact-manager').'</span>'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div>
<div id="level-2-commission-module"<?php if (in_array('level-2-commission', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="level-2-commission"><strong><?php echo $modules['message']['affiliation']['modules']['level-2-commission']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('The level 2 commission is awarded to the referrer of the affiliate who referred the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/affiliation-manager/documentation/#commissions-levels"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="referrer2"><?php _e('Referrer', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="referrer2" id="referrer2" rows="1" cols="50" onkeyup="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>if (this.value == '') { this.form.referrer2_emptied.value = 'yes'; } kleor_update_form(this.form);" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>if (this.value == '') { this.form.referrer2_emptied.value = 'yes'; } kleor_update_form(this.form);"><?php echo $_POST['referrer2']; ?></textarea><input type="hidden" name="referrer2_emptied" value="no" />
<?php if (!isset($_GET['id'])) { echo '<span class="description noscript" style="vertical-align: 25%;">'.__('Leave this field blank for the referrer of the affiliate who referred this message.', 'contact-manager').'</span>'; } ?>
<span id="referrer2-links"><?php $ids_fields[] = 'referrer2'; echo contact_manager_pages_field_links($back_office_options, 'referrer2', $_POST['referrer2']); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission2_amount"><?php _e('Amount', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="commission2_amount" id="commission2_amount" rows="1" cols="25" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);"><?php echo number_format((float) $_POST['commission2_amount'], 2, '.', ''); ?></textarea> <span style="vertical-align: 25%;"><?php echo $currency_code; ?></span> 
<?php if (!isset($_GET['id'])) { echo '<span class="description noscript" style="vertical-align: 25%;">'.__('Leave this field blank to automatically calculate the amount.', 'contact-manager').'</span>'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission2_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="commission2_status" id="commission2_status" onchange="if (this.value == 'paid') { document.getElementById('commission2-payment-date').style.display = ''; } else { document.getElementById('commission2-payment-date').style.display = 'none'; } <?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);">
<option value=""<?php if ($_POST['commission2_status'] == '') { echo ' selected="selected"'; } ?>><?php _e('None', 'contact-manager'); ?></option>
<option value="unpaid"<?php if ($_POST['commission2_status'] == 'unpaid') { echo ' selected="selected"'; } ?>><?php _e('Unpaid', 'contact-manager'); ?></option>
<option value="paid"<?php if ($_POST['commission2_status'] == 'paid') { echo ' selected="selected"'; } ?>><?php _e('Paid', 'contact-manager'); ?></option>
</select><?php if (isset($_GET['id'])) { echo '<input type="hidden" name="old_commission2_status" value="'.$_POST['commission2_status'].'" />'; } ?></td></tr>
<tr id="commission2-payment-date" style="<?php if ($_POST['commission2_status'] != 'paid') { echo 'display: none; '; } ?>vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission2_payment_date"><?php _e('Payment date', 'contact-manager'); ?></label></strong></th>
<td><input class="date-pick" type="text" name="commission2_payment_date" id="commission2_payment_date" size="20" value="<?php echo $_POST['commission2_payment_date']; ?>" onchange="<?php if (!isset($_GET['id'])) { echo "this.setAttribute('data-changed', 'yes'); "; } ?>kleor_update_form(this.form);" />
<?php if ($_POST['commission2_payment_date'] == '') { echo '<span class="description noscript"><br />'.__('Leave this field blank if the commission is not paid, or for the current date if the commission is paid.', 'contact-manager').'</span>'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div>
</div></div>

<?php if (!isset($_GET['id'])) {
if (!isset($_POST['submit'])) {
$contact_manager_options = (array) kleor_get_option('contact_manager');
foreach ($contact_manager_options as $key => $value) { if (is_string($value)) { $contact_manager_options[$key] = htmlspecialchars($value); } }
foreach ($add_message_fields as $field) { $_POST[$field] = (isset($contact_manager_options[$field]) ? $contact_manager_options[$field] : ''); }
$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', $_POST['sender_members_areas'], 0, PREG_SPLIT_NO_EMPTY)));
if (count($members_areas) == 1) { $GLOBALS['member_area_id'] = (int) $members_areas[0]; }
else { $GLOBALS['member_area_id'] = 0; $GLOBALS['member_area_data'] = array(); }
foreach (array('category_id', 'status') as $field) {
if (($_POST['sender_client_'.$field] == '') && (function_exists('commerce_data'))) { $_POST['sender_client_'.$field] = commerce_data('clients_initial_'.$field); }
if (($_POST['sender_affiliate_'.$field] == '') && (function_exists('affiliation_data'))) { $_POST['sender_affiliate_'.$field] = affiliation_data('affiliates_initial_'.$field); }
if (($_POST['sender_member_'.$field] == '') && (function_exists('member_area_data'))) { $_POST['sender_member_'.$field] = member_area_data('members_initial_'.$field); } }
foreach (array('confirmation', 'notification') as $action) {
if ($_POST['sender_subscribed_as_a_client'] != 'yes') { $_POST['commerce_registration_'.$action.'_email_sent'] = 'no'; }
elseif (($_POST['commerce_registration_'.$action.'_email_sent'] == '') && (function_exists('commerce_data'))) { $_POST['commerce_registration_'.$action.'_email_sent'] = commerce_data('registration_'.$action.'_email_sent'); }
if ($_POST['sender_subscribed_to_affiliate_program'] != 'yes') { $_POST['affiliation_registration_'.$action.'_email_sent'] = 'no'; }
elseif (($_POST['affiliation_registration_'.$action.'_email_sent'] == '') && (function_exists('affiliation_data'))) { $_POST['affiliation_registration_'.$action.'_email_sent'] = affiliation_data('registration_'.$action.'_email_sent'); }
if ($_POST['sender_subscribed_to_members_areas'] != 'yes') { $_POST['membership_registration_'.$action.'_email_sent'] = 'no'; }
elseif (($_POST['membership_registration_'.$action.'_email_sent'] == '') && (function_exists('member_area_data'))) { $_POST['membership_registration_'.$action.'_email_sent'] = member_area_data('registration_'.$action.'_email_sent'); } }
$_POST['message_notification_email_subject'] = '[message subject]'; }
foreach ($add_message_fields as $field) { if (!isset($_POST[$field])) { $_POST[$field] = ''; } }
$value = false; foreach ($add_message_modules as $module) { if (!$value) { $value = (!in_array($module, $undisplayed_modules)); } }
if ($value) { ?><p class="noscript submit" style="margin-left: 0;"><input type="hidden" name="submit" value="true" />
<input type="submit" class="button-primary" name="update_fields" formaction="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>#add-message-modules" value="<?php _e('Complete the fields below with the informations about the sender, the message and the form', 'contact-manager'); ?>" /></p><?php } ?>

<div id="add-message-modules">
<?php if (!isset($_POST['submit'])) { $_POST['message_confirmation_email_body'] = htmlspecialchars(kleor_get_option('contact_manager_message_confirmation_email_body')); } ?>
<div class="postbox" id="message-confirmation-email-module"<?php if (in_array('message-confirmation-email', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="message-confirmation-email"><strong><?php echo $modules['message']['message-confirmation-email']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#message-confirmation-email"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_confirmation_email_sent" id="message_confirmation_email_sent" value="yes"<?php if ($_POST['message_confirmation_email_sent'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes');" /> <strong><?php _e('Send a message confirmation email', 'contact-manager'); ?></strong></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_sender"><?php _e('Sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_sender" id="message_confirmation_email_sender" rows="1" cols="75" onchange="this.setAttribute('data-changed', 'yes');"><?php echo $_POST['message_confirmation_email_sender']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_receiver"><?php _e('Receiver', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_receiver" id="message_confirmation_email_receiver" rows="1" cols="75" onchange="this.setAttribute('data-changed', 'yes');"><?php echo $_POST['message_confirmation_email_receiver']; ?></textarea><br />
<span class="description"><?php _e('You can enter several email addresses. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_subject"><?php _e('Subject', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_subject" id="message_confirmation_email_subject" rows="1" cols="75" onchange="this.setAttribute('data-changed', 'yes');"><?php echo $_POST['message_confirmation_email_subject']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_body"><?php _e('Body', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_confirmation_email_body" id="message_confirmation_email_body" rows="15" cols="75" onchange="this.setAttribute('data-changed', 'yes');"><?php echo $_POST['message_confirmation_email_body']; ?></textarea>
<span class="description"><?php _e('You can insert shortcodes into <em>Sender</em>, <em>Receiver</em>, <em>Subject</em> and <em>Body</em> fields to display informations about the sender, the message and the form.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#email-shortcodes"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<?php if (!isset($_POST['submit'])) { $_POST['message_notification_email_body'] = htmlspecialchars(kleor_get_option('contact_manager_message_notification_email_body')); } ?>
<div class="postbox" id="message-notification-email-module"<?php if (in_array('message-notification-email', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="message-notification-email"><strong><?php echo $modules['message']['message-notification-email']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#message-notification-email"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_notification_email_sent" id="message_notification_email_sent" value="yes"<?php if ($_POST['message_notification_email_sent'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes');" /> <strong><?php _e('Send a message notification email', 'contact-manager'); ?></strong></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_sender"><?php _e('Sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_sender" id="message_notification_email_sender" rows="1" cols="75" onchange="this.setAttribute('data-changed', 'yes');"><?php echo $_POST['message_notification_email_sender']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_receiver"><?php _e('Receiver', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_receiver" id="message_notification_email_receiver" rows="1" cols="75" onchange="this.setAttribute('data-changed', 'yes');"><?php echo $_POST['message_notification_email_receiver']; ?></textarea><br />
<span class="description"><?php _e('You can enter several email addresses. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_subject"><?php _e('Subject', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_subject" id="message_notification_email_subject" rows="1" cols="75" onchange="this.setAttribute('data-changed', 'yes');"><?php echo $_POST['message_notification_email_subject']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_body"><?php _e('Body', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_notification_email_body" id="message_notification_email_body" rows="15" cols="75" onchange="this.setAttribute('data-changed', 'yes');"><?php echo $_POST['message_notification_email_body']; ?></textarea>
<span class="description"><?php _e('You can insert shortcodes into <em>Sender</em>, <em>Receiver</em>, <em>Subject</em> and <em>Body</em> fields to display informations about the sender, the message and the form.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#email-shortcodes"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="autoresponders-module"<?php if (in_array('autoresponders', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="autoresponders"><strong><?php echo $modules['message']['autoresponders']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#autoresponders"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You must make some adjustments so that the subscription works with some autoresponders.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_to_autoresponder" id="sender_subscribed_to_autoresponder" value="yes"<?php if ($_POST['sender_subscribed_to_autoresponder'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes');" /> <strong><?php _e('Subscribe the sender to an autoresponder list', 'contact-manager'); ?></strong></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_autoresponder"><?php _e('Autoresponder', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_autoresponder" id="sender_autoresponder" onchange="this.setAttribute('data-changed', 'yes');">
<?php $autoresponder = kleor_do_shortcode($_POST['sender_autoresponder']);
foreach (contact_autoresponders() as $key => $value) {
echo '<option value="'.$key.'"'.($autoresponder == $key ? ' selected="selected"' : '').'>'.$key.'</option>'."\n"; } ?>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_autoresponder_list"><?php _e('List', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sender_autoresponder_list" id="sender_autoresponder_list" rows="1" cols="50" onchange="this.setAttribute('data-changed', 'yes');"><?php echo $_POST['sender_autoresponder_list']; ?></textarea><br />
<span class="description"><?php _e('For most autoresponders, you must enter the list ID.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="registration-as-a-client-module"<?php if (in_array('registration-as-a-client', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="registration-as-a-client"><strong><?php echo $modules['message']['registration-as-a-client']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('commerce_data')) { ?>
<a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#registration-as-a-client"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a>
<?php } else { echo str_replace('<a', '<a '.$documentations_links_markup, __('To register the senders as clients, you must have installed and activated <a href="https://www.kleor.com/commerce-manager/">Commerce Manager</a>.', 'contact-manager')); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_as_a_client" id="sender_subscribed_as_a_client" value="yes"<?php if ($_POST['sender_subscribed_as_a_client'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes'); kleor_update_form(this.form);" /> 
<strong><?php _e('Register the sender as a client', 'contact-manager'); ?></strong></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#registration-as-a-client"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php if (kleor_get_option('commerce_manager')) {
$categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."commerce_manager_clients_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_client_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_client_category_id" id="sender_client_category_id" onchange="this.setAttribute('data-changed', 'yes'); kleor_update_form(this.form);">
<option value="0"<?php if ($_POST['sender_client_category_id'] == 0) { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($_POST['sender_client_category_id'] == $category->id ? ' selected="selected"' : '').'>'.kleor_do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if (function_exists('commerce_data')) {
$ids_fields[] = 'sender_client_category_id';
echo '<span id="sender-client-category-id-links">'.contact_manager_pages_field_links($back_office_options, 'sender_client_category_id', $_POST['sender_client_category_id']).'</span>'; } ?></td></tr>
<?php } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_client_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_client_status" id="sender_client_status" onchange="this.setAttribute('data-changed', 'yes');">
<option value="active"<?php if ($_POST['sender_client_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($_POST['sender_client_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/commerce-manager/documentation/#client-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="commerce_registration_confirmation_email_sent" id="commerce_registration_confirmation_email_sent" value="yes"<?php if ($_POST['commerce_registration_confirmation_email_sent'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes');" /> <?php _e('Send a registration confirmation email', 'contact-manager'); ?></label><br />
<span class="description"><?php (((function_exists('commerce_data')) && (current_user_can('view_commerce_manager'))) ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=commerce-manager-client-area#registration-confirmation-email') : _e('You can configure this email through the <em>Client Area</em> page of Commerce Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="commerce_registration_notification_email_sent" id="commerce_registration_notification_email_sent" value="yes"<?php if ($_POST['commerce_registration_notification_email_sent'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes');" /> <?php _e('Send a registration notification email', 'contact-manager'); ?></label><br />
<span class="description"><?php (((function_exists('commerce_data')) && (current_user_can('view_commerce_manager'))) ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=commerce-manager-client-area#registration-notification-email') : _e('You can configure this email through the <em>Client Area</em> page of Commerce Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="registration-to-affiliate-program-module"<?php if (in_array('registration-to-affiliate-program', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="registration-to-affiliate-program"><strong><?php echo $modules['message']['registration-to-affiliate-program']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('affiliation_data')) { ?>
<a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#registration-to-affiliate-program"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a>
<?php } else { echo str_replace('<a', '<a '.$documentations_links_markup, __('To use affiliation, you must have installed and activated <a href="https://www.kleor.com/affiliation-manager/">Affiliation Manager</a>.', 'contact-manager')); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_to_affiliate_program" id="sender_subscribed_to_affiliate_program" value="yes"<?php if ($_POST['sender_subscribed_to_affiliate_program'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes'); kleor_update_form(this.form);" /> 
<strong><?php _e('Register the sender to the affiliate program', 'contact-manager'); ?></strong></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#registration-to-affiliate-program"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php if (kleor_get_option('affiliation_manager')) {
$categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."affiliation_manager_affiliates_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_affiliate_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_affiliate_category_id" id="sender_affiliate_category_id" onchange="this.setAttribute('data-changed', 'yes'); kleor_update_form(this.form);">
<option value="0"<?php if ($_POST['sender_affiliate_category_id'] == 0) { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($_POST['sender_affiliate_category_id'] == $category->id ? ' selected="selected"' : '').'>'.kleor_do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if (function_exists('affiliation_data')) {
$ids_fields[] = 'sender_affiliate_category_id';
echo '<span id="sender-affiliate-category-id-links">'.contact_manager_pages_field_links($back_office_options, 'sender_affiliate_category_id', $_POST['sender_affiliate_category_id']).'</span>'; } ?></td></tr>
<?php } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_affiliate_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_affiliate_status" id="sender_affiliate_status" onchange="this.setAttribute('data-changed', 'yes');">
<option value="active"<?php if ($_POST['sender_affiliate_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($_POST['sender_affiliate_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/affiliation-manager/documentation/#affiliate-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="affiliation_registration_confirmation_email_sent" id="affiliation_registration_confirmation_email_sent" value="yes"<?php if ($_POST['affiliation_registration_confirmation_email_sent'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes');" /> <?php _e('Send a registration confirmation email', 'contact-manager'); ?></label><br />
<span class="description"><?php (((function_exists('affiliation_data')) && (current_user_can('view_affiliation_manager'))) ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=affiliation-manager#registration-confirmation-email') : _e('You can configure this email through the <em>Options</em> page of Affiliation Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="affiliation_registration_notification_email_sent" id="affiliation_registration_notification_email_sent" value="yes"<?php if ($_POST['affiliation_registration_notification_email_sent'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes');" /> <?php _e('Send a registration notification email', 'contact-manager'); ?></label><br />
<span class="description"><?php (((function_exists('affiliation_data')) && (current_user_can('view_affiliation_manager'))) ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=affiliation-manager#registration-notification-email') : _e('You can configure this email through the <em>Options</em> page of Affiliation Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="membership-module"<?php if (in_array('membership', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="membership"><strong><?php echo $modules['message']['membership']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('membership_data')) { ?>
<a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#membership"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a>
<?php } else { echo str_replace('<a', '<a '.$documentations_links_markup, __('To use membership, you must have installed and activated <a href="https://www.kleor.com/membership-manager/">Membership Manager</a>.', 'contact-manager')); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_to_members_areas" id="sender_subscribed_to_members_areas" value="yes"<?php if ($_POST['sender_subscribed_to_members_areas'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes'); kleor_update_form(this.form);" /> 
<strong><?php _e('Subscribe the sender to a member area', 'contact-manager'); ?></strong></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#membership"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_members_areas"><?php _e('Members areas', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sender_members_areas" id="sender_members_areas" rows="1" cols="50" onkeyup="this.setAttribute('data-changed', 'yes'); kleor_update_links(this.form); kleor_update_form(this.form);" onchange="this.setAttribute('data-changed', 'yes'); kleor_update_links(this.form); kleor_update_form(this.form);"><?php echo $_POST['sender_members_areas']; ?></textarea>
<?php if (function_exists('membership_data')) {
$ids_fields[] = 'sender_members_areas';
echo '<span class="description" style="vertical-align: 25%;" id="sender-members-areas-description">'.contact_manager_pages_field_description('sender_members_areas', $_POST['sender_members_areas']).'</span>';
$links = contact_manager_pages_field_links($back_office_options, 'sender_members_areas', $_POST['sender_members_areas']); echo '<span id="sender-members-areas-links">'.$links.'</span>';
$string = '-member-area&amp;id='.$_POST['sender_members_areas']; $url = 'admin.php?page=membership-manager'.(strstr($links, $string) ? $string : ''); } ?><br />
<span class="description"><?php _e('You can enter several members areas IDs. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_members_areas_modifications"><?php _e('Automatic modifications', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 50%;" name="sender_members_areas_modifications" id="sender_members_areas_modifications" rows="2" cols="50" onchange="this.setAttribute('data-changed', 'yes'); kleor_update_form(this.form);"><?php echo $_POST['sender_members_areas_modifications']; ?></textarea>
<span class="description"><?php _e('You can offer a temporary access, and automatically modify the list of members areas to which the member can access when a certain date is reached.', 'contact-manager'); ?>
 <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/membership-manager/documentation/#members-areas-modifications"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php if (kleor_get_option('membership_manager')) {
$categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."membership_manager_members_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_member_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_member_category_id" id="sender_member_category_id" onchange="this.setAttribute('data-changed', 'yes'); kleor_update_form(this.form);">
<option value="0"<?php if ($_POST['sender_member_category_id'] == 0) { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($_POST['sender_member_category_id'] == $category->id ? ' selected="selected"' : '').'>'.kleor_do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if (function_exists('membership_data')) {
$ids_fields[] = 'sender_member_category_id';
echo '<span id="sender-member-category-id-links">'.contact_manager_pages_field_links($back_office_options, 'sender_member_category_id', $_POST['sender_member_category_id']).'</span>'; } ?></td></tr>
<?php } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_member_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_member_status" id="sender_member_status" onchange="this.setAttribute('data-changed', 'yes');">
<option value="active"<?php if ($_POST['sender_member_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($_POST['sender_member_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/membership-manager/documentation/#member-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="membership_registration_confirmation_email_sent" id="membership_registration_confirmation_email_sent" value="yes"<?php if ($_POST['membership_registration_confirmation_email_sent'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes');" /> <?php _e('Send a registration confirmation email', 'contact-manager'); ?></label><br />
<span class="description"><?php (((function_exists('membership_data')) && (current_user_can('view_membership_manager'))) ? printf(str_replace('<a', '<a id="membership-registration-confirmation-email-sent-link" '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), $url.'#registration-confirmation-email') : _e('You can configure this email through the interface of Membership Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="membership_registration_notification_email_sent" id="membership_registration_notification_email_sent" value="yes"<?php if ($_POST['membership_registration_notification_email_sent'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes');" /> <?php _e('Send a registration notification email', 'contact-manager'); ?></label><br />
<span class="description"><?php (((function_exists('membership_data')) && (current_user_can('view_membership_manager'))) ? printf(str_replace('<a', '<a id="membership-registration-notification-email-sent-link" '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), $url.'#registration-notification-email') : _e('You can configure this email through the interface of Membership Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="wordpress-module"<?php if (in_array('wordpress', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="wordpress"><strong><?php echo $modules['message']['wordpress']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#wordpress"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_as_a_user" id="sender_subscribed_as_a_user" value="yes"<?php if ($_POST['sender_subscribed_as_a_user'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes');" /> 
<strong><?php _e('Register the sender as a user', 'contact-manager'); ?></strong></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#wordpress"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_user_role"><?php _e('Role', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_user_role" id="sender_user_role" onchange="this.setAttribute('data-changed', 'yes');">
<?php foreach (contact_manager_users_roles() as $role => $name) {
echo '<option value="'.$role.'"'.($_POST['sender_user_role'] == $role ? ' selected="selected"' : '').'>'.$name.'</option>'."\n"; } ?>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<?php if (!isset($_POST['submit'])) { $_POST['message_custom_instructions'] = htmlspecialchars(kleor_get_option('contact_manager_message_custom_instructions')); } ?>
<div class="postbox" id="custom-instructions-module"<?php if (in_array('custom-instructions', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="custom-instructions"><strong><?php echo $modules['message']['custom-instructions']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#custom-instructions"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_custom_instructions_executed" id="message_custom_instructions_executed" value="yes"<?php if ($_POST['message_custom_instructions_executed'] == 'yes') { echo ' checked="checked"'; } ?> onchange="this.setAttribute('data-changed', 'yes');" /> <strong><?php _e('Execute custom instructions', 'contact-manager'); ?></strong></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_custom_instructions"><?php _e('PHP code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_custom_instructions" id="message_custom_instructions" rows="10" cols="75" onchange="this.setAttribute('data-changed', 'yes');" <?php if (current_user_can('edit_plugins')) { echo 'placeholder="'.__('You can enter a PHP code or the path (relative to the directory in which your wp-config.php file is located) of a PHP file on your website. In the latter case, it\'s the PHP code of this file that will be executed.', 'contact-manager').'"'; } else { echo 'disabled="disabled" title="'.__('You don\'t have the capability required (edit_plugins) to change this code.', 'contact-manager').'"'; } ?>><?php echo $_POST['message_custom_instructions']; ?></textarea>
<span class="description"><?php _e('You can add custom instructions that will be executed just after the sending of the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>
</div>

<?php } ?>
<p class="submit"><input type="submit" class="button-primary" name="submit" id="submit" value="<?php (isset($_GET['id']) ? _e('Save Changes', 'contact-manager') : _e('Save Message', 'contact-manager')); ?>" /></p>
<?php contact_manager_pages_module($back_office_options, 'message-page', $undisplayed_modules); ?>
</form>
</div>
</div>

<script>
<?php if (isset($_POST['update_fields'])) { echo "window.location = '#add-message-modules';"; }
else {
$modules_list = array(); $submodules = array();
foreach ($modules[$admin_page] as $key => $value) { $modules_list[] = $key; $submodules[$key] = array();
if (isset($value['modules'])) { foreach ($value['modules'] as $module_key => $module_value) { $submodules[$key][] = $module_key; } } }
echo 'anchor = window.location.hash;
modules = '.json_encode($modules_list).';
submodules = '.json_encode($submodules).';
for (i = 0, n = modules.length; i < n; i++) {
element = document.getElementById(modules[i]+"-module");
if ((element) && (anchor == "#"+modules[i])) { element.style.display = "block"; }
for (j = 0, m = submodules[modules[i]].length; j < m; j++) {
subelement = document.getElementById(submodules[modules[i]][j]+"-module");
if ((subelement) && (anchor == "#"+submodules[modules[i]][j])) {
element.style.display = "block"; subelement.style.display = "block"; } } }'."\n"; } ?>

<?php $fields = array(); $strings = array();
foreach (array('kleor_strip_accents_js', 'kleor_format_email_address_js') as $function) { add_action('admin_footer', $function); }
foreach (array(
'email_address',
'referrer',
'referrer2') as $field) { $fields[] = $field; $strings[$field] = "kleor_format_email_address(this.value)"; }
foreach (array(
'commission_amount',
'commission2_amount') as $field) { $fields[] = $field; $strings[$field] = "kleor_format_number(this.value)"; }
foreach (array('form_id') as $field) { $fields[] = $field; $strings[$field] = "kleor_format_integer(this.value)"; }
echo 'fields = '.json_encode($fields).';
strings = '.json_encode($strings).';
for (i = 0, n = fields.length; i < n; i++) {
element = document.getElementById(fields[i]);
if ((element) && ((element.type == "text") || (element.type == "textarea"))) {
events = ["onchange","onkeyup"]; for (j = 0; j < 2; j++) {
if (element.hasAttribute(events[j])) { string = " "+element.getAttribute(events[j]); } else { string = ""; }
element.setAttribute(events[j], "this.value = "+strings[fields[i]]+";"+string); } } }'."\n"; ?>

<?php $fields = array();
foreach ($tables['messages'] as $key => $value) { $fields[] = $key; }
foreach ($custom_fields as $key => $value) { $fields[] = 'custom_field_'.$key; }
if (!isset($_GET['id'])) { $fields = array_merge($fields, $add_message_fields); }
echo 'kleor_update_form_call_number = 0;
function kleor_update_form(form) {
kleor_update_form_call_number += 1;
data = {}; fields = '.json_encode($fields).';
for (i = 0, n = fields.length; i < n; i++) {
if '.(isset($_GET['id']) ? '(typeof form[fields[i]] == "object")' : '((typeof form[fields[i]] == "object") && (form[fields[i]].getAttribute("data-changed") == "yes"))').' {
if (form[fields[i]].type != "checkbox") { data[fields[i]] = form[fields[i]].value; }
else { if (form[fields[i]].checked == true) { data[fields[i]] = "yes"; } else { data[fields[i]] = "no"; } } } }
emptiable_fields = ["referring_url","referrer","referrer2"];
for (i = 0, n = emptiable_fields.length; i < n; i++) { var key = emptiable_fields[i]+"_emptied"; data[key] = form[key].value; }
ids_fields = '.json_encode($ids_fields).'; data["ids_fields"] = ids_fields;
data["kleor_update_form_call_number"] = kleor_update_form_call_number;
jQuery.post("'.HOME_URL.'?plugin=contact-manager&action=update-form&page='.$_GET['page'].(isset($_GET['id']) ? '&id='.$_GET['id'] : '').'&time='.$current_time.'&key='.md5(AUTH_KEY).'", data, function(data) {
if (data["kleor_update_form_call_number"] == kleor_update_form_call_number) {
for (i = 0, n = fields.length; i < n; i++) {
if (typeof form[fields[i]] == "object") {
if '.(isset($_GET['id']) ? '((typeof data[fields[i]] != "undefined") && (fields[i] != document.activeElement.name))'
 : '((typeof data[fields[i]] != "undefined") && ((fields[i] != document.activeElement.name)
 || (fields[i] == "receiver") || (fields[i] == "commission_amount") || (fields[i] == "referrer2") || (fields[i] == "commission2_amount")))').' {
if (form[fields[i]].type != "checkbox") { form[fields[i]].value = data[fields[i]]; }
else { if (data[fields[i]] == "yes") { form[fields[i]].checked = true; } else { form[fields[i]].checked = false; } } }
var element = document.getElementById(fields[i]+"_error"); if (element) {
if (typeof data[fields[i]+"_error"] == "undefined") { element.innerHTML = ""; }
else { element.innerHTML = data[fields[i]+"_error"]; } } } }
var strings = ["description","links"];
for (i = 0, n = ids_fields.length; i < n; i++) { for (j = 0; j < 2; j++) {
var key = ids_fields[i]+"_"+strings[j];
var element = document.getElementById(key.replace(/[_]/g, "-"));
if ((element) && (typeof data[key] != "undefined")) { element.innerHTML = data[key]; } } }
if (form["commission_status"].value == "paid") { document.getElementById("commission-payment-date").style.display = ""; } else { document.getElementById("commission-payment-date").style.display = "none"; }
if (form["commission2_status"].value == "paid") { document.getElementById("commission2-payment-date").style.display = ""; } else { document.getElementById("commission2-payment-date").style.display = "none"; }
kleor_update_links(form); jQuery(".noscript").css("display", "none"); } }, "json"); }'."\n";

if ((!isset($_GET['id'])) && ($_POST['form_id'] > 0)) { echo 'kleor_update_form(document.forms["'.$_GET['page'].'"]);'; } ?>

<?php echo 'function search_form_id(form) {
var search = encodeURIComponent(form.form_id_search.value);
if (search != "") {
var value = form.form_id.value;
var element = document.getElementById("form_id_selector");
jQuery.get("'.contact_url('index.php').'?action=search-form-id'.(is_multisite() ? '&blog_id='.get_current_blog_id() : '').'&lang='.strtolower(substr(get_locale(), 0, 2)).'&search="+search+"&value="+value+"&key='.md5(DB_NAME.DB_PASSWORD).'", function(data) {
if (data.indexOf("<select") >= 0) {
element.innerHTML = data;
if (form.form_id.value != value) { kleor_update_form(form); } } }); } }'."\n"; ?>

<?php echo 'function kleor_update_links(form) {
var fields = '.json_encode($urls_fields).';
for (i = 0, n = fields.length; i < n; i++) {
var element = document.getElementById(fields[i].replace(/[_]/g, "-")+"-link");
if (element) {
if (fields[i].substr(0, 13) == "custom_field_") {
var url = form[fields[i]].value; if ((url.indexOf(" ") >= 0) || (url.substr(0, 4) != "http")) { url = ""; } }
else { var urls = form[fields[i]].value.split(","); var url = kleor_format_url(urls[0].replace(/[ ]/g, "")); }
if (url == "") { element.innerHTML = ""; }
else { element.innerHTML = \'<a style="vertical-align: 25%;" '.$urls_fields_links_markup.' href="\'+url.replace(/[&]/g, "&amp;")+\'">'.__('Link', 'contact-manager').'</a>\'; }
if (fields[i] == "website_url") {
var element2 = document.getElementById("website-name-link");
if (element2) { element2.innerHTML = element.innerHTML; } } } }
'.(((!function_exists('membership_data')) || (isset($_GET['id'])) || (in_array('membership', $undisplayed_modules))) ? '' : 'var field = form["sender_members_areas"]; if (field) {
var url = "admin.php?page=membership-manager";
var element = document.getElementById("sender-members-areas-links");
if ((field.value != "") && (field.value != 0) && (element.innerHTML.indexOf("id="+field.value) >= 0)) { url += "-member-area&id="+field.value; }
var actions = ["confirmation","notification"]; for (i = 0; i < 2; i++) {
var element = document.getElementById("membership-registration-"+actions[i]+"-email-sent-link");
if (element) { element.href = url+"#registration-"+actions[i]+"-email"; } } }').' }'."\n"; ?>

<?php foreach (array('kleor_strip_accents_js', 'kleor_format_nice_name_js') as $function) { add_action('admin_footer', $function); }
echo 'function kleor_update_country(form) {
var countries = '.json_encode($countries).';
if '.(isset($_GET['id']) ? '(form["country"].value == "")' : '((form["country"].value == "") || (form["country"].getAttribute("data-changed") != "yes"))').' {
var key = form["country_code"].value;
if (typeof countries[key] != "undefined") { form["country"].value = countries[key]; } } }

function kleor_update_country_code(form) {
var country_codes = '.json_encode(array_flip(array_map('kleor_format_nice_name', $countries))).';
if '.(isset($_GET['id']) ? '(form["country_code"].value == "")' : '((form["country_code"].value == "") || (form["country_code"].getAttribute("data-changed") != "yes"))').' {
var key = kleor_format_nice_name(form["country"].value);
if (typeof country_codes[key] != "undefined") { form["country_code"].value = country_codes[key]; } } }'."\n"; ?>
</script>
<?php }
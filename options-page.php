<?php if (!defined('ABSPATH')) { exit(); }
global $wpdb; $error = '';
$back_office_options = (array) kleor_get_option('contact_manager_back_office');
extract(contact_manager_pages_links_markups($back_office_options));
$admin_page = 'options';

if (((!isset($_GET['action'])) || ($_GET['action'] != 'uninstall')) && (!kleor_get_option('contact_manager_answers'))) {
if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!current_user_can('manage_contact_manager')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
include contact_path('libraries/questions.php');
$a = array(); foreach ($default_answers as $key => $value) {
$a[$key] = (isset($_POST[$key.'_question']) ? ($_POST[$key.'_question'] == 'yes' ? true : false) : $value); }
kleor_update_option('contact_manager_answers', $a);
reset_contact_manager($false);
if ((!defined('KLEOR_DEMO')) || (KLEOR_DEMO == false)) {
if ($a['contact_page']) {
wp_insert_post(array(
'comment_status' => 'closed',
'ping_status' => 'closed',
'post_name' => __('contact', 'contact-manager'),
'post_status' => 'publish',
'post_title' => __('Contact', 'contact-manager'),
'post_type' => 'page',
'post_content' => '<p>'.__('To contact us, please fill out the form below. Only fields marked with * are required. <strong>We will answer you quickly</strong> (most of the time in less than 24 hours).', 'contact-manager').'</p>

[contact-form redirection=# id=1]')); } }
if (!headers_sent()) { header('Location: admin.php?page=contact-manager-back-office'); exit(); } } } ?>
<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if (isset($_POST['submit'])) {
echo '<script>window.location = "admin.php?page=contact-manager-back-office";</script>';
contact_manager_pages_menu($back_office_options); }
else { ?>
<form method="post" name="<?php echo esc_attr($_GET['page']); ?>" id="<?php echo esc_attr($_GET['page']); ?>" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>" onsubmit="return kleor_validate_form(this, true);">
<?php wp_nonce_field($_GET['page']); ?>
<?php if ($error != '') { echo '<p style="color: #c00000; font-size: 2em; font-weight: bold;">'.$error.'</p>'; } ?>

<?php contact_manager_pages_celia('', '<p style="font-family: \'Amaranth\', Verdana, sans-serif; font-size: 2em;">'.__('I\'ll do a lot of the work for you. Answer the questions below. Then I configure the plugin according to your answers and I give you a guided tour.', 'contact-manager').'&nbsp;'.str_replace('<svg xmlns="http://www.w3.org/2000/svg"', '<svg style="width: 1em; height: 1em; vertical-align: -10%;"', file_get_contents(contact_path('images/1f642.svg'))).'</p>'); ?>

<div class="postbox">
<div class="inside">
<table class="form-table"><tbody>
<?php include contact_path('libraries/questions.php');
$fields = array();
foreach ($questions as $key => $value) { $last = $key; }
foreach ($questions as $key => $value) { $fields[] = $key.'_question'; ?>
<tr style="<?php if ($key != $last) { echo 'border-bottom: 1px solid #b8d0e8; '; } ?>font-size: 1.3em; vertical-align: top;" onmouseover="document.getElementById('<?php echo $key; ?>_question_label').style.opacity = 1;" onmouseout="kleor_update_form(this_form, false);">
<th scope="row" style="width: 60%;">
<label id="<?php echo $key; ?>_question_label" style="cursor: default;"><strong style="font-weight: 700;"><?php echo $value['label']; ?></strong>
<?php if (isset($value['description'])) { echo '<br /><span class="description" style="color: #606060;">'.$value['description'].'</span>'; } ?>
<input type="hidden" name="<?php echo $key; ?>_question" id="<?php echo $key; ?>_question" value="" /></label></th>
<td class="answer" style="text-align: center; width: 20%;"><span id="<?php echo $key; ?>_question_yes_button" class="yes-button" onclick="this_form.<?php echo $key; ?>_question.value = 'yes'; kleor_update_form(this_form, true);" onmouseover="this.style.opacity = 1;" onmouseout="kleor_update_form(this_form, false);"><?php _e('Yes', 'contact-manager'); ?></span></td>
<td class="answer" style="text-align: center; width: 20%;"><span id="<?php echo $key; ?>_question_no_button" class="no-button" onclick="this_form.<?php echo $key; ?>_question.value = 'no'; kleor_update_form(this_form, true);" onmouseover="this.style.opacity = 1;" onmouseout="kleor_update_form(this_form, false);"><?php _e('No', 'contact-manager'); ?></span></td>
</tr>
<?php } ?>
</tbody></table>
</div></div>

<div id="submit-button" style="text-align: center;">
<p id="error" style="color: #c00000; font-size: 2em; font-weight: bold;"></p>
<p class="submit" style="text-align: center;"><input type="submit" class="button-primary yellow" style="background-color: #ffcc00; cursor: default; font-size: 2em; opacity: 0.8;" title="<?php _e('You must answer all the questions.', 'contact-manager'); ?>" name="submit" id="submit" value="<?php _e('Configure Contact Manager', 'contact-manager'); ?>" /></p>
</div>

<?php contact_manager_pages_celia('submit'); ?>

<script>
this_form = document.forms['<?php echo esc_attr($_GET['page']); ?>'];
fields = <?php echo json_encode($fields); ?>;

function kleor_update_form(form, redirection) {
var current = -1;
for (i = 0, n = fields.length; i < n; i++) {
if (form[fields[i]].value == 'yes') {
document.getElementById(fields[i]+'_label').style.opacity = 0.6;
document.getElementById(fields[i]+'_yes_button').style.opacity = 1;
document.getElementById(fields[i]+'_no_button').style.opacity = 0.4; }
else if (form[fields[i]].value == 'no') {
document.getElementById(fields[i]+'_label').style.opacity = 0.6;
document.getElementById(fields[i]+'_yes_button').style.opacity = 0.4;
document.getElementById(fields[i]+'_no_button').style.opacity = 1; }
else {
if (current < 0) { current = i; }
document.getElementById(fields[i]+'_label').style.opacity = 1;
document.getElementById(fields[i]+'_yes_button').style.opacity = 0.8;
document.getElementById(fields[i]+'_no_button').style.opacity = 0.8; } }
if (kleor_validate_form(form, false)) { if (redirection) { window.location = '#<?php echo $last; ?>_question_label'; } }
else if ((current%5 == 1) && (redirection)) { window.location = '#'+fields[(current - 1)]+'_label'; } }

function kleor_validate_form(form, display_error) {
error = false;
error_element = document.getElementById('error');
submit_element = document.getElementById('submit');
submit_button_element = document.getElementById('submit-button');
celia_submit_element = document.getElementById('celia-submit');
for (i = 0, n = fields.length; i < n; i++) {
if ((!error) && (form[fields[i]].value != 'yes') && (form[fields[i]].value != 'no')) { error = true; } }
if (error) {
if (display_error) { error_element.innerHTML = '<?php _e('You must answer all the questions.', 'contact-manager'); ?>'; }
submit_element.style.cursor = 'default'; submit_element.style.opacity = 0.8; submit_element.title = '<?php _e('You must answer all the questions.', 'contact-manager'); ?>';
submit_button_element.style.display = ''; celia_submit_element.style.display = 'none'; }
else {
error_element.innerHTML = '';
submit_element.style.cursor = 'pointer'; submit_element.style.opacity = 1; submit_element.title = '';
if (celia_submit_element.innerHTML != '') { submit_button_element.style.display = 'none'; celia_submit_element.style.display = ''; } }
return !error; }
</script>
</form><?php } ?>
</div>
</div><?php }

elseif ((isset($_GET['action'])) && (($_GET['action'] == 'reset') || ($_GET['action'] == 'uninstall'))) {
if ((!current_user_can('activate_plugins')) || (!current_user_can('manage_contact_manager'))) {
if (!headers_sent()) { header('Location: admin.php?page=contact-manager'); exit(); }
else { echo '<script>window.location = "admin.php?page=contact-manager";</script>'; } }
else {
$for = (((isset($_GET['for'])) && (is_multisite()) && (current_user_can('manage_network'))) ? $_GET['for'] : 'single');
if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if ($_GET['action'] == 'reset') { reset_contact_manager(); } else { uninstall_contact_manager($for); } } ?>
<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if (isset($_POST['submit'])) {
echo '<div class="updated-notice"><p><strong>'.($_GET['action'] == 'reset' ? __('Options reset.', 'contact-manager') : __('Options and tables deleted.', 'contact-manager')).'</strong></p></div>
<script>setTimeout(\'window.location = "'.($_GET['action'] == 'reset' ? 'admin.php?page=contact-manager' : ($for == 'network' ? 'network/' : '').'plugins.php').'"\', 2000);</script>'; } ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<?php if (!isset($_POST['submit'])) { ?>
<form method="post" name="<?php echo esc_attr($_GET['page']); ?>" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<div class="alignleft actions">
<p style="font-size: 1.2em;"><strong style="color: #c00000;"><?php if ($_GET['action'] == 'reset') { _e('Do you really want to reset the options of Contact Manager?', 'contact-manager'); }
elseif ($for == 'network') { _e('Do you really want to permanently delete the options and tables of Contact Manager for all sites in this network?', 'contact-manager'); }
else { _e('Do you really want to permanently delete the options and tables of Contact Manager?', 'contact-manager'); } ?></strong></p>
<p><input type="submit" class="button-secondary" name="submit" id="submit" value="<?php _e('Yes', 'contact-manager'); ?>" />
<span class="description"><?php _e('This action is irreversible.', 'contact-manager'); ?></span></p>
</div>
</form><?php } ?>
</div>
</div><?php } }

else {
foreach (array('admin-pages.php', 'initial-options.php') as $file) { include contact_path($file); }
$other_options = array(
'code',
'form_submission_custom_instructions',
'message_confirmation_email_body',
'message_custom_instructions',
'message_notification_email_body',
'message_removal_custom_instructions');
if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!current_user_can('manage_contact_manager')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
foreach ($_POST as $key => $value) {
if (is_string($value)) { $_POST[$key] = stripslashes(html_entity_decode(str_replace(array('&nbsp;', '&#91;', '&#93;'), array(' ', '&amp;#91;', '&amp;#93;'), $value))); } }
$back_office_options = update_contact_manager_back_office($back_office_options, 'options');
include contact_path('includes/update-form.php'); } }
if (!isset($options)) { $options = (array) kleor_get_option('contact_manager'); }

foreach ($options as $key => $value) {
if (is_string($value)) { $options[$key] = htmlspecialchars($value); } }
$undisplayed_modules = (array) $back_office_options['options_page_undisplayed_modules'];
foreach (array('ids_fields', 'urls_fields') as $variable) { $$variable = array(); }
if (function_exists('commerce_data')) { $currency_code = commerce_data('currency_code'); }
else { $commerce_manager_options = array_merge((array) kleor_get_option('commerce_manager'), (array) kleor_get_option('commerce_manager_client_area'));
$currency_code = (isset($commerce_manager_options['currency_code']) ? kleor_do_shortcode($commerce_manager_options['currency_code']) : ''); } ?>

<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if (isset($_POST['submit'])) { echo '<div class="updated-notice"><p><strong>'.__('Settings saved.', 'contact-manager').'</strong></p></div>'; } ?>
<form method="post" name="<?php echo esc_attr($_GET['page']); ?>" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>" onsubmit="return kleor_validate_form(this);">
<?php wp_nonce_field($_GET['page']); ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<p class="description"><?php _e('You can reset an option by leaving the corresponding field blank.', 'contact-manager'); ?></p>
<?php contact_manager_pages_summary($back_office_options); ?>

<div class="postbox" id="automatic-display-module"<?php if (in_array('automatic-display', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="automatic-display"><strong><?php echo $modules['options']['automatic-display']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="automatic_display_enabled" id="automatic_display_enabled" value="yes"<?php if ($options['automatic_display_enabled'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Enable automatic display', 'contact-manager'); ?></label> 
<span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#automatic-display"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="automatic_display_only_on_single_post_pages" id="automatic_display_only_on_single_post_pages" value="yes"<?php if ($options['automatic_display_only_on_single_post_pages'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Only on single post pages', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="automatic_display_location"><?php _e('Location', 'contact-manager'); ?></label></strong></th>
<td><select name="automatic_display_location" id="automatic_display_location">
<option value="top"<?php if ($options['automatic_display_location'] == 'top') { echo ' selected="selected"'; } ?>><?php _e('On the top of posts', 'contact-manager'); ?></option>
<option value="bottom"<?php if ($options['automatic_display_location'] == 'bottom') { echo ' selected="selected"'; } ?>><?php _e('On the bottom of posts', 'contact-manager'); ?></option>
<option value="top, bottom"<?php if ($options['automatic_display_location'] == 'top, bottom') { echo ' selected="selected"'; } ?>><?php _e('On the top and bottom of posts', 'contact-manager'); ?></option>
</select></td></tr>
<?php $selector = ''; $items = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."contact_manager_forms ORDER BY name ASC", OBJECT);
if (count($items) <= 10) {
$selector = '<select name="automatic_display_form_id" id="automatic_display_form_id" onchange="kleor_update_form(this.form);">';
foreach ($items as $item) {
$selector .= '<option value="'.$item->id.'"'.($options['automatic_display_form_id'] == $item->id ? ' selected="selected"' : '').'>'.$item->id.' ('.htmlspecialchars(contact_excerpt(contact_form_data(array(0 => 'name', 'id' => $item->id)), 50)).')</option>'."\n"; }
$selector .= '</select>';
if (!strstr($selector, 'selected="selected"')) { $selector = ''; } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="automatic_display_form_id"><?php _e('Form ID', 'contact-manager'); ?></label></strong></th>
<td><span id="automatic_display_form_id_selector"><?php if ($selector != '') { echo $selector; } else { ?><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="automatic_display_form_id" id="automatic_display_form_id" rows="1" cols="25" onkeyup="if (this.value != '') { kleor_update_form(this.form); }" onchange="kleor_update_form(this.form);"><?php echo $options['automatic_display_form_id']; ?></textarea>
<span class="description" style="vertical-align: 25%;" id="automatic-display-form-id-description"><?php echo contact_manager_pages_field_description('automatic_display_form_id', $options['automatic_display_form_id']); ?></span><?php } ?></span>
<span id="automatic-display-form-id-links"><?php $ids_fields[] = 'automatic_display_form_id'; echo contact_manager_pages_field_links($back_office_options, 'automatic_display_form_id', $options['automatic_display_form_id']); ?></span>
<?php if ($selector == '') { ?><br /><textarea style="margin-top: 0.5em; padding: 0 0.25em; height: 1.75em; width: 75%;" name="automatic_display_form_id_search" id="automatic_display_form_id_search" rows="1" cols="75" onkeyup="if (this.value != '') { search_automatic_display_form_id(this.form); }" onchange="search_automatic_display_form_id(this.form);" placeholder="<?php _e('Search a form (name, description or keywords)', 'contact-manager'); ?>"></textarea>
<?php } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="automatic_display_maximum_forms_quantity"><?php _e('Maximum quantity of forms displayed per page', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="automatic_display_maximum_forms_quantity" id="automatic_display_maximum_forms_quantity" rows="1" cols="25" onchange="kleor_update_form(this.form);"><?php echo ($options['automatic_display_maximum_forms_quantity'] === 'unlimited' ? '' : $options['automatic_display_maximum_forms_quantity']); ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('Leave this field blank for an unlimited quantity.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="forms-module"<?php if (in_array('forms', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="forms"><strong><?php echo $modules['options']['forms']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="code"><?php _e('Code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="code" id="code" rows="15" cols="75"><?php echo htmlspecialchars(kleor_get_option('contact_manager_code')); ?></textarea>
<span id="code_preview_button"></span>
<p class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#forms"><?php _e('How to display a form?', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#forms-creation"><?php _e('How to create a form?', 'contact-manager'); ?></a></p>
<p class="description" style="margin: 1.5em 0;">
<a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#input"><?php _e('Display a form field', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#textarea"><?php _e('Display a text area', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#select"><?php _e('Display a dropdown list', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#error"><?php _e('Display an error message', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#button"><?php _e('Display a submit button', 'contact-manager'); ?></a></p></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
<div id="captcha-module"<?php if (in_array('captcha', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="captcha"><strong><?php echo $modules['options']['forms']['modules']['captcha']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#captcha"><?php _e('How to display a CAPTCHA?', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="default_captcha_type"><?php _e('Default type', 'contact-manager'); ?></label></strong></th>
<td><select name="default_captcha_type" id="default_captcha_type">
<?php include contact_path('libraries/captchas.php');
$captcha_type = kleor_do_shortcode($options['default_captcha_type']);
asort($captchas_types);
foreach ($captchas_types as $key => $value) {
echo '<option value="'.$key.'"'.($captcha_type == $key ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; } ?>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#captcha"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="default_recaptcha_theme"><?php _e('Default reCAPTCHA theme', 'contact-manager'); ?></label></strong></th>
<td><select name="default_recaptcha_theme" id="default_recaptcha_theme">
<?php include contact_path('libraries/captchas.php');
$recaptcha_theme = kleor_do_shortcode($options['default_recaptcha_theme']);
asort($recaptcha_themes);
foreach ($recaptcha_themes as $key => $value) {
echo '<option value="'.$key.'"'.($recaptcha_theme == $key ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; } ?>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#recaptcha"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="recaptcha_public_key"><?php _e('reCAPTCHA v2 site key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="recaptcha_public_key" id="recaptcha_public_key" rows="1" cols="50"><?php echo $options['recaptcha_public_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#recaptcha"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="recaptcha_private_key"><?php _e('reCAPTCHA v2 secret key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="recaptcha_private_key" id="recaptcha_private_key" rows="1" cols="50"><?php echo $options['recaptcha_private_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#recaptcha"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="recaptcha3_public_key"><?php _e('reCAPTCHA v3 site key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="recaptcha3_public_key" id="recaptcha3_public_key" rows="1" cols="50"><?php echo $options['recaptcha3_public_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#recaptcha"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="recaptcha3_private_key"><?php _e('reCAPTCHA v3 secret key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="recaptcha3_private_key" id="recaptcha3_private_key" rows="1" cols="50"><?php echo $options['recaptcha3_private_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#recaptcha"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="default_hcaptcha_theme"><?php _e('Default hCaptcha theme', 'contact-manager'); ?></label></strong></th>
<td><select name="default_hcaptcha_theme" id="default_hcaptcha_theme">
<?php include contact_path('libraries/captchas.php');
$hcaptcha_theme = kleor_do_shortcode($options['default_hcaptcha_theme']);
asort($hcaptcha_themes);
foreach ($hcaptcha_themes as $key => $value) {
echo '<option value="'.$key.'"'.($hcaptcha_theme == $key ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; } ?>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#hcaptcha"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="hcaptcha_public_key"><?php _e('hCaptcha site key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="hcaptcha_public_key" id="hcaptcha_public_key" rows="1" cols="50"><?php echo $options['hcaptcha_public_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#hcaptcha"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="hcaptcha_private_key"><?php _e('hCaptcha secret key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="hcaptcha_private_key" id="hcaptcha_private_key" rows="1" cols="50"><?php echo $options['hcaptcha_private_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#hcaptcha"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
<div id="error-messages-module"<?php if (in_array('error-messages', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="error-messages"><strong><?php echo $modules['options']['forms']['modules']['error-messages']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#error"><?php _e('How to display an error message?', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="unfilled_fields_message"><?php _e('Unfilled required fields', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="unfilled_fields_message" id="unfilled_fields_message" rows="1" cols="75"><?php echo $options['unfilled_fields_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="unfilled_field_message"><?php _e('Unfilled required field', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="unfilled_field_message" id="unfilled_field_message" rows="1" cols="75"><?php echo $options['unfilled_field_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="invalid_fields_message"><?php _e('Invalid fields', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="invalid_fields_message" id="invalid_fields_message" rows="1" cols="75"><?php echo $options['invalid_fields_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="invalid_field_message"><?php _e('Invalid field', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="invalid_field_message" id="invalid_field_message" rows="1" cols="75"><?php echo $options['invalid_field_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="invalid_email_address_message"><?php _e('Invalid email address', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="invalid_email_address_message" id="invalid_email_address_message" rows="1" cols="75"><?php echo $options['invalid_email_address_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="invalid_captcha_message"><?php _e('Invalid CAPTCHA', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="invalid_captcha_message" id="invalid_captcha_message" rows="1" cols="75"><?php echo $options['invalid_captcha_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="failed_upload_message"><?php _e('Failed upload', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="failed_upload_message" id="failed_upload_message" rows="1" cols="75"><?php echo $options['failed_upload_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="too_large_file_message"><?php _e('Too large file', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="too_large_file_message" id="too_large_file_message" rows="1" cols="75"><?php echo $options['too_large_file_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="unauthorized_extension_message"><?php _e('Unauthorized extension', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="unauthorized_extension_message" id="unauthorized_extension_message" rows="1" cols="75"><?php echo $options['unauthorized_extension_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="maximum_messages_quantity_reached_message"><?php _e('Maximum messages quantity reached', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="maximum_messages_quantity_reached_message" id="maximum_messages_quantity_reached_message" rows="1" cols="75"><?php echo $options['maximum_messages_quantity_reached_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
</div></div>

<div class="postbox" id="messages-registration-module"<?php if (in_array('messages-registration', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="messages-registration"><strong><?php echo $modules['options']['messages-registration']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="messages_registration_enabled" id="messages_registration_enabled" value="yes"<?php if ($options['messages_registration_enabled'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Save messages in the database', 'contact-manager'); ?></label><br />
<a style="text-decoration: none;" <?php echo $ids_fields_links_markup; ?> href="admin.php?page=contact-manager-messages"><?php _e('Display the messages saved in the database', 'contact-manager'); ?></a></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="maximum_messages_quantity"><?php _e('Maximum messages quantity', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="maximum_messages_quantity" id="maximum_messages_quantity" rows="1" cols="25" onchange="kleor_update_form(this.form);"><?php echo ($options['maximum_messages_quantity'] === 'unlimited' ? '' : $options['maximum_messages_quantity']); ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('You can save only the latest messages to ease your database.', 'contact-manager'); ?><br />
<?php _e('Leave this field blank for an unlimited quantity.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="urls-encryption-module"<?php if (in_array('urls-encryption', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="urls-encryption"><strong><?php echo $modules['options']['urls-encryption']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You can encrypt the download URLs.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#urls-encryption"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="encrypted_urls_validity_duration"><?php _e('Validity duration', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="encrypted_urls_validity_duration" id="encrypted_urls_validity_duration" rows="1" cols="25" onchange="kleor_update_form(this.form);"><?php echo $options['encrypted_urls_validity_duration']; ?></textarea> <span style="vertical-align: 25%;"><?php _e('hours', 'contact-manager'); ?></span>
<span class="description" style="vertical-align: 25%;"><?php _e('Encrypted URLs must have a limited validity duration.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="encrypted_urls_key"><?php _e('Encryption key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="encrypted_urls_key" id="encrypted_urls_key" rows="1" cols="50"><?php echo $options['encrypted_urls_key']; ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('Enter a difficult-to-guess string of characters.', 'contact-manager'); ?><br />
<?php _e('Leave this field blank to automatically generate a new key.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="message-confirmation-email-module"<?php if (in_array('message-confirmation-email', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="message-confirmation-email"><strong><?php echo $modules['options']['message-confirmation-email']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_confirmation_email_sent" id="message_confirmation_email_sent" value="yes"<?php if ($options['message_confirmation_email_sent'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Send a message confirmation email', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_sender"><?php _e('Sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_sender" id="message_confirmation_email_sender" rows="1" cols="75"><?php echo $options['message_confirmation_email_sender']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_receiver"><?php _e('Receiver', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_receiver" id="message_confirmation_email_receiver" rows="1" cols="75"><?php echo $options['message_confirmation_email_receiver']; ?></textarea><br />
<span class="description"><?php _e('You can enter several email addresses. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_subject"><?php _e('Subject', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_subject" id="message_confirmation_email_subject" rows="1" cols="75"><?php echo $options['message_confirmation_email_subject']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_body"><?php _e('Body', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_confirmation_email_body" id="message_confirmation_email_body" rows="15" cols="75"><?php echo htmlspecialchars(kleor_get_option('contact_manager_message_confirmation_email_body')); ?></textarea>
<span class="description"><?php _e('You can insert shortcodes into <em>Sender</em>, <em>Receiver</em>, <em>Subject</em> and <em>Body</em> fields to display informations about the sender, the message and the form.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#email-shortcodes"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="message-notification-email-module"<?php if (in_array('message-notification-email', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="message-notification-email"><strong><?php echo $modules['options']['message-notification-email']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_notification_email_sent" id="message_notification_email_sent" value="yes"<?php if ($options['message_notification_email_sent'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Send a message notification email', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_sender"><?php _e('Sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_sender" id="message_notification_email_sender" rows="1" cols="75"><?php echo $options['message_notification_email_sender']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_receiver"><?php _e('Receiver', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_receiver" id="message_notification_email_receiver" rows="1" cols="75"><?php echo $options['message_notification_email_receiver']; ?></textarea><br />
<span class="description"><?php _e('You can enter several email addresses. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_subject"><?php _e('Subject', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_subject" id="message_notification_email_subject" rows="1" cols="75"><?php echo $options['message_notification_email_subject']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_body"><?php _e('Body', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_notification_email_body" id="message_notification_email_body" rows="15" cols="75"><?php echo htmlspecialchars(kleor_get_option('contact_manager_message_notification_email_body')); ?></textarea>
<span class="description"><?php _e('You can insert shortcodes into <em>Sender</em>, <em>Receiver</em>, <em>Subject</em> and <em>Body</em> fields to display informations about the sender, the message and the form.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#email-shortcodes"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="autoresponders-module"<?php if (in_array('autoresponders', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="autoresponders"><strong><?php echo $modules['options']['autoresponders']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You must make some adjustments so that the subscription works with some autoresponders.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_to_autoresponder" id="sender_subscribed_to_autoresponder" value="yes"<?php if ($options['sender_subscribed_to_autoresponder'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Subscribe the sender to an autoresponder list', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_autoresponder"><?php _e('Autoresponder', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_autoresponder" id="sender_autoresponder">
<?php $autoresponder = kleor_do_shortcode($options['sender_autoresponder']);
foreach (contact_autoresponders() as $key => $value) {
echo '<option value="'.$key.'"'.($autoresponder == $key ? ' selected="selected"' : '').'>'.$key.'</option>'."\n"; } ?>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_autoresponder_list"><?php _e('List', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sender_autoresponder_list" id="sender_autoresponder_list" rows="1" cols="50"><?php echo $options['sender_autoresponder_list']; ?></textarea><br />
<span class="description"><?php _e('For most autoresponders, you must enter the list ID.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="autoresponders-integration-module"<?php if (in_array('autoresponders-integration', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="autoresponders-integration"><strong><?php echo $modules['options']['autoresponders-integration']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<?php if ((function_exists('commerce_data')) && (current_user_can('view_commerce_manager'))) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=commerce-manager#autoresponders-integration"><?php _e('Click here to configure the options of Commerce Manager.', 'contact-manager'); ?></a></span></td></tr>
<?php } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You must make some adjustments so that the subscription works with some autoresponders.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
</tbody></table>
<?php include_once contact_path('libraries/autoresponders-functions.php');
$autoresponders = contact_autoresponders(); foreach ($autoresponders_integration_modules  as $key => $value) {
if ((isset($autoresponders[$value['name']]['display_function'])) && (function_exists($autoresponders[$value['name']]['display_function']))) {
$function = $autoresponders[$value['name']]['display_function']; ?>
<div id="<?php echo $key; ?>-module"<?php if (in_array($key, $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="<?php echo $key; ?>"><strong><?php echo $value['name']; ?></strong></h4><?php $function($options, $back_office_options); ?></div><?php } } ?>
</div></div>

<div class="postbox" id="registration-as-a-client-module"<?php if (in_array('registration-as-a-client', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="registration-as-a-client"><strong><?php echo $modules['options']['registration-as-a-client']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php echo (((function_exists('commerce_data')) && (current_user_can('view_commerce_manager'))) ? '<a '.$default_options_links_markup.' href="admin.php?page=commerce-manager-client-area">'.__('Click here to configure the options of Commerce Manager.', 'contact-manager').'</a>' : str_replace('<a', '<a '.$documentations_links_markup, __('To register the senders as clients, you must have installed and activated <a href="https://www.kleor.com/commerce-manager/">Commerce Manager</a>.', 'contact-manager'))); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_as_a_client" id="sender_subscribed_as_a_client" value="yes"<?php if ($options['sender_subscribed_as_a_client'] == 'yes') { echo ' checked="checked"'; } ?> /> 
<?php _e('Register the sender as a client', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#registration-as-a-client"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php if (kleor_get_option('commerce_manager')) {
$categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."commerce_manager_clients_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_client_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_client_category_id" id="sender_client_category_id" onchange="kleor_update_form(this.form);">
<option value=""<?php if ($options['sender_client_category_id'] == '') { echo ' selected="selected"'; } ?> id="sender_client_category_id_default_option"><?php _e('Commerce Manager\'s option', 'contact-manager'); ?></option>
<option value="0"<?php if ($options['sender_client_category_id'] == '0') { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($options['sender_client_category_id'] == $category->id ? ' selected="selected"' : '').'>'.kleor_do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if (function_exists('commerce_data')) {
$ids_fields[] = 'sender_client_category_id';
$applied_value = ($options['sender_client_category_id'] == '' ? commerce_data('clients_initial_category_id') : $options['sender_client_category_id']);
echo '<span id="sender-client-category-id-links">'.contact_manager_pages_field_links($back_office_options, 'sender_client_category_id', $applied_value).'</span>'; } ?></td></tr>
<?php } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_client_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_client_status" id="sender_client_status">
<option value=""<?php if ($options['sender_client_status'] == '') { echo ' selected="selected"'; } ?> id="sender_client_status_default_option"><?php _e('Commerce Manager\'s option', 'contact-manager'); ?></option>
<option value="active"<?php if ($options['sender_client_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($options['sender_client_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/commerce-manager/documentation/#client-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commerce_registration_confirmation_email_sent"><?php _e('Send a registration confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="commerce_registration_confirmation_email_sent" id="commerce_registration_confirmation_email_sent">
<option value=""<?php if ($options['commerce_registration_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?> id="commerce_registration_confirmation_email_sent_default_option"><?php _e('Commerce Manager\'s option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($options['commerce_registration_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($options['commerce_registration_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (((function_exists('commerce_data')) && (current_user_can('view_commerce_manager'))) ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=commerce-manager-client-area#registration-confirmation-email') : _e('You can configure this email through the <em>Client Area</em> page of Commerce Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commerce_registration_notification_email_sent"><?php _e('Send a registration notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="commerce_registration_notification_email_sent" id="commerce_registration_notification_email_sent">
<option value=""<?php if ($options['commerce_registration_notification_email_sent'] == '') { echo ' selected="selected"'; } ?> id="commerce_registration_notification_email_sent_default_option"><?php _e('Commerce Manager\'s option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($options['commerce_registration_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($options['commerce_registration_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (((function_exists('commerce_data')) && (current_user_can('view_commerce_manager'))) ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=commerce-manager-client-area#registration-notification-email') : _e('You can configure this email through the <em>Client Area</em> page of Commerce Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="registration-to-affiliate-program-module"<?php if (in_array('registration-to-affiliate-program', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="registration-to-affiliate-program"><strong><?php echo $modules['options']['registration-to-affiliate-program']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php echo (((function_exists('affiliation_data')) && (current_user_can('view_affiliation_manager'))) ? '<a '.$default_options_links_markup.' href="admin.php?page=affiliation-manager">'.__('Click here to configure the options of Affiliation Manager.', 'contact-manager').'</a>' : str_replace('<a', '<a '.$documentations_links_markup, __('To use affiliation, you must have installed and activated <a href="https://www.kleor.com/affiliation-manager/">Affiliation Manager</a>.', 'contact-manager'))); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_to_affiliate_program" id="sender_subscribed_to_affiliate_program" value="yes"<?php if ($options['sender_subscribed_to_affiliate_program'] == 'yes') { echo ' checked="checked"'; } ?> /> 
<?php _e('Register the sender to the affiliate program', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#registration-to-affiliate-program"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php if (kleor_get_option('affiliation_manager')) {
$categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."affiliation_manager_affiliates_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_affiliate_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_affiliate_category_id" id="sender_affiliate_category_id" onchange="kleor_update_form(this.form);">
<option value=""<?php if ($options['sender_affiliate_category_id'] == '') { echo ' selected="selected"'; } ?> id="sender_affiliate_category_id_default_option"><?php _e('Affiliation Manager\'s option', 'contact-manager'); ?></option>
<option value="0"<?php if ($options['sender_affiliate_category_id'] == '0') { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($options['sender_affiliate_category_id'] == $category->id ? ' selected="selected"' : '').'>'.kleor_do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if (function_exists('affiliation_data')) {
$ids_fields[] = 'sender_affiliate_category_id';
$applied_value = ($options['sender_affiliate_category_id'] == '' ? affiliation_data('affiliates_initial_category_id') : $options['sender_affiliate_category_id']);
echo '<span id="sender-affiliate-category-id-links">'.contact_manager_pages_field_links($back_office_options, 'sender_affiliate_category_id', $applied_value).'</span>'; } ?></td></tr>
<?php } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_affiliate_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_affiliate_status" id="sender_affiliate_status">
<option value=""<?php if ($options['sender_affiliate_status'] == '') { echo ' selected="selected"'; } ?> id="sender_affiliate_status_default_option"><?php _e('Affiliation Manager\'s option', 'contact-manager'); ?></option>
<option value="active"<?php if ($options['sender_affiliate_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($options['sender_affiliate_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/affiliation-manager/documentation/#affiliate-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="affiliation_registration_confirmation_email_sent"><?php _e('Send a registration confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="affiliation_registration_confirmation_email_sent" id="affiliation_registration_confirmation_email_sent">
<option value=""<?php if ($options['affiliation_registration_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?> id="affiliation_registration_confirmation_email_sent_default_option"><?php _e('Affiliation Manager\'s option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($options['affiliation_registration_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($options['affiliation_registration_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (((function_exists('affiliation_data')) && (current_user_can('view_affiliation_manager'))) ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=affiliation-manager#registration-confirmation-email') : _e('You can configure this email through the <em>Options</em> page of Affiliation Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="affiliation_registration_notification_email_sent"><?php _e('Send a registration notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="affiliation_registration_notification_email_sent" id="affiliation_registration_notification_email_sent">
<option value=""<?php if ($options['affiliation_registration_notification_email_sent'] == '') { echo ' selected="selected"'; } ?> id="affiliation_registration_notification_email_sent_default_option"><?php _e('Affiliation Manager\'s option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($options['affiliation_registration_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($options['affiliation_registration_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (((function_exists('affiliation_data')) && (current_user_can('view_affiliation_manager'))) ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=affiliation-manager#registration-notification-email') : _e('You can configure this email through the <em>Options</em> page of Affiliation Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="membership-module"<?php if (in_array('membership', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="membership"><strong><?php echo $modules['options']['membership']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php echo (((function_exists('membership_data')) && (current_user_can('view_membership_manager'))) ? '<a '.$default_options_links_markup.' href="admin.php?page=membership-manager">'.__('Click here to configure the options of Membership Manager.', 'contact-manager').'</a>' : str_replace('<a', '<a '.$documentations_links_markup, __('To use membership, you must have installed and activated <a href="https://www.kleor.com/membership-manager/">Membership Manager</a>.', 'contact-manager'))); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_to_members_areas" id="sender_subscribed_to_members_areas" value="yes"<?php if ($options['sender_subscribed_to_members_areas'] == 'yes') { echo ' checked="checked"'; } ?> /> 
<?php _e('Subscribe the sender to a member area', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#membership"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_members_areas"><?php _e('Members areas', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sender_members_areas" id="sender_members_areas" rows="1" cols="50" onkeyup="kleor_update_links(this.form); kleor_update_form(this.form);" onchange="kleor_update_links(this.form); kleor_update_form(this.form);"><?php echo $options['sender_members_areas']; ?></textarea>
<?php if (function_exists('membership_data')) {
$ids_fields[] = 'sender_members_areas';
echo '<span class="description" style="vertical-align: 25%;" id="sender-members-areas-description">'.contact_manager_pages_field_description('sender_members_areas', $options['sender_members_areas']).'</span>';
$links = contact_manager_pages_field_links($back_office_options, 'sender_members_areas', $options['sender_members_areas']); echo '<span id="sender-members-areas-links">'.$links.'</span>';
$string = '-member-area&amp;id='.$options['sender_members_areas']; $url = 'admin.php?page=membership-manager'.(strstr($links, $string) ? $string : ''); } ?><br />
<span class="description"><?php _e('You can enter several members areas IDs. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_members_areas_modifications"><?php _e('Automatic modifications', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 50%;" name="sender_members_areas_modifications" id="sender_members_areas_modifications" rows="2" cols="50" onchange="kleor_update_form(this.form);"><?php echo $options['sender_members_areas_modifications']; ?></textarea>
<span class="description"><?php _e('You can offer a temporary access, and automatically modify the list of members areas to which the member can access when a certain date is reached.', 'contact-manager'); ?>
 <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/membership-manager/documentation/#members-areas-modifications"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php if (kleor_get_option('membership_manager')) {
$categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."membership_manager_members_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_member_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_member_category_id" id="sender_member_category_id" onchange="kleor_update_form(this.form);">
<option value=""<?php if ($options['sender_member_category_id'] == '') { echo ' selected="selected"'; } ?> id="sender_member_category_id_default_option"><?php _e('Member area\'s option', 'contact-manager'); ?></option>
<option value="0"<?php if ($options['sender_member_category_id'] == '0') { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($options['sender_member_category_id'] == $category->id ? ' selected="selected"' : '').'>'.kleor_do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if (function_exists('membership_data')) {
$ids_fields[] = 'sender_member_category_id';
$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', $options['sender_members_areas'], 0, PREG_SPLIT_NO_EMPTY)));
if (count($members_areas) == 1) { $GLOBALS['member_area_id'] = (int) $members_areas[0]; }
else { $GLOBALS['member_area_id'] = 0; $GLOBALS['member_area_data'] = array(); }
$applied_value = ($options['sender_member_category_id'] == '' ? member_area_data('members_initial_category_id') : $options['sender_member_category_id']);
echo '<span id="sender-member-category-id-links">'.contact_manager_pages_field_links($back_office_options, 'sender_member_category_id', $applied_value).'</span>'; } ?></td></tr>
<?php } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_member_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_member_status" id="sender_member_status">
<option value=""<?php if ($options['sender_member_status'] == '') { echo ' selected="selected"'; } ?> id="sender_member_status_default_option"><?php _e('Member area\'s option', 'contact-manager'); ?></option>
<option value="active"<?php if ($options['sender_member_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($options['sender_member_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/membership-manager/documentation/#member-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="membership_registration_confirmation_email_sent"><?php _e('Send a registration confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="membership_registration_confirmation_email_sent" id="membership_registration_confirmation_email_sent">
<option value=""<?php if ($options['membership_registration_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?> id="membership_registration_confirmation_email_sent_default_option"><?php _e('Member area\'s option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($options['membership_registration_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($options['membership_registration_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (((function_exists('membership_data')) && (current_user_can('view_membership_manager'))) ? printf(str_replace('<a', '<a id="membership-registration-confirmation-email-sent-link" '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), $url.'#registration-confirmation-email') : _e('You can configure this email through the interface of Membership Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="membership_registration_notification_email_sent"><?php _e('Send a registration notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="membership_registration_notification_email_sent" id="membership_registration_notification_email_sent">
<option value=""<?php if ($options['membership_registration_notification_email_sent'] == '') { echo ' selected="selected"'; } ?> id="membership_registration_notification_email_sent_default_option"><?php _e('Member area\'s option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($options['membership_registration_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($options['membership_registration_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (((function_exists('membership_data')) && (current_user_can('view_membership_manager'))) ? printf(str_replace('<a', '<a id="membership-registration-notification-email-sent-link" '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), $url.'#registration-notification-email') : _e('You can configure this email through the interface of Membership Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="wordpress-module"<?php if (in_array('wordpress', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="wordpress"><strong><?php echo $modules['options']['wordpress']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_as_a_user" id="sender_subscribed_as_a_user" value="yes"<?php if ($options['sender_subscribed_as_a_user'] == 'yes') { echo ' checked="checked"'; } ?> /> 
<?php _e('Register the sender as a user', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#wordpress"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_user_role"><?php _e('Role', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_user_role" id="sender_user_role">
<?php foreach (contact_manager_users_roles() as $role => $name) {
echo '<option value="'.$role.'"'.($options['sender_user_role'] == $role ? ' selected="selected"' : '').'>'.$name.'</option>'."\n"; } ?>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<?php $can_edit_plugins = (current_user_can('edit_plugins')); ?>
<div class="postbox" id="custom-instructions-module"<?php if (in_array('custom-instructions', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="custom-instructions"><strong><?php echo $modules['options']['custom-instructions']['name']; ?></strong></h3>
<div class="inside">
<div id="message-custom-instructions-module"<?php if (in_array('message-custom-instructions', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="message-custom-instructions"><strong><?php echo $modules['options']['custom-instructions']['modules']['message-custom-instructions']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_custom_instructions_executed" id="message_custom_instructions_executed" value="yes"<?php if ($options['message_custom_instructions_executed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Execute custom instructions', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_custom_instructions"><?php _e('PHP code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_custom_instructions" id="message_custom_instructions" rows="10" cols="75" <?php if ($can_edit_plugins) { echo 'placeholder="'.__('You can enter a PHP code or the path (relative to the directory in which your wp-config.php file is located) of a PHP file on your website. In the latter case, it\'s the PHP code of this file that will be executed.', 'contact-manager').'"'; } else { echo 'disabled="disabled" title="'.__('You don\'t have the capability required (edit_plugins) to change this code.', 'contact-manager').'"'; } ?>><?php echo htmlspecialchars(kleor_get_option('contact_manager_message_custom_instructions')); ?></textarea>
<span class="description"><?php _e('You can add custom instructions that will be executed just after the sending of a message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
<div id="message-removal-custom-instructions-module"<?php if (in_array('message-removal-custom-instructions', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="message-removal-custom-instructions"><strong><?php echo $modules['options']['custom-instructions']['modules']['message-removal-custom-instructions']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_removal_custom_instructions_executed" id="message_removal_custom_instructions_executed" value="yes"<?php if ($options['message_removal_custom_instructions_executed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Execute custom instructions', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_removal_custom_instructions"><?php _e('PHP code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_removal_custom_instructions" id="message_removal_custom_instructions" rows="10" cols="75" <?php if ($can_edit_plugins) { echo 'placeholder="'.__('You can enter a PHP code or the path (relative to the directory in which your wp-config.php file is located) of a PHP file on your website. In the latter case, it\'s the PHP code of this file that will be executed.', 'contact-manager').'"'; } else { echo 'disabled="disabled" title="'.__('You don\'t have the capability required (edit_plugins) to change this code.', 'contact-manager').'"'; } ?>><?php echo htmlspecialchars(kleor_get_option('contact_manager_message_removal_custom_instructions')); ?></textarea>
<span class="description"><?php _e('You can add custom instructions that will be executed just after the removal of a message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
<div id="form-submission-custom-instructions-module"<?php if (in_array('form-submission-custom-instructions', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="form-submission-custom-instructions"><strong><?php echo $modules['options']['custom-instructions']['modules']['form-submission-custom-instructions']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="form_submission_custom_instructions_executed" id="form_submission_custom_instructions_executed" value="yes"<?php if ($options['form_submission_custom_instructions_executed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Execute custom instructions', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="form_submission_custom_instructions"><?php _e('PHP code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="form_submission_custom_instructions" id="form_submission_custom_instructions" rows="10" cols="75" <?php if ($can_edit_plugins) { echo 'placeholder="'.__('You can enter a PHP code or the path (relative to the directory in which your wp-config.php file is located) of a PHP file on your website. In the latter case, it\'s the PHP code of this file that will be executed.', 'contact-manager').'"'; } else { echo 'disabled="disabled" title="'.__('You don\'t have the capability required (edit_plugins) to change this code.', 'contact-manager').'"'; } ?>><?php echo htmlspecialchars(kleor_get_option('contact_manager_form_submission_custom_instructions')); ?></textarea>
<span class="description"><?php _e('You can add custom instructions that will be executed just after the submission of a form.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
</div></div>

<div class="postbox" id="affiliation-module"<?php if (in_array('affiliation', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="affiliation"><strong><?php echo $modules['options']['affiliation']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('affiliation_data')) { _e('You can award a commission to the affiliate who referred a message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/contact-manager/#affiliation"><?php _e('More informations', 'contact-manager'); ?></a><?php }
else { echo str_replace('<a', '<a '.$documentations_links_markup, __('To use affiliation, you must have installed and activated <a href="https://www.kleor.com/affiliation-manager/">Affiliation Manager</a>.', 'contact-manager')); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="affiliation_enabled" id="affiliation_enabled" value="yes"<?php if ($options['affiliation_enabled'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Use affiliation', 'contact-manager'); ?></label>
<span class="description" style="vertical-align: -5%;"><?php _e('Uncheck this box allows you to disable the award of commissions.', 'contact-manager'); ?></span></td></tr>
</tbody></table>
<div id="level-1-commission-module"<?php if (in_array('level-1-commission', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="level-1-commission"><strong><?php echo $modules['options']['affiliation']['modules']['level-1-commission']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('The level 1 commission is awarded to the affiliate who referred the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/affiliation-manager/documentation/#commissions-levels"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission_amount"><?php _e('Amount', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="commission_amount" id="commission_amount" rows="1" cols="25" onchange="kleor_update_form(this.form);"><?php echo $options['commission_amount']; ?></textarea> <span style="vertical-align: 25%;"><?php echo $currency_code; ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
<div id="level-2-commission-module"<?php if (in_array('level-2-commission', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="level-2-commission"><strong><?php echo $modules['options']['affiliation']['modules']['level-2-commission']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('The level 2 commission is awarded to the referrer of the affiliate who referred the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="https://www.kleor.com/affiliation-manager/documentation/#commissions-levels"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="commission2_enabled" id="commission2_enabled" value="yes"<?php if ($options['commission2_enabled'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Award a level 2 commission', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission2_amount"><?php _e('Amount', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="commission2_amount" id="commission2_amount" rows="1" cols="25" onchange="kleor_update_form(this.form);"><?php echo $options['commission2_amount']; ?></textarea> <span style="vertical-align: 25%;"><?php echo $currency_code; ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
</div></div>

<p class="submit"><input type="submit" class="button-primary" name="submit" id="submit" value="<?php _e('Save Changes', 'contact-manager'); ?>" /></p>
<?php contact_manager_pages_module($back_office_options, 'options-page', $undisplayed_modules); ?>
</form>
</div>
</div>

<script>
<?php $modules_list = array(); $submodules = array();
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
element.style.display = "block"; subelement.style.display = "block"; } } }'."\n"; ?>

<?php $fields = array(); $strings = array();
foreach (array(
'encrypted_urls_validity_duration',
'commission_amount',
'commission2_amount') as $field) { $fields[] = $field; $strings[$field] = "this.value.replace(/[?,;]/g, '.')"; }
foreach (array('automatic_display_form_id') as $field) { $fields[] = $field; $strings[$field] = "kleor_format_integer(this.value)"; }
echo 'fields = '.json_encode($fields).';
strings = '.json_encode($strings).';
for (i = 0, n = fields.length; i < n; i++) {
element = document.getElementById(fields[i]);
if ((element) && ((element.type == "text") || (element.type == "textarea"))) {
events = ["onchange","onkeyup"]; for (j = 0; j < 2; j++) {
if (element.hasAttribute(events[j])) { string = " "+element.getAttribute(events[j]); } else { string = ""; }
element.setAttribute(events[j], "this.value = "+strings[fields[i]]+";"+string); } } }'."\n"; ?>

<?php echo 'fields = []; default_values = [];'."\n";
foreach ($initial_options[''] as $key => $value) {
$value = (string) $value; if (($value != '') && (!in_array($key, array('automatic_display_maximum_forms_quantity', 'maximum_messages_quantity')))) {
echo 'fields.push("'.$key.'"); default_values["'.$key.'"] = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), $value).'";'."\n"; } }
foreach ($other_options as $field) {
$value = (string) $initial_options[$field]; if ($value != '') {
echo 'fields.push("'.$field.'"); default_values["'.$field.'"] = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), $value).'";'."\n"; } }
echo 'for (i = 0, n = fields.length; i < n; i++) {
element = document.getElementById(fields[i]);
if ((element) && ((element.type == "text") || (element.type == "textarea"))) {
element.setAttribute("data-default", default_values[fields[i]]);
if (element.hasAttribute("onchange")) { string = " "+element.getAttribute("onchange"); } else { string = ""; }
element.setAttribute("onchange", "if (this.value === \'\') { this.value = this.getAttribute(\'data-default\'); }"+string); } }'."\n";

if (function_exists('commerce_data')) {
$default_options = (array) kleor_get_option('commerce_manager');
include contact_path('libraries/api-fields.php');
echo 'fields = []; default_values = [];'."\n";
foreach ($api_fields as $field) { if (isset($default_options[$field])) {
echo 'fields.push("'.$field.'"); default_values["'.$field.'"] = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), $default_options[$field]).'";'."\n"; } }
echo 'for (i = 0, n = fields.length; i < n; i++) {
element = document.getElementById(fields[i]);
element.setAttribute("data-default", default_values[fields[i]]); if (element.value === "") { element.setAttribute("data-empty", "yes"); element.style.color = "#a0a0a0"; element.value = default_values[fields[i]]; }
if (element.hasAttribute("onfocus")) { string = " "+element.getAttribute("onfocus"); } else { string = ""; }
element.setAttribute("onfocus", "this.setAttribute(\'data-focused\', \'yes\'); if (this.getAttribute(\'data-empty\') == \'yes\') { this.style.color = \'\'; this.value = \'\'; }"+string);
events = ["onblur","onchange"]; for (j = 0; j < 2; j++) {
if (element.hasAttribute(events[j])) { string = " "+element.getAttribute(events[j]); } else { string = ""; }
element.setAttribute(events[j], "if (this.getAttribute(\'data-focused\') == \'yes\') { this.setAttribute(\'data-focused\', \'no\'); if (this.value === \'\') { this.setAttribute(\'data-empty\', \'yes\'); this.style.color = \'#a0a0a0\'; this.value = this.getAttribute(\'data-default\'); } else { this.setAttribute(\'data-empty\', \'no\'); } }"+string); } }'."\n"; }

$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', $options['sender_members_areas'], 0, PREG_SPLIT_NO_EMPTY)));
if (count($members_areas) == 1) { $GLOBALS['member_area_id'] = (int) $members_areas[0]; }
else { $GLOBALS['member_area_id'] = 0; $GLOBALS['member_area_data'] = array(); }
$fields = array(); $default_options = array();
foreach (array('category_id', 'status') as $field) {
$fields[] = 'sender_client_'.$field; $default_options['sender_client_'.$field] = (function_exists('commerce_data') ? commerce_data('clients_initial_'.$field) : '');
$fields[] = 'sender_affiliate_'.$field; $default_options['sender_affiliate_'.$field] = (function_exists('affiliation_data') ? affiliation_data('affiliates_initial_'.$field) : '');
$fields[] = 'sender_member_'.$field; $default_options['sender_member_'.$field] = (function_exists('member_area_data') ? member_area_data('members_initial_'.$field) : ''); }
foreach (array('confirmation', 'notification') as $action) {
$fields[] = 'commerce_registration_'.$action.'_email_sent'; $default_options['commerce_registration_'.$action.'_email_sent'] = (function_exists('commerce_data') ? commerce_data('registration_'.$action.'_email_sent') : '');
$fields[] = 'affiliation_registration_'.$action.'_email_sent'; $default_options['affiliation_registration_'.$action.'_email_sent'] = (function_exists('affiliation_data') ? affiliation_data('registration_'.$action.'_email_sent') : '');
$fields[] = 'membership_registration_'.$action.'_email_sent'; $default_options['membership_registration_'.$action.'_email_sent'] = (function_exists('member_area_data') ? member_area_data('registration_'.$action.'_email_sent') : ''); }
foreach ($fields as $field) { echo 'element = document.getElementById("'.$field.'_default_option"); if (element) { element.innerHTML = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), contact_manager_pages_selector_default_option_content($field, $default_options[$field])).'"; }'."\n"; } ?>

<?php $fields = array();
foreach ($initial_options[''] as $key => $value) { $fields[] = $key; }
$fields = array_merge($fields, $other_options);
echo 'kleor_update_form_call_number = 0;
function kleor_update_form(form) {
kleor_update_form_call_number += 1;
data = {}; fields = '.json_encode($fields).';
for (i = 0, n = fields.length; i < n; i++) {
if ((typeof form[fields[i]] == "object") && ((form[fields[i]].getAttribute("data-empty") != "yes") || (form[fields[i]].getAttribute("data-focused") == "yes"))) {
if (form[fields[i]].type != "checkbox") { data[fields[i]] = form[fields[i]].value; }
else { if (form[fields[i]].checked == true) { data[fields[i]] = "yes"; } } } }
data["other_options"] = '.json_encode($other_options).';
ids_fields = '.json_encode($ids_fields).'; data["ids_fields"] = ids_fields;
data["kleor_update_form_call_number"] = kleor_update_form_call_number;
jQuery.post("'.HOME_URL.'?plugin=contact-manager&action=update-form&page='.$_GET['page'].'&key='.md5(AUTH_KEY).'", data, function(data) {
if (data["kleor_update_form_call_number"] == kleor_update_form_call_number) {
for (i = 0, n = fields.length; i < n; i++) {
if ((typeof form[fields[i]] == "object") && (typeof data[fields[i]] != "undefined") && (fields[i] != document.activeElement.name)) {
if (form[fields[i]].type != "checkbox") { form[fields[i]].value = data[fields[i]]; }
else { if (data[fields[i]] == "yes") { form[fields[i]].checked = true; } else { form[fields[i]].checked = false; } } } }
fields = ["sender_member_category_id","sender_member_status","membership_registration_confirmation_email_sent","membership_registration_notification_email_sent"];
for (i = 0, n = fields.length; i < n; i++) {
var element = document.getElementById(fields[i]+"_default_option");
if ((element) && (typeof data[fields[i]+"_default_option_content"] != "undefined")) { element.innerHTML = data[fields[i]+"_default_option_content"]; } }
var strings = ["description","links"];
for (i = 0, n = ids_fields.length; i < n; i++) { for (j = 0; j < 2; j++) {
var key = ids_fields[i]+"_"+strings[j];
var element = document.getElementById(key.replace(/[_]/g, "-"));
if ((element) && (typeof data[key] != "undefined")) { element.innerHTML = data[key]; } } }
kleor_update_links(form); jQuery(".noscript").css("display", "none"); } }, "json"); }'."\n"; ?>

<?php echo 'function search_automatic_display_form_id(form) {
var search = encodeURIComponent(form.automatic_display_form_id_search.value);
if (search != "") {
var value = form.automatic_display_form_id.value;
var element = document.getElementById("automatic_display_form_id_selector");
jQuery.get("'.contact_url('index.php').'?action=search-automatic-display-form-id'.(is_multisite() ? '&blog_id='.get_current_blog_id() : '').'&lang='.strtolower(substr(get_locale(), 0, 2)).'&search="+search+"&value="+value+"&key='.md5(DB_NAME.DB_PASSWORD).'", function(data) {
if (data.indexOf("<select") >= 0) {
element.innerHTML = data;
if (form.automatic_display_form_id.value != value) { kleor_update_form(form); } } }); } }'."\n"; ?>

<?php echo 'function kleor_update_links(form) {
var fields = '.json_encode($urls_fields).';
for (i = 0, n = fields.length; i < n; i++) {
var element = document.getElementById(fields[i].replace(/[_]/g, "-")+"-link");
if (element) {
var urls = form[fields[i]].value.split(","); var url = kleor_format_url(urls[0].replace(/[ ]/g, ""));
if (url == "") { element.innerHTML = ""; }
else { element.innerHTML = \'<a style="vertical-align: 25%;" '.$urls_fields_links_markup.' href="\'+url.replace(/[&]/g, "&amp;")+\'">'.__('Link', 'contact-manager').'</a>\'; } } }
'.(!function_exists('membership_data') ? '' : 'var field = form["sender_members_areas"]; if (field) {
var url = "admin.php?page=membership-manager";
var element = document.getElementById("sender-members-areas-links");
if ((field.value != "") && (field.value != 0) && (element.innerHTML.indexOf("id="+field.value) >= 0)) { url += "-member-area&id="+field.value; }
var actions = ["confirmation","notification"]; for (i = 0; i < 2; i++) {
var element = document.getElementById("membership-registration-"+actions[i]+"-email-sent-link");
if (element) { element.href = url+"#registration-"+actions[i]+"-email"; } } }').' }'."\n"; ?>

function kleor_preview_code(form, field) {
if (window.ActiveXObject) {
try { var xhr = new ActiveXObject("Msxml2.XMLHTTP"); }
catch(e) { var xhr = new ActiveXObject("Microsoft.XMLHTTP"); } }
else { var xhr = new XMLHttpRequest(); }
xhr.onreadystatechange = function() {
if (xhr.readyState == 4) { window.open("<?php echo HOME_URL; ?>/?plugin=contact-manager&action=preview&field="+field, "_blank"); } };
xhr.open("POST", "<?php echo contact_url('index.php'); ?>?action=set-preview-variables");
data = new FormData();
data.append(field, form[field].value);
xhr.send(data); }

document.getElementById("code_preview_button").innerHTML = '<input type="button" class="button-tertiary" value="<?php _e('Preview', 'contact-manager'); ?>" onclick="kleor_preview_code(this.form, \'code\');" />';

<?php echo 'function kleor_validate_form(form) {
for (i = 0, n = form.length; i < n; i++) { if (form[i].getAttribute("data-empty") == "yes") { form[i].value = ""; } }
return true; }'."\n"; ?>
</script>
<?php }
<?php if (!defined('ABSPATH')) { exit(); }
global $wpdb; $error = '';
$options = (array) kleor_get_option('contact_manager_back_office');
extract(contact_manager_pages_links_markups($options));
$admin_page = 'back_office';
foreach (array('admin-pages.php', 'initial-options.php') as $file) { include contact_path($file); }
$max_links = count($admin_links);
$max_menu_items = count($admin_pages);

$roles = array();
$current_user = wp_get_current_user();
$roles['current_user'] = (array) $current_user->roles;
foreach (array('required', 'manage', 'view') as $string) { $roles[$string] = array(); }
$wp_roles = new WP_Roles();
foreach ($wp_roles->role_objects as $key => $role) {
if (($key == 'administrator') || ($role->has_cap('activate_plugins'))) {
foreach (array('required', 'manage', 'view') as $string) {
$roles[$string][] = $key; if (($string != 'required') && (!$role->has_cap($string.'_contact_manager'))) { $role->add_cap($string.'_contact_manager'); } } }
elseif ($role->has_cap('manage_contact_manager')) {
foreach (array('manage', 'view') as $string) { $roles[$string][] = $key; }
if (in_array($key, $roles['current_user'])) { $roles['required'][] = $key; }
if (!$role->has_cap('view_contact_manager')) { $role->add_cap('view_contact_manager'); } }
elseif ($role->has_cap('view_contact_manager')) { $roles['view'][] = $key; } }

if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!current_user_can('manage_contact_manager')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
foreach ($_POST as $key => $value) {
if (is_string($value)) { $_POST[$key] = stripslashes(html_entity_decode(str_replace(array('&nbsp;', '&#91;', '&#93;'), array(' ', '&amp;#91;', '&amp;#93;'), $value))); } }
foreach ($initial_options['back_office'] as $key => $value) { if (!isset($_POST[$key])) { $_POST[$key] = ''; } }
foreach (array(
'custom_icon_used',
'links_displayed',
'menu_displayed',
'title_displayed') as $field) { if ($_POST[$field] != 'yes') { $_POST[$field] = 'no'; } }
foreach (array(
'back_office',
'form',
'form_category',
'message',
'options') as $page) { update_contact_manager_back_office($options, $page); }
foreach ($wp_roles->role_objects as $key => $role) {
foreach (array('manage', 'view') as $string) {
if ((in_array($key, $roles['required'])) || ((isset($_POST[$key.'_can_'.$string])) && ($_POST[$key.'_can_'.$string] == 'yes'))) {
$_POST[$key.'_can_view'] = 'yes'; $role->add_cap($string.'_contact_manager'); if (!in_array($key, $roles[$string])) { $roles[$string][] = $key; } }
elseif ($role->has_cap($string.'_contact_manager')) {
$role->remove_cap($string.'_contact_manager'); if (in_array($key, $roles[$string])) { unset($roles[$string][array_search($key, $roles[$string])]); } } } }
if (isset($_POST['reset_links'])) {
foreach (array('links', 'displayed_links') as $field) { $_POST[$field] = $initial_options['back_office'][$field]; } }
else {
$_POST['links'] = array();
$_POST['displayed_links'] = array();
for ($i = 0; $i < $max_links; $i++) {
$_POST['links'][$i] = $_POST['link'.$i];
if (isset($_POST['link'.$i.'_displayed'])) { $_POST['displayed_links'][] = $i; } } }
if (isset($_POST['reset_menu_items'])) {
foreach (array('menu_items', 'menu_displayed_items') as $field) { $_POST[$field] = $initial_options['back_office'][$field]; } }
else {
$_POST['menu_items'] = array();
$_POST['menu_displayed_items'] = array();
for ($i = 0; $i < $max_menu_items; $i++) {
$_POST['menu_items'][$i] = $_POST['menu_item'.$i];
if (isset($_POST['menu_item'.$i.'_displayed'])) { $_POST['menu_displayed_items'][] = $i; } } }
foreach (array('default_options', 'documentations', 'ids_fields', 'pages_modules', 'urls_fields') as $string) {
$_POST[$string.'_links_target'] = (isset($_POST[$string.'_links_targets_opened_in_new_tab']) ? '_blank' : '_self'); }
$_POST['statistics_page_undisplayed_columns'] = array();
foreach ($statistics_columns as $key => $value) {
if ((!isset($_POST['statistics_page_'.$key.'_column_displayed'])) && ((!isset($value['required'])) || ($value['required'] != 'yes'))) { $_POST['statistics_page_undisplayed_columns'][] = $key; } }
$_POST['statistics_page_undisplayed_rows'] = array();
foreach ($statistics_rows as $key => $value) {
if ((!isset($_POST['statistics_page_'.$key.'_row_displayed'])) && ((!isset($value['required'])) || ($value['required'] != 'yes'))) { $_POST['statistics_page_undisplayed_rows'][] = $key; } }
foreach ($initial_options['back_office'] as $key => $value) { if (!in_array($key, array('next_page_url', 'pages_viewed'))) { if ($_POST[$key] == '') { $_POST[$key] = $value; } $options[$key] = $_POST[$key]; } }
include contact_path('libraries/questions.php');
$a = array(); foreach ($default_answers as $key => $value) {
$a[$key] = (isset($_POST[$key.'_question']) ? ($_POST[$key.'_question'] == 'yes' ? true : false) : $value); }
kleor_update_option('contact_manager_answers', $a);
if (isset($_POST['reoptimize_interface'])) {
include contact_path('initial-options.php');
foreach ($initial_options['back_office'] as $key => $value) { if (!in_array($key, array('next_page_url', 'pages_viewed'))) { $_POST[$key] = $value; $options[$key] = $_POST[$key]; } } }
kleor_update_option('contact_manager_back_office', $options); } }

$undisplayed_modules = (array) $options['back_office_page_undisplayed_modules']; ?>

<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($options); ?>
<?php if (isset($_POST['submit'])) { echo '<div class="updated-notice"><p><strong>'.__('Settings saved.', 'contact-manager').'</strong></p></div>'; } ?>
<form method="post" name="<?php echo esc_attr($_GET['page']); ?>" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<?php contact_manager_pages_menu($options); ?>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<?php contact_manager_pages_summary($options); ?>

<div class="postbox" id="icon-module"<?php if (in_array('icon', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="icon"><strong><?php echo $modules['back_office']['icon']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="custom_icon_used" id="custom_icon_used" value="yes"<?php if ($options['custom_icon_used'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Use a custom icon', 'contact-manager'); ?></label>
<span class="description" style="vertical-align: -5%;"><?php _e('Icon displayed in the admin menu of WordPress', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="custom_icon_url"><?php _e('Icon URL', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="custom_icon_url" id="custom_icon_url" rows="1" cols="75" onkeyup="document.getElementById('custom-icon-url-link').href = kleor_format_url(this.value);" onchange="document.getElementById('custom-icon-url-link').href = kleor_format_url(this.value);"><?php echo $options['custom_icon_url']; ?></textarea> 
<span style="vertical-align: 25%;"><a id="custom-icon-url-link" target="<?php echo $options['urls_fields_links_target']; ?>" href="<?php echo htmlspecialchars(kleor_format_url(kleor_do_shortcode($options['custom_icon_url']))); ?>"><?php _e('Link', 'contact-manager'); ?></a>
<?php if (current_user_can('upload_files')) { echo '<br /><a class="select-image" data-id="custom_icon_url" target="'.$options['urls_fields_links_target'].'" href="media-new.php" title="'.__('After the upload, you will just need to copy and paste the URL of the image in this field.', 'contact-manager').'">'.__('Upload an image', 'contact-manager').'</a>'; } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="capabilities-module"<?php if (in_array('capabilities', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="capabilities"><strong><?php echo $modules['back_office']['capabilities']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<?php $checkboxes = array('manage' => '', 'view' => '');
foreach (contact_manager_users_roles() as $role => $name) {
foreach (array('manage', 'view') as $string) {
if (in_array($role, $roles['required'])) {
if ($role == 'administrator') { $title = __('You can\'t uncheck this role.', 'contact-manager'); }
elseif (in_array($role, $roles['current_user'])) { $title = __('You can\'t uncheck your own role.', 'contact-manager'); }
else { $title = __('You can\'t uncheck the roles able to activate plugins.', 'contact-manager'); }
$checkboxes[$string] .= '<label title="'.$title.'"><input type="checkbox" name="'.$role.'_can_'.$string.'" id="'.$role.'_can_'.$string.'" value="yes" checked="checked" disabled="disabled" /> '.$name.'</label><br />'; }
else {
if ($string == 'manage') { $onchange = 'if (this.checked == true) { this.form[\''.$role.'_can_view\'].checked = true; }'; }
else { $onchange = 'if (this.checked == false) { this.form[\''.$role.'_can_manage\'].checked = false; }'; }
$checkboxes[$string] .= '<label><input type="checkbox" name="'.$role.'_can_'.$string.'" id="'.$role.'_can_'.$string.'" value="yes" onchange="'.$onchange.'"'.(in_array($role, $roles[$string]) ? ' checked="checked"' : '').' /> '.$name.'</label><br />'; } } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Management', 'contact-manager'); ?></strong></th>
<td><span style="float: left; margin-right: 5em;"><?php echo $checkboxes['manage']; ?></span>
<p class="description"><?php _e('Roles able to change the options and add, edit or delete elements of Contact Manager', 'contact-manager'); ?></p></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Access', 'contact-manager'); ?></strong></th>
<td><span style="float: left; margin-right: 5em;"><?php echo $checkboxes['view']; ?></span>
<p class="description"><?php _e('Roles able to view the interface of Contact Manager', 'contact-manager'); ?></p></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="answers-module"<?php if (in_array('answers', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="answers"><strong><?php echo $modules['back_office']['answers']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;">
<td style="width: 100%;"><span class="description"><?php _e('If you want to reoptimize the interface of Contact Manager according to your answers, click on the <em>Reoptimize the interface</em> button at the bottom of this module.', 'contact-manager'); ?></span></td></tr>
</tbody></table>
<table class="form-table"><tbody>
<?php $a = (array) kleor_get_option('contact_manager_answers');
include contact_path('libraries/questions.php');
foreach ($default_answers as $key => $value) { if (!isset($a[$key])) { $a[$key] = $value; } }
$fields = array();
foreach ($questions as $key => $value) { $last = $key; }
foreach ($questions as $key => $value) { $fields[] = $key.'_question'; ?>
<tr style="<?php if ($key != $last) { echo 'border-bottom: 1px solid #b8d0e8; '; } ?>vertical-align: top;">
<th scope="row" style="width: 60%;">
<label id="<?php echo $key; ?>_question_label" style="cursor: default;"><strong style="font-weight: 700;"><?php echo $value['label']; ?></strong>
<?php if (isset($value['description'])) { echo '<br /><span class="description" style="color: #606060;">'.$value['description'].'</span>'; } ?>
<input type="hidden" name="<?php echo $key; ?>_question" id="<?php echo $key; ?>_question" value="<?php echo ($a[$key] ? 'yes' : 'no'); ?>" /></label></th>
<td class="answer" style="text-align: center; width: 20%;"><span id="<?php echo $key; ?>_question_yes_button" class="yes-button" style="opacity: <?php echo ($a[$key] ? 1 : 0.4); ?>;" onclick="this_form.<?php echo $key; ?>_question.value = 'yes'; this.style.opacity = 1; document.getElementById('<?php echo $key; ?>_question_no_button').style.opacity = 0.4;" onmouseover="this.style.opacity = 1;" onmouseout="if (this_form.<?php echo $key; ?>_question.value == 'yes') { this.style.opacity = 1; document.getElementById('<?php echo $key; ?>_question_no_button').style.opacity = 0.4; } else { this.style.opacity = 0.4; document.getElementById('<?php echo $key; ?>_question_no_button').style.opacity = 1; }"><?php _e('Yes', 'contact-manager'); ?></span></td>
<td class="answer" style="text-align: center; width: 20%;"><span id="<?php echo $key; ?>_question_no_button" class="no-button" style="opacity: <?php echo ($a[$key] ? 0.4 : 1); ?>;" onclick="this_form.<?php echo $key; ?>_question.value = 'no'; this.style.opacity = 1; document.getElementById('<?php echo $key; ?>_question_yes_button').style.opacity = 0.4;" onmouseover="this.style.opacity = 1;" onmouseout="if (this_form.<?php echo $key; ?>_question.value == 'yes') { this.style.opacity = 0.4; document.getElementById('<?php echo $key; ?>_question_yes_button').style.opacity = 1; } else { this.style.opacity = 1; document.getElementById('<?php echo $key; ?>_question_yes_button').style.opacity = 0.4; }"><?php _e('No', 'contact-manager'); ?></span></td>
</tr>
<?php } ?>
</tbody></table>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="reoptimize_interface" value="<?php _e('Reoptimize the interface', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<script>this_form = document.forms['<?php echo $_GET['page']; ?>'];</script>

<div class="postbox" id="top-module"<?php if (in_array('top', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="top"><strong><?php echo $modules['back_office']['top']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="title_displayed" id="title_displayed" value="yes"<?php if ($options['title_displayed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Display the title', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="title"><?php _e('Title', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="title" id="title" rows="1" cols="50"><?php echo $options['title']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="links_displayed" id="links_displayed" value="yes"<?php if ($options['links_displayed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Display the links', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Links', 'contact-manager'); ?></strong></th>
<td ondragover="event.preventDefault();"><input type="hidden" name="submit" value="true" /><input style="margin-bottom: 0.5em;" type="submit" class="button-secondary" name="reset_links" formaction="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>#top" value="<?php _e('Reset the links', 'contact-manager'); ?>" />
<div id="undraggable-links">
<?php $displayed_links = (array) $options['displayed_links'];
$draggable_links = $keys = array();
$j = 0; $k = count(array_unique($displayed_links));
for ($i = 0; $i < $max_links; $i++) {
if (!in_array($options['links'][$i], $keys)) {
$keys[] = $options['links'][$i];
$item = array('key' => $options['links'][$i], 'name' => $admin_links[$options['links'][$i]]['name'], 'displayed' => (in_array($i, $displayed_links)));
if ($item['displayed']) { $draggable_links[$j] = $item; $j = $j + 1; } else { $draggable_links[$k] = $item; $k = $k + 1; } } }
foreach ($admin_links as $key => $value) {
if (!in_array($key, $keys)) { $draggable_links[] = array('key' => $key, 'name' => $value['name'], 'displayed' => false); } }
for ($i = 0; $i < $max_links; $i++) {
echo '<label><span style="margin-right: 0.3em;">'.__('Link', 'contact-manager').' '.($i + 1).'</span> <select style="margin-right: 0.3em;" name="link'.$i.'" id="link'.$i.'">';
foreach ($admin_links as $key => $value) { echo '<option value="'.$key.'"'.($draggable_links[$i]['key'] == $key ? ' selected="selected"' : '').'>'.$value['name'].'</option>'."\n"; }
echo '</select></label>
<label><input type="checkbox" name="link'.$i.'_displayed" id="link'.$i.'_displayed" value="yes"'.($draggable_links[$i]['displayed'] ? ' checked="checked"' : '').' /> '.__('Display', 'contact-manager').'</label><br />'; } ?>
</div><div id="draggable-links"></div></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="menu-module"<?php if (in_array('menu', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="menu"><strong><?php echo $modules['back_office']['menu']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="menu_displayed" id="menu_displayed" value="yes"<?php if ($options['menu_displayed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Display the menu', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Pages', 'contact-manager'); ?></strong></th>
<td ondragover="event.preventDefault();"><input type="hidden" name="submit" value="true" /><input style="margin-bottom: 0.5em;" type="submit" class="button-secondary" name="reset_menu_items" formaction="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>#menu" value="<?php _e('Reset the pages', 'contact-manager'); ?>" />
<div id="undraggable-pages">
<?php $menu_displayed_items = (array) $options['menu_displayed_items'];
$draggable_pages = $keys = array();
$j = 0; $k = count(array_unique($menu_displayed_items));
for ($i = 0; $i < $max_menu_items; $i++) {
if (!in_array($options['menu_items'][$i], $keys)) {
$keys[] = $options['menu_items'][$i];
$item = array('key' => $options['menu_items'][$i], 'name' => $admin_pages[$options['menu_items'][$i]]['menu_title'], 'displayed' => (in_array($i, $menu_displayed_items)));
if ($item['displayed']) { $draggable_pages[$j] = $item; $j = $j + 1; } else { $draggable_pages[$k] = $item; $k = $k + 1; } } }
foreach ($admin_pages as $key => $value) {
if (!in_array($key, $keys)) { $draggable_pages[] = array('key' => $key, 'name' => $value['menu_title'], 'displayed' => false); } }
for ($i = 0; $i < $max_menu_items; $i++) {
echo '<label><span style="margin-right: 0.3em;">'.__('Page', 'contact-manager').' '.($i + 1).'</span> <select style="margin-right: 0.3em;" name="menu_item'.$i.'" id="menu_item'.$i.'">';
foreach ($admin_pages as $key => $value) { echo '<option value="'.$key.'"'.($draggable_pages[$i]['key'] == $key ? ' selected="selected"' : '').'>'.$value['menu_title'].'</option>'."\n"; }
echo '</select></label>
<label><input type="checkbox" name="menu_item'.$i.'_displayed" id="menu_item'.$i.'_displayed" value="yes"'.($draggable_pages[$i]['displayed'] ? ' checked="checked"' : '').' /> '.__('Display', 'contact-manager').'</label><br />'; } ?>
</div><div id="draggable-pages"></div></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="links-module"<?php if (in_array('links', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="links"><strong><?php echo $modules['back_office']['links']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Open in a new tab the targets of the links', 'contact-manager'); ?></strong></th>
<td><?php foreach (array(
'documentations' => __('pointing to the documentation', 'contact-manager'),
'default_options' => __('allowing to configure the default options', 'contact-manager'),
'ids_fields' => __('below the fields allowing to enter an ID', 'contact-manager'),
'urls_fields' => __('next to the fields allowing to enter a URL', 'contact-manager'),
'pages_modules' => __('at the top of the modules of this page', 'contact-manager')) as $key => $value) {
$name = $key.'_links_targets_opened_in_new_tab';
echo '<label><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes"'.($options[$key.'_links_target'] != '_blank' ? '' : ' checked="checked"').' /> '.$value.'</label><br />'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<?php foreach (array(
'options-page',
'form-page',
'form-category-page',
'message-page') as $module) { contact_manager_pages_module($options, $module, $undisplayed_modules); } ?>

<div class="postbox" id="statistics-page-module"<?php if (in_array('statistics-page', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="statistics-page"><strong><?php echo $modules['back_office']['statistics-page']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a target="<?php echo $options['pages_modules_links_target']; ?>" href="admin.php?page=contact-manager-statistics"><?php _e('Click here to open this page.', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Columns displayed', 'contact-manager'); ?></strong></th>
<td><?php foreach ($statistics_columns as $key => $value) {
$name = 'statistics_page_'.$key.'_column_displayed';
if ((!isset($value['title'])) || ($value['title'] == '')) {
if ((isset($value['required'])) && ($value['required'] == 'yes')) { $title = ' title="'.__('You can\'t disable the display of this column.', 'contact-manager').'"'; }
else { $title = ''; } }
else { $title = ' title="'.$value['title'].'"'; }
$undisplayed_columns = (array) $options['statistics_page_undisplayed_columns'];
if ((isset($value['required'])) && ($value['required'] == 'yes')) { echo '<label'.$title.'><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes" checked="checked" disabled="disabled" /> '.$value['name'].'</label><br />'; }
else { echo '<label'.$title.'><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes"'.(in_array($key, $undisplayed_columns) ? '' : ' checked="checked"').' /> '.$value['name'].'</label><br />'; } } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Rows displayed', 'contact-manager'); ?></strong></th>
<td><?php foreach ($statistics_rows as $key => $value) {
$name = 'statistics_page_'.$key.'_row_displayed';
if ((!isset($value['title'])) || ($value['title'] == '')) {
if ((isset($value['required'])) && ($value['required'] == 'yes')) { $title = ' title="'.__('You can\'t disable the display of this row.', 'contact-manager').'"'; }
else { $title = ''; } }
else { $title = ' title="'.$value['title'].'"'; }
$undisplayed_rows = (array) $options['statistics_page_undisplayed_rows'];
if ((isset($value['required'])) && ($value['required'] == 'yes')) { echo '<label'.$title.'><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes" checked="checked" disabled="disabled" /> '.$value['name'].'</label><br />'; }
else { echo '<label'.$title.'><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes"'.(in_array($key, $undisplayed_rows) ? '' : ' checked="checked"').' /> '.$value['name'].'</label><br />'; } } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<?php contact_manager_pages_module($options, 'back-office-page', $undisplayed_modules); ?>
<p class="submit"><input type="submit" class="button-primary" name="submit" id="submit" value="<?php _e('Save Changes', 'contact-manager'); ?>" /></p>
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

<?php $fields = array('custom_icon_url', 'title'); echo 'fields = '.json_encode($fields).'; default_values = [];'."\n";
foreach ($fields as $field) { echo 'default_values["'.$field.'"] = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), $initial_options['back_office'][$field]).'";'."\n"; }
echo 'for (i = 0, n = fields.length; i < n; i++) {
element = document.getElementById(fields[i]);
element.setAttribute("data-default", default_values[fields[i]]);
if (element.hasAttribute("onchange")) { string = " "+element.getAttribute("onchange"); } else { string = ""; }
element.setAttribute("onchange", "if (this.value === \'\') { this.value = this.getAttribute(\'data-default\'); }"+string); }'."\n"; ?>

<?php foreach (array('reset_links' => 'top', 'reset_menu_items' => 'menu') as $field => $location) { if (isset($_POST[$field])) { echo 'window.location = \'#'.$location.'\';'; } }
foreach ($modules as $key => $value) {
if ((isset($value['custom-fields'])) && ((isset($_POST['add_'.$key.'_page_custom_field'])) || (isset($_POST['delete_'.$key.'_page_custom_field'])))) {
echo 'window.location = \'#'.str_replace('_', '-', $key).'-page-custom-fields-module\';'; } } ?>

var div = document.createElement('div');
if (('draggable' in div) || ((('ondragstart' in div) && ('ondrop' in div)))) {
<?php echo file_get_contents(contact_path('libraries/drag-drop-touch.js')); //Useful for mobile devices and touchscreens ?>
document.getElementById('undraggable-links').style.display = 'none';
link_position = dragged_link = 0;
link_width = 25; link_height = 2.5; link_margin = link_height/5;
draggable_links = <?php echo json_encode($draggable_links); ?>;
update_draggable_links();

function update_draggable_links() {
if (dragged_link < link_position) { link_position = link_position - 1; }
if (dragged_link != link_position) {
var new_draggable_links = [];
new_draggable_links[link_position] = draggable_links[dragged_link];
var j = 0; for (i = 0; i < <?php echo $max_links; ?>; i++) {
if (j == link_position) { j = j + 1; }
if (i != dragged_link) { new_draggable_links[j] = draggable_links[i]; j = j + 1; } }
draggable_links = new_draggable_links;
for (i = 0; i < <?php echo $max_links; ?>; i++) {
document.getElementById('link'+i).value = draggable_links[i]['key'];
document.getElementById('link'+i+'_displayed').checked = draggable_links[i]['displayed']; } }
var content = "<p class=\"description\"><?php _e('You can change the order of the links by Drag-and-Drop:', 'contact-manager'); ?></p>"+link_dropzone(0);
for (i = 0; i < <?php echo $max_links; ?>; i++) {
content += '<div id="draggable-link'+i+'" style="background-color: #ffffff; border: 1px solid #c0c0c0; cursor: move; width: '+link_width+'em; min-height: '+link_height+'em; max-width: 80%;" draggable="true" ondragenter="update_link_dropzones('+i+');" ondragstart="dragged_link = '+i+'; dragged_content = this.innerHTML; this.style.color = \'#808080\'; this.style.border = \'1px dashed #c0c0c0\';" ondragend="update_draggable_links();">'
+'<div style="float: left; max-width: 70%; padding: '+link_margin+'em;"><span style="vertical-align: 25%;">'+draggable_links[i]['name']+'</span></div>'
+'<div style="float: right; padding: '+link_margin+'em;"><label style="vertical-align: 25%;"><input type="checkbox" value="yes"'+(draggable_links[i]['displayed'] ? ' checked="checked"' : '')+' onclick="this.checked = !draggable_links['+i+'][\'displayed\']; document.getElementById(\'link'+i+'_displayed\').checked = draggable_links['+i+'][\'displayed\'] = this.checked;" /> <?php _e('Display', 'contact-manager'); ?></label></div>'
+'<div style="clear: both;"></div></div>'+link_dropzone(i + 1); }
document.getElementById("draggable-links").innerHTML = content; }

function link_dropzone(i) { return '<div id="link-dropzone'+i+'" style="width: '+link_width+'em; height: '+link_margin+'em; max-width: 80%;" ondragenter="update_link_dropzones('+i+');" ondragend="update_draggable_links();"></div>'; }

function update_link_dropzones(j) {
link_position = dragged_link;
for (i = 0; i <= <?php echo $max_links; ?>; i++) {
var dropzone = document.getElementById('link-dropzone'+i);
if ((i == j) && (dragged_link != i) && ((dragged_link + 1) != i)) {
link_position = i;
dropzone.setAttribute('style', 'background-color: #ffffff; border: 1px dashed #c0c0c0; color: #404040; margin: '+link_margin+'em 0; width: '+link_width+'em; min-height: '+link_height+'em; max-width: 80%;');
dropzone.innerHTML = dragged_content; }
else { dropzone.setAttribute('style', 'width: '+link_width+'em; height: '+link_margin+'em; max-width: 80%;'); dropzone.innerHTML = ''; } }
var link = document.getElementById('draggable-link'+dragged_link);
if (link_position == dragged_link) { link.style.backgroundColor = '#ffffff'; link.style.color = '#404040'; }
else { link.style.backgroundColor = ''; link.style.color = '#808080'; } }

document.getElementById('undraggable-pages').style.display = 'none';
page_position = dragged_page = 0;
page_width = 25; page_height = 2.5; page_margin = page_height/5;
draggable_pages = <?php echo json_encode($draggable_pages); ?>;
update_draggable_pages();

function update_draggable_pages() {
if (dragged_page < page_position) { page_position = page_position - 1; }
if (dragged_page != page_position) {
var new_draggable_pages = [];
new_draggable_pages[page_position] = draggable_pages[dragged_page];
var j = 0; for (i = 0; i < <?php echo $max_menu_items; ?>; i++) {
if (j == page_position) { j = j + 1; }
if (i != dragged_page) { new_draggable_pages[j] = draggable_pages[i]; j = j + 1; } }
draggable_pages = new_draggable_pages;
for (i = 0; i < <?php echo $max_menu_items; ?>; i++) {
document.getElementById('menu_item'+i).value = draggable_pages[i]['key'];
document.getElementById('menu_item'+i+'_displayed').checked = draggable_pages[i]['displayed']; } }
var content = "<p class=\"description\"><?php _e('You can change the order of the pages by Drag-and-Drop:', 'contact-manager'); ?></p>"+page_dropzone(0);
for (i = 0; i < <?php echo $max_menu_items; ?>; i++) {
content += '<div id="draggable-page'+i+'" style="background-color: #ffffff; border: 1px solid #c0c0c0; cursor: move; width: '+page_width+'em; min-height: '+page_height+'em; max-width: 80%;" draggable="true" ondragenter="update_page_dropzones('+i+');" ondragstart="dragged_page = '+i+'; dragged_content = this.innerHTML; this.style.color = \'#808080\'; this.style.border = \'1px dashed #c0c0c0\';" ondragend="update_draggable_pages();">'
+'<div style="float: left; max-width: 70%; padding: '+page_margin+'em;"><span style="vertical-align: 25%;">'+draggable_pages[i]['name']+'</span></div>'
+'<div style="float: right; padding: '+page_margin+'em;"><label style="vertical-align: 25%;"><input type="checkbox" value="yes"'+(draggable_pages[i]['displayed'] ? ' checked="checked"' : '')+' onclick="this.checked = !draggable_pages['+i+'][\'displayed\']; document.getElementById(\'menu_item'+i+'_displayed\').checked = draggable_pages['+i+'][\'displayed\'] = this.checked;" /> <?php _e('Display', 'contact-manager'); ?></label></div>'
+'<div style="clear: both;"></div></div>'+page_dropzone(i + 1); }
document.getElementById("draggable-pages").innerHTML = content; }

function page_dropzone(i) { return '<div id="page-dropzone'+i+'" style="width: '+page_width+'em; height: '+page_margin+'em; max-width: 80%;" ondragenter="update_page_dropzones('+i+');" ondragend="update_draggable_pages();"></div>'; }

function update_page_dropzones(j) {
page_position = dragged_page;
for (i = 0; i <= <?php echo $max_menu_items; ?>; i++) {
var dropzone = document.getElementById('page-dropzone'+i);
if ((i == j) && (dragged_page != i) && ((dragged_page + 1) != i)) {
page_position = i;
dropzone.setAttribute('style', 'background-color: #ffffff; border: 1px dashed #c0c0c0; color: #404040; margin: '+page_margin+'em 0; width: '+page_width+'em; min-height: '+page_height+'em; max-width: 80%;');
dropzone.innerHTML = dragged_content; }
else { dropzone.setAttribute('style', 'width: '+page_width+'em; height: '+page_margin+'em; max-width: 80%;'); dropzone.innerHTML = ''; } }
var page = document.getElementById('draggable-page'+dragged_page);
if (page_position == dragged_page) { page.style.backgroundColor = '#ffffff'; page.style.color = '#404040'; }
else { page.style.backgroundColor = ''; page.style.color = '#808080'; } } }

jQuery(document).ready(function() {
if ((typeof wp !== "undefined") && (wp.media) && (wp.media.editor)) {
jQuery(".select-image").html("<?php _e('Click here to select an image instead of entering a URL.', 'contact-manager'); ?>");
jQuery(".select-image").attr("title", "");
jQuery(".select-image").on("click", function(e) {
field_id = this.getAttribute("data-id");
e.preventDefault();
media_selector = wp.media.frames.file_frame = wp.media({
title: "<?php _e('Select Image', 'contact-manager'); ?>",
button: { text: "<?php _e('Select this image', 'contact-manager'); ?>"},
multiple: false});
media_selector.on('select', function() {
file = media_selector.state().get('selection').first().toJSON();
document.getElementById(field_id).value = file.url;
document.getElementById(field_id.replace(/[_]/g, "-")+"-link").href = file.url; });
media_selector.open(); }); } });
</script>
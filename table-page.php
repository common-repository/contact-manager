<?php if (!defined('ABSPATH')) { exit(); }
global $wpdb; $error = '';
$back_office_options = (array) kleor_get_option('contact_manager_back_office');
$table_slug = str_replace('-', '_', str_replace('contact-manager-', '', $_GET['page']));

if (isset($_POST['delete_items'])) {
switch ($table_slug) {
case 'forms': $deleted = __('Forms deleted.', 'contact-manager'); $question = __('Do you really want to permanently delete these forms?', 'contact-manager'); break;
case 'forms_categories': $deleted = __('Categories deleted.', 'contact-manager'); $question = __('Do you really want to permanently delete these categories?', 'contact-manager'); break;
case 'messages': $deleted = __('Messages deleted.', 'contact-manager'); $question = __('Do you really want to permanently delete these messages?', 'contact-manager'); }
if ((isset($_POST['confirm'])) && (check_admin_referer($_GET['page']))) {
if (!current_user_can('manage_contact_manager')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
$ids = array_map('intval', explode(',', $_POST['ids']));
if ($table_slug == 'messages') {
foreach ($ids as $id) {
$message_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_messages WHERE id = ".$id, OBJECT);
$GLOBALS['message_data'] = (array) $message_data;
$GLOBALS['referrer'] = $GLOBALS['message_data']['referrer'];
$GLOBALS['contact_form_id'] = $GLOBALS['message_data']['form_id'];
$results = $wpdb->query("DELETE FROM ".$wpdb->prefix."contact_manager_messages WHERE id = ".$id);
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
else { eval($instructions); } } } } }
else {
if ($table_slug == 'forms_categories') {
foreach ($ids as $id) {
$category = $wpdb->get_row("SELECT category_id FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE id = ".$id, OBJECT);
foreach (array('forms', 'forms_categories') as $table) {
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_".$table." SET category_id = ".$category->category_id." WHERE category_id = ".$id); } } }
$results = $wpdb->query("DELETE FROM ".$wpdb->prefix."contact_manager_".$table_slug." WHERE id IN ('".implode("','", $ids)."')"); } } } ?>
<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if (isset($_POST['confirm'])) {
echo '<div class="updated-notice"><p><strong>'.$deleted.'</strong></p></div>
<script>setTimeout(\'window.location = "admin.php?page='.$_GET['page'].'"\', 2000);</script>'; } ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<?php if (!isset($_POST['confirm'])) {
$ids = array(); foreach ($_POST as $key => $value) { if (($value == 'yes') && (substr($key, 0, 11) == 'check_item_')) { $ids[] = (int) substr($key, 11); } } ?>
<form method="post" name="<?php echo $_GET['page']; ?>" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<div class="alignleft actions">
<p style="font-size: 1.2em;"><strong style="color: #c00000;"><?php echo $question; ?></strong></p>
<p><input type="hidden" name="delete_items" value="true" /><input type="hidden" name="ids" value="<?php echo implode(',', $ids); ?>" />
<input type="submit" class="button-secondary" name="confirm" id="confirm" value="<?php _e('Yes', 'contact-manager'); ?>" />
<span class="description"><?php _e('This action is irreversible.', 'contact-manager'); ?></span></p>
</div>
<div class="clear"></div>
</form><?php } ?>
</div>
</div>
<?php }

else {
include contact_path('tables.php');
include_once contact_path('tables-functions.php');
$option_name = str_replace('-', '_', $_GET['page']);
$options = (array) kleor_get_option($option_name);
if (!current_user_can('manage_contact_manager')) {
if ((isset($_SESSION[$option_name.'_'.$_SERVER['REMOTE_ADDR']])) && (is_array($_SESSION[$option_name.'_'.$_SERVER['REMOTE_ADDR']]))) { $options = $_SESSION[$option_name.'_'.$_SERVER['REMOTE_ADDR']]; }
else { @session_start(); $_SESSION[$option_name.'_'.$_SERVER['REMOTE_ADDR']] = $options; session_write_close(); } }
$table_name = contact_manager_table_name($table_slug);
$custom_fields = (array) $back_office_options[contact_manager_single_page_slug($table_slug).'_page_custom_fields'];
foreach ($custom_fields as $key => $value) { $custom_fields[$key] = kleor_do_shortcode($value); }
asort($custom_fields); foreach ($custom_fields as $key => $value) {
$tables[$table_slug]['custom_field_'.$key] = array('modules' => array('custom-fields'), 'name' => $value, 'width' => 18); }
foreach ($tables[$table_slug] as $key => $value) { if (!isset($value['name'])) { unset($tables[$table_slug][$key]); } }
$max_columns = count($tables[$table_slug]);
$undisplayed_keys = contact_manager_table_undisplayed_keys($tables, $table_slug, $back_office_options);
foreach ($tables[$table_slug] as $key => $value) {
if ((isset($value['searchby'])) && (!in_array($key, $undisplayed_keys))) { $searchby_options[$key] = $value['searchby']; } }
if ((!isset($_GET['orderby'])) || (!isset($tables[$table_slug][$_GET['orderby']]))) { $_GET['orderby'] = $options['orderby']; }
if (!isset($_GET['order'])) { $_GET['order'] = ''; }
switch ($_GET['order']) { case 'asc': case 'desc': break; default: $_GET['order'] = $options['order']; }

if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
foreach ($_POST as $key => $value) {
if (is_string($value)) { $_POST[$key] = stripslashes($value); } }
$_GET['s'] = $_POST['s'];
if (isset($_POST['reset_columns'])) {
include contact_path('initial-options.php');
$columns = $initial_options[$table_slug]['columns'];
$displayed_columns = $initial_options[$table_slug]['displayed_columns']; }
else {
$displayed_columns = array();
for ($i = 0; $i < $max_columns; $i++) {
if (isset($_POST['column'.$i])) { $columns[$i] = $_POST['column'.$i]; }
if ((isset($_POST['column'.$i.'_displayed'])) && ($_POST['column'.$i.'_displayed'] == 'yes')) { $displayed_columns[] = $i; } }
if (count($displayed_columns) == 0) { $displayed_columns = (array) $options['displayed_columns']; } }
$columns_list_displayed = (isset($_POST['columns_list_displayed']) ? 'yes' : 'no');
$limit = (int) ($_POST['limit'] != '' ? $_POST['limit'] : $_POST['old_limit']);
if ($limit > 1000) { $limit = 1000; }
elseif ($limit < 1) { $limit = $options['limit']; }
$searchby = $_POST['searchby'];
$start_date = ($_POST['start_date'] != '' ? $_POST['start_date'] : $_POST['old_start_date']);
$end_date = ($_POST['end_date'] != '' ? $_POST['end_date'] : $_POST['old_end_date']); }
else {
if (isset($_GET['start_date'])) { $start_date = $_GET['start_date']; }
else { $start_date = $options['start_date']; }
if (isset($_GET['end_date'])) { $end_date = $_GET['end_date']; }
else { $end_date = date('Y-m-d H:i:s', time() + 3600*UTC_OFFSET); }
$columns = (array) $options['columns'];
for ($i = 0; $i < $max_columns; $i++) {
if ((!isset($columns[$i])) || (!isset($tables[$table_slug][$columns[$i]]))) { $columns[$i] = 'id'; } }
$columns_list_displayed = $options['columns_list_displayed'];
$displayed_columns = (array) $options['displayed_columns'];
$limit = $options['limit'];
$searchby = $options['searchby']; }

if ($limit < 1) { $limit = 1; }
if ($start_date == '') { $start_date = $options['start_date']; }
else {
$d = preg_split('#[^0-9]#', $start_date, 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? 1 : 0)); }
$start_date = date('Y-m-d H:i:s', mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0])); }
if ($end_date == '') { $end_date = date('Y-m-d H:i:s', time() + 3600*UTC_OFFSET); }
else {
$d = preg_split('#[^0-9]#', $end_date, 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? 1 : ($i == 3 ? 23 : 59))); }
$end_date = date('Y-m-d H:i:s', mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0])); }
$GLOBALS['date_criteria'] = str_replace(' ', '%20', '&amp;start_date='.$start_date.'&amp;end_date='.$end_date);
$date_criteria = "(date BETWEEN '$start_date' AND '$end_date')";

if ($options) {
$options = array(
'columns' => $columns,
'columns_list_displayed' => $columns_list_displayed,
'displayed_columns' => $displayed_columns,
'limit' => $limit,
'order' => $_GET['order'],
'orderby' => $_GET['orderby'],
'searchby' => $searchby,
'start_date' => $start_date);
if (current_user_can('manage_contact_manager')) { kleor_update_option($option_name, $options); }
else { @session_start(); $_SESSION[$option_name.'_'.$_SERVER['REMOTE_ADDR']] = $options; session_write_close(); } }

$GLOBALS['criteria'] = $GLOBALS['date_criteria'].$GLOBALS['selection_criteria'];

$GLOBALS['search_criteria'] = ''; $search_criteria = ''; $search_column = false;
if ((isset($_GET['s'])) && ($_GET['s'] != '')) {
if ($searchby == '') {
foreach ($tables[$table_slug] as $key => $value) {
if (substr($key, 0, 13) != 'custom_field_') { $search_criteria .= " OR ".$key." LIKE '%".$_GET['s']."%'"; } }
$search_criteria = substr($search_criteria, 4); }
else {
$search_column = true; for ($i = 0; $i < $max_columns; $i++) {
if ((in_array($i, $displayed_columns)) && ($searchby == $columns[$i])) { $search_column = false; } }
$search_criteria = $searchby." LIKE '%".$_GET['s']."%'"; }
$GLOBALS['search_criteria'] = '&amp;s='.str_replace('+', '%20', urlencode($_GET['s']));
$search_criteria = 'AND ('.$search_criteria.')';
$GLOBALS['criteria'] .= $GLOBALS['search_criteria']; }

$query = $wpdb->get_row("SELECT count(*) as total FROM $table_name WHERE $date_criteria $selection_criteria $search_criteria", OBJECT);
$n = (int) $query->total;
$_GET['paged'] = (int) (((isset($_REQUEST['paged'])) && ($_REQUEST['paged'] != '')) ? $_REQUEST['paged'] : (isset($_REQUEST['old_paged']) ? $_REQUEST['old_paged'] : 1));
if ($_GET['paged'] < 1) { $_GET['paged'] = 1; }
$max_paged = ceil($n/$limit);
if ($max_paged < 1) { $max_paged = 1; }
if ($_GET['paged'] > $max_paged) { $_GET['paged'] = $max_paged; }
$start = ($_GET['paged'] - 1)*$limit;

if ($n > 0) {
switch ($_GET['orderby']) {
case 'id': case 'category_id': case 'date': case 'date_utc': case 'displays_count':
case 'ip_address': case 'maximum_messages_quantity_per_sender': case 'messages_count':
case 'referrer': case 'referring_url': case 'user_agent': $sorting_method = 'basic'; break;
default: $sorting_method = 'advanced'; }
if (($table_slug == 'messages') && (substr($_GET['orderby'], 0, 13) != 'custom_field_')) { $sorting_method = 'basic'; }

if ($sorting_method == 'basic') { $items = $wpdb->get_results("SELECT * FROM $table_name WHERE $date_criteria $selection_criteria $search_criteria ORDER BY ".$_GET['orderby']." ".strtoupper($_GET['order'])." LIMIT $start, $limit", OBJECT); }
else {
$items = $wpdb->get_results("SELECT * FROM $table_name WHERE $date_criteria $selection_criteria $search_criteria", OBJECT);
foreach ($items as $item) { $all_datas[$item->id] = $item; $datas[$item->id] = contact_manager_table_data($table_slug, $_GET['orderby'], $item); }
if ($_GET['order'] == 'asc') { asort($datas); } else { arsort($datas); }
$array = array(); foreach ($datas as $key => $value) { $array[] = array('id' => $key, 'data' => $value); }
$ids = array(); for ($i = $start; $i < $start + $limit; $i++) { if (isset($array[$i])) { $ids[] = $array[$i]['id']; } }
$items = array(); foreach ($ids as $id) { $items[] = $all_datas[$id]; }
$orderby = $_GET['orderby']; foreach ($items as $item) { $item->$orderby = $datas[$item->id]; } } } ?>

<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($back_office_options); ?>
<form method="post" name="<?php echo esc_attr($_GET['page']); ?>" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php contact_manager_pages_search_field('search', $searchby, $searchby_options); ?>
<?php contact_manager_pages_date_picker($start_date, $end_date); ?>
<div class="tablenav top">
<div class="alignleft actions">
<?php if ($n > 0) { echo '<span id="delete_items1_button"></span>'; } ?>
<?php _e('Display', 'contact-manager'); ?> <input type="hidden" name="old_limit" value="<?php echo $limit; ?>" /><input style="text-align: center;" type="text" name="limit" id="limit" size="2" value="<?php echo $limit; ?>" onfocus="this.value = '';" onblur="if (this.value == '') { this.value = <?php echo $limit; ?>; }" onkeyup="this.value = kleor_format_integer(this.value);" onchange="this.value = kleor_format_integer(this.value); if ((this.value == '') || (this.value == 0)) { this.value = <?php echo $limit; ?>; } if (this.value > 1000) { this.value = 1000; }" /> 
<?php switch ($table_slug) {
case 'forms': $singular = __('form', 'contact-manager'); $plural = __('forms', 'contact-manager'); break;
case 'forms_categories': $singular = __('category', 'contact-manager'); $plural = __('categories', 'contact-manager'); break;
case 'messages': $singular = __('message', 'contact-manager'); $plural = __('messages', 'contact-manager'); break;
default: $singular = __('item', 'contact-manager'); $plural = __('items', 'contact-manager'); }
echo ($limit == 1 ? $singular : $plural).' '.__('per page', 'contact-manager'); ?> <input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" />
</div><?php contact_manager_tablenav_pages($table_slug, $n, $max_paged, 'top'); ?></div>
<div style="clear: both; overflow: auto;">
<table class="wp-list-table widefat">
<?php if ($search_column) { $search_table_th = contact_manager_table_th($tables, $table_slug, $searchby); $table_ths = $search_table_th; } else { $table_ths = ''; }
$columns_displayed = array();
$original_displayed_columns = $displayed_columns;
foreach ($displayed_columns as $key => $value) {
if (in_array($columns[$value], $columns_displayed)) { unset($displayed_columns[$key]); }
$columns_displayed[] = $columns[$value]; }
for ($i = 0; $i < $max_columns; $i++) { if (in_array($i, $displayed_columns)) { $table_ths .= contact_manager_table_th($tables, $table_slug, $columns[$i]); } }
if ($table_ths != '') {
if ($n > 0) {
$check_all_items1_input = '<th scope="col" class="manage-column" style="margin-left: 0; padding-left: 0.15em; text-align: left; width: 2.5%;"><input type="checkbox" name="check_all_items1" id="check_all_items1" value="yes" onchange="check_all_items_js(1);" /></th>';
$check_all_items2_input = str_replace(array('check_all_items1', '(1)'), array('check_all_items2', '(2)', ''), $check_all_items1_input); }
echo '<thead><tr>'.($n > 0 ? $check_all_items1_input : '').$table_ths.'</tr></thead><tfoot><tr>'.($n > 0 ? $check_all_items2_input : '').$table_ths.'</tr></tfoot>'; } ?>
<tbody id="the-list">
<?php $boolean = false; if ($n > 0) { foreach ($items as $item) {
$table_tds = '';
$check_item_input = '<td style="margin-left: 0.25em; text-align: left; width: 2.5%;"><input type="checkbox" name="check_item_'.$item->id.'" id="check_item_'.$item->id.'" value="yes" onchange="display_delete_items_buttons_js();" /></td>';
if ($search_column) { $search_table_td = '<td>'.contact_manager_table_td($table_slug, $searchby, $item).'</td>'; } else { $search_table_td = ''; }
$first = true; for ($i = 0; $i < $max_columns; $i++) {
if (in_array($i, $displayed_columns)) {
$table_tds .= '<td'.($first ? ' style="height: 6em;"' : '').'>'.contact_manager_table_td($table_slug, $columns[$i], $item).($first ? contact_manager_row_actions($table_slug, $item) : '').'</td>';
$first = false; } }
echo '<tr'.($boolean ? '' : ' class="alternate"').'>'.$check_item_input.$search_table_td.$table_tds.'</tr>';
$table_tds = ''; $boolean = !$boolean; } }
else { echo '<tr class="no-items"><td class="colspanchange" colspan="'.count($displayed_columns).'">'.contact_manager_no_items($table_slug).'</td></tr>'; } ?>
</tbody>
</table>
</div>
<div class="tablenav bottom">
<?php contact_manager_tablenav_pages($table_slug, $n, $max_paged, 'bottom'); ?>
<div class="alignleft actions">
<div><input type="hidden" name="submit" value="true" />
<?php if ($n > 0) { echo '<span id="delete_items2_button"></span>'; } ?>
<?php $displayed_columns = $original_displayed_columns;
echo '<input style="margin-bottom: 0.5em; margin-right: 0.5em;" type="submit" class="button-secondary" name="submit" value="'.__('Update', 'contact-manager').'" />
<label style="margin-left: 1.5em;"><input type="checkbox" name="columns_list_displayed" id="columns_list_displayed" value="yes" 
onchange="if (this.checked == true) { value = \'yes\'; document.getElementById(\'columns-list\').style.display = \'\'; } else { value = \'no\'; document.getElementById(\'columns-list\').style.display = \'none\'; } jQuery.get(\''.HOME_URL.'?plugin=contact-manager&amp;action=update-options&amp;page='.$_GET['page'].'&amp;key='.md5(AUTH_KEY).'&amp;columns_list_displayed=\'+value);"
'.($columns_list_displayed == 'yes' ? ' checked="checked"' : '').' /> '.__('Display the columns list', 'contact-manager').'</label>'; ?></div>
<div id="columns-list" style="background-color: #eef4fa;<?php if ($columns_list_displayed == 'no') { echo ' display: none;'; } ?> margin: 1em 0; padding: 1em; width: 50em; max-width: 80vw;" ondragover="event.preventDefault();">
<div><?php $columns_inputs = '<input style="margin-bottom: 0.5em;" type="submit" class="button-secondary" name="reset_columns" value="'.__('Reset the columns', 'contact-manager').'" />
<input style="margin-bottom: 0.5em; margin-right: 0.5em;" type="submit" class="button-secondary" name="submit" value="'.__('Update', 'contact-manager').'" />
<span id="check-all-columns1-input"></span>'; echo $columns_inputs; ?></div>
<div id="undraggable-columns">
<?php $hidden_columns = $checked_columns = array();
for ($i = 0; $i < $max_columns; $i++) {
if (!isset($columns[$i])) { $columns[$i] = 'id'; }
if (in_array($i, $displayed_columns)) { $checked_columns[] = $columns[$i]; } }
foreach ($undisplayed_keys as $key) { if (!in_array($key, $checked_columns)) { $hidden_columns[] = $key; } }
$draggable_columns = $keys = array();
$j = 0; $k = count(array_unique($checked_columns));
for ($i = 0; $i < $max_columns; $i++) {
if ((!in_array($columns[$i], $keys)) && (!in_array($columns[$i], $hidden_columns))) {
$keys[] = $columns[$i];
$item = array('key' => $columns[$i], 'name' => $tables[$table_slug][$columns[$i]]['name'], 'displayed' => (in_array($i, $displayed_columns)));
if ($item['displayed']) { $draggable_columns[$j] = $item; $j = $j + 1; } else { $draggable_columns[$k] = $item; $k = $k + 1; } } }
foreach ($tables[$table_slug] as $key => $value) {
if ((!in_array($key, $keys)) && (!in_array($key, $hidden_columns))) { $draggable_columns[] = array('key' => $key, 'name' => $value['name'], 'displayed' => false); } }
$max_columns = count($draggable_columns);
$all_columns_checked = true;
for ($i = 0; $i < $max_columns; $i++) {
if (!$draggable_columns[$i]['displayed']) { $all_columns_checked = false; }
if ($i < 9) { $space = 1.5; } elseif ($i < 99) { $space = 0.9; } else { $space = 0.3; }
echo '<label><span style="margin-right: '.$space.'em;">'.__('Column', 'contact-manager').' '.($i + 1).'</span> <select style="float: none; max-width: 40em;" name="column'.$i.'" id="column'.$i.'">';
foreach ($tables[$table_slug] as $key => $value) {
if (!in_array($key, $hidden_columns)) { echo '<option value="'.$key.'"'.($draggable_columns[$i]['key'] == $key ? ' selected="selected"' : '').'>'.$value['name'].'</option>'."\n"; } }
echo '</select></label>
<label><input type="checkbox" name="column'.$i.'_displayed" id="column'.$i.'_displayed" value="yes"'.($draggable_columns[$i]['displayed'] ? ' checked="checked"' : '').' onchange="kleor_all_columns_checked_js();" /> '.__('Display', 'contact-manager').'</label><br />'; } ?>
</div><div id="draggable-columns"></div>
<div><?php echo str_replace(array('check-all-columns1', 'margin-bottom'), array('check-all-columns2', 'margin-top'), $columns_inputs); ?></div></div>
</div></div>
</form>
</div>
</div>

<?php $check_all_columns1_input = '<label id="check-all-columns1" style="margin-left: 0.5em;"><input type="checkbox" name="check_all_columns1" id="check_all_columns1" value="yes" onchange="kleor_check_all_columns_js(1);"'.($all_columns_checked ? ' checked="checked"' : '').' /> <span id="check_all_columns1_text">'.($all_columns_checked ? __('Uncheck all columns', 'contact-manager') : __('Check all columns', 'contact-manager')).'</span></label>';
$check_all_columns2_input = str_replace(array('check_all_columns1', 'check-all-columns1', '(1)'), array('check_all_columns2', 'check-all-columns2', '(2)'), $check_all_columns1_input);
$paging_input_bottom = '<input class="current-page" title="'.__('Current page', 'contact-manager').'" type="text" name="paged2" id="paged2" value="'.$_GET['paged'].'" size="2" onfocus="this.value = \\\'\\\';" onblur="if (this.value == \\\'\\\') { this.value = '.$_GET['paged'].'; }" onkeyup="this.value = kleor_format_integer(this.value); this.form.paged.value = this.value;" onchange="this.value = kleor_format_integer(this.value); if ((this.value == \\\'\\\') || (this.value == 0)) { this.value = '.$_GET['paged'].'; } if (this.value > '.$max_paged.') { this.value = '.$max_paged.'; } this.form.paged.value = this.value; if (this.value != '.$_GET['paged'].') { window.location = \\\'admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;orderby='.$_GET['orderby'].'&amp;order='.$_GET['order'].'&amp;paged=\\\'+this.value; }" />'; ?>

<script>
document.getElementById('check-all-columns1-input').innerHTML = '<?php echo $check_all_columns1_input; ?>';
document.getElementById('check-all-columns2-input').innerHTML = '<?php echo $check_all_columns2_input; ?>';
document.getElementById('paging-input-bottom').innerHTML = '<?php echo $paging_input_bottom; ?>';

function kleor_all_columns_checked_js() {
var checked = true;
for (i = 0; i < <?php echo $max_columns; ?>; i++) {
if (document.getElementById('column'+i+'_displayed').checked == false) { checked = false; } }
if (checked) { var text = '<?php _e('Uncheck all columns', 'contact-manager'); ?>'; }
else { var text = '<?php _e('Check all columns', 'contact-manager'); ?>'; }
for (i = 1; i <= 2; i++) {
document.getElementById('check_all_columns'+i).checked = checked;
document.getElementById('check_all_columns'+i+'_text').innerHTML = text; } }

function kleor_check_all_columns_js(i) {
var j = 3 - i;
var checked = document.getElementById('check_all_columns'+i).checked;
document.getElementById('check_all_columns'+j).checked = checked;
if (checked) { var text = '<?php _e('Uncheck all columns', 'contact-manager'); ?>'; }
else { var text = '<?php _e('Check all columns', 'contact-manager'); ?>'; }
for (i = 1; i <= 2; i++) { document.getElementById('check_all_columns'+i+'_text').innerHTML = text; }
for (i = 0; i < <?php echo $max_columns; ?>; i++) {
document.getElementById('column'+i+'_displayed').checked = checked;
var element = document.getElementById('draggable_column'+i+'_displayed');
if (element) { element.checked = checked; } } }<?php if ($n > 0) {
switch ($table_slug) {
case 'forms': $text = __('Delete the selected forms', 'contact-manager'); break;
case 'forms_categories': $text = __('Delete the selected categories', 'contact-manager'); break;
case 'messages': $text = __('Delete the selected messages', 'contact-manager'); }
$delete_items_button = '<input type="submit" class="button-secondary" name="delete_items" style="margin-right: 0.5em;" value="'.$text.'" />'; ?>

function check_all_items_js(i) {
var j = 3 - i;
var checked = document.getElementById('check_all_items'+i).checked;
document.getElementById('check_all_items'+j).checked = checked;
if (checked) { var button = '<?php echo $delete_items_button; ?>'; } else { var button = ''; }
for (i = 1; i <= 2; i++) { document.getElementById('delete_items'+i+'_button').innerHTML = button; }
checkboxes = document.querySelectorAll('input[type=checkbox]');
checkboxes.forEach((entry) => {
if (entry.name.substring(0, 11) == 'check_item_') { entry.checked = checked; } }); }

function display_delete_items_buttons_js() {
checked_items_number = 0;
unchecked_items_number = 0;
checkboxes = document.querySelectorAll('input[type=checkbox]');
checkboxes.forEach((entry) => {
if (entry.name.substring(0, 11) == 'check_item_') {
if (entry.checked) { checked_items_number = checked_items_number + 1; }
else { unchecked_items_number = unchecked_items_number + 1; } } });
if (checked_items_number > 0) { var button = '<?php echo $delete_items_button; ?>'; } else { var button = ''; }
for (i = 1; i <= 2; i++) {
document.getElementById('delete_items'+i+'_button').innerHTML = button;
document.getElementById('check_all_items'+i).checked = (unchecked_items_number == 0); } }
<?php } ?>

var div = document.createElement('div');
if (('draggable' in div) || ((('ondragstart' in div) && ('ondrop' in div)))) {
<?php echo file_get_contents(contact_path('libraries/drag-drop-touch.js')); //Useful for mobile devices and touchscreens ?>
document.getElementById('undraggable-columns').style.display = 'none';
column_position = dragged_column = 0;
column_width = 45; column_height = 2.5; column_margin = column_height/5;
draggable_columns = <?php echo json_encode($draggable_columns); ?>;
update_draggable_columns();

function update_draggable_columns() {
if (dragged_column < column_position) { column_position = column_position - 1; }
if (dragged_column != column_position) {
var new_draggable_columns = [];
new_draggable_columns[column_position] = draggable_columns[dragged_column];
var j = 0; for (i = 0; i < <?php echo $max_columns; ?>; i++) {
if (j == column_position) { j = j + 1; }
if (i != dragged_column) { new_draggable_columns[j] = draggable_columns[i]; j = j + 1; } }
draggable_columns = new_draggable_columns;
for (i = 0; i < <?php echo $max_columns; ?>; i++) {
document.getElementById('column'+i).value = draggable_columns[i]['key'];
document.getElementById('column'+i+'_displayed').checked = draggable_columns[i]['displayed']; } }
var content = "<p class=\"description\" style=\"color: #004080; font-size: 1.2em; font-weight: 600;\"><?php _e('You can change the order of the columns by Drag-and-Drop:', 'contact-manager'); ?></p>"+column_dropzone(0);
for (i = 0; i < <?php echo $max_columns; ?>; i++) {
content += '<div id="draggable-column'+i+'" style="background-color: #ffffff; border: 1px solid #c0c0c0; cursor: move; width: '+column_width+'em; min-height: '+column_height+'em; max-width: 95%;" draggable="true" ondragenter="update_column_dropzones('+i+');" ondragstart="dragged_column = '+i+'; dragged_content = this.innerHTML; this.style.color = \'#808080\'; this.style.border = \'1px dashed #c0c0c0\';" ondragend="update_draggable_columns();">'
+'<div style="float: left; max-width: 70%; padding: '+column_margin+'em;"><span style="vertical-align: 25%;">'+draggable_columns[i]['name']+'</span></div>'
+'<div style="float: right; padding: '+column_margin+'em;"><label style="vertical-align: 25%;"><input type="checkbox" name="draggable_column'+i+'_displayed" id="draggable_column'+i+'_displayed" value="yes"'+(draggable_columns[i]['displayed'] ? ' checked="checked"' : '')+' onclick="this.checked = !draggable_columns['+i+'][\'displayed\']; document.getElementById(\'column'+i+'_displayed\').checked = draggable_columns['+i+'][\'displayed\'] = this.checked; kleor_all_columns_checked_js();" /> <?php _e('Display', 'contact-manager'); ?></label></div>'
+'<div style="clear: both;"></div></div>'+column_dropzone(i + 1); }
document.getElementById("draggable-columns").innerHTML = content; }

function column_dropzone(i) { return '<div id="column-dropzone'+i+'" style="width: '+column_width+'em; height: '+column_margin+'em; max-width: 95%;" ondragenter="update_column_dropzones('+i+');" ondragend="update_draggable_columns();"></div>'; }

function update_column_dropzones(j) {
column_position = dragged_column;
for (i = 0; i <= <?php echo $max_columns; ?>; i++) {
var dropzone = document.getElementById('column-dropzone'+i);
if ((i == j) && (dragged_column != i) && ((dragged_column + 1) != i)) {
column_position = i;
dropzone.setAttribute('style', 'background-color: #ffffff; border: 1px dashed #c0c0c0; color: #404040; margin: '+column_margin+'em 0; width: '+column_width+'em; min-height: '+column_height+'em; max-width: 95%;');
dropzone.innerHTML = dragged_content; }
else { dropzone.setAttribute('style', 'width: '+column_width+'em; height: '+column_margin+'em; max-width: 95%;'); dropzone.innerHTML = ''; } }
var column = document.getElementById('draggable-column'+dragged_column);
if (column_position == dragged_column) { column.style.backgroundColor = '#ffffff'; column.style.color = '#404040'; }
else { column.style.backgroundColor = ''; column.style.color = '#808080'; } } }
</script>
<?php }
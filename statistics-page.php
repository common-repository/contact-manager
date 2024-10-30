<?php if (!defined('ABSPATH')) { exit(); }
global $wpdb; $error = '';
include contact_path('tables.php');
include_once contact_path('tables-functions.php');
$back_office_options = (array) kleor_get_option('contact_manager_back_office');
$undisplayed_rows = (array) $back_office_options['statistics_page_undisplayed_rows'];
$undisplayed_columns = (array) $back_office_options['statistics_page_undisplayed_columns'];
include contact_path('admin-pages.php');
$options = (array) kleor_get_option('contact_manager_statistics');

$tables_names = array(
'forms' => __('Forms', 'contact-manager'),
'forms_categories' => __('Forms categories', 'contact-manager'),
'messages' => __('Messages', 'contact-manager'));
$max_tables = count($tables_names);

$filterby_options = array(
'postcode' => __('postcode', 'contact-manager'),
'town' => __('town', 'contact-manager'),
'country' => __('country', 'contact-manager'),
'country_code' => __('country code', 'contact-manager'),
'ip_address' => __('IP address ', 'contact-manager'),
'user_agent' => __('browser', 'contact-manager'),
'referring_url' => __('referring URL', 'contact-manager'),
'form_id' => __('form ID', 'contact-manager'),
'referrer' => __('referrer', 'contact-manager'));

if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
foreach ($_POST as $key => $value) {
if (is_string($value)) { $_POST[$key] = stripslashes($value); } }
$_GET['s'] = $_POST['s'];
$filterby = $_POST['filterby'];
$start_date = ($_POST['start_date'] != '' ? $_POST['start_date'] : $_POST['old_start_date']);
$end_date = ($_POST['end_date'] != '' ? $_POST['end_date'] : $_POST['old_end_date']);
$displayed_tables = array();
for ($i = 0; $i < $max_tables; $i++) {
$tables_slugs[$i] = $_POST['table'.$i];
if (isset($_POST['table'.$i.'_displayed'])) { $displayed_tables[] = $i; } } }
else {
$displayed_tables = (array) $options['displayed_tables'];
$end_date = date('Y-m-d H:i:s', time() + 3600*UTC_OFFSET);
$filterby = $options['filterby'];
$start_date = $options['start_date'];
$tables_slugs = $options['tables']; }

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

if (($options) && (current_user_can('manage_contact_manager'))) {
$options = array(
'displayed_tables' => $displayed_tables,
'filterby' => $filterby,
'start_date' => $start_date,
'tables' => $tables_slugs);
kleor_update_option('contact_manager_statistics', $options); }

$GLOBALS['filter_criteria'] = ''; $filter_criteria = '';
if ((isset($_GET['s'])) && ($_GET['s'] != '')) {
$GLOBALS['filter_criteria'] = '&amp;'.$filterby.'='.str_replace('+', '%20', urlencode($_GET['s']));
$filter_criteria = (is_numeric($_GET['s']) ? "AND (".$filterby." = ".$_GET['s'].")" : "AND (".$filterby." = '".$_GET['s']."')"); }

$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE $date_criteria $selection_criteria $filter_criteria", OBJECT);
$messages_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms WHERE $date_criteria $selection_criteria $filter_criteria", OBJECT);
$forms_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE $date_criteria $selection_criteria $filter_criteria", OBJECT);
$forms_categories_number = (int) (isset($row->total) ? $row->total : 0);

$GLOBALS['criteria'] = $GLOBALS['date_criteria'].$GLOBALS['selection_criteria'].$GLOBALS['filter_criteria'];

$messages_a_tag = '<a style="text-decoration: none;" href="admin.php?page=contact-manager-messages'.$GLOBALS['criteria'].'">';
$forms_a_tag = '<a style="text-decoration: none;" href="admin.php?page=contact-manager-forms'.$GLOBALS['criteria'].'">';
$forms_categories_a_tag = '<a style="text-decoration: none;" href="admin.php?page=contact-manager-forms-categories'.$GLOBALS['criteria'].'">'; ?>

<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($back_office_options); ?>
<form method="post" name="<?php echo esc_attr($_GET['page']); ?>" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php contact_manager_pages_search_field('filter', $filterby, $filterby_options); ?>
<?php contact_manager_pages_date_picker($start_date, $end_date); ?>
<?php if (count($undisplayed_rows) < count($statistics_rows)) {
$global_table_ths = '';
foreach ($statistics_columns as $key => $value) {
if (!in_array($key, $undisplayed_columns)) { $global_table_ths .= '<th scope="col" class="manage-column" style="width: '.$value['width'].'%;">'.$value['name'].'</th>'; } }
echo '
<h3 style="font-size: 1.5em; padding-left: 0.125em;" id="global-statistics"><strong>'.__('Global statistics', 'contact-manager').'</strong></h3>
<table class="wp-list-table widefat" style="margin: 1em 0;">
<thead><tr>'.$global_table_ths.'</tr></thead>
<tfoot><tr>'.$global_table_ths.'</tr></tfoot>
<tbody>';
$boolean = false;
if (!in_array('messages', $undisplayed_rows)) { echo '
<tr'.($boolean ? '' : ' class="alternate"').'>
<td><strong>'.$statistics_rows['messages']['name'].'</strong></td>
'.(in_array('quantity', $undisplayed_columns) ? '' : '<td>'.$messages_a_tag.$messages_number.'</a></td>').'
</tr>'; $boolean = !$boolean; }
if (!in_array('forms', $undisplayed_rows)) { echo '
<tr'.($boolean ? '' : ' class="alternate"').'>
<td><strong>'.$statistics_rows['forms']['name'].'</strong></td>
'.(in_array('quantity', $undisplayed_columns) ? '' : '<td>'.$forms_a_tag.$forms_number.'</a></td>').'
</tr>'; $boolean = !$boolean; }
if (!in_array('forms_categories', $undisplayed_rows)) { echo '
<tr'.($boolean ? '' : ' class="alternate"').'>
<td><strong>'.$statistics_rows['forms_categories']['name'].'</strong></td>
'.(in_array('quantity', $undisplayed_columns) ? '' : '<td>'.$forms_categories_a_tag.$forms_categories_number.'</a></td>').'
</tr>'; $boolean = !$boolean; }
echo '</tbody></table>'; } ?>
<p class="description" style="margin: 0 0.5em;"><a href="admin.php?page=contact-manager-back-office#statistics-page"><?php _e('Click here to personalize this table.', 'contact-manager'); ?></a></p>
<div style="background-color: #eef4fa; margin: 1em auto; padding: 1em; text-align: center; width: 35em; max-width: 100%;" ondragover="event.preventDefault();">
<div id="undraggable-tables">
<?php $draggable_tables = $keys = array();
$j = 0; $k = count(array_unique($displayed_tables));
for ($i = 0; $i < $max_tables; $i++) {
if (!in_array($options['tables'][$i], $keys)) {
$keys[] = $options['tables'][$i];
$item = array('key' => $options['tables'][$i], 'name' => $tables_names[$options['tables'][$i]], 'displayed' => (in_array($i, $displayed_tables)));
if ($item['displayed']) { $draggable_tables[$j] = $item; $j = $j + 1; } else { $draggable_tables[$k] = $item; $k = $k + 1; } } }
foreach ($tables_names as $key => $value) {
if (!in_array($key, $keys)) { $draggable_tables[] = array('key' => $key, 'name' => $value, 'displayed' => false); } }
for ($i = 0; $i < $max_tables; $i++) {
echo '<label><span style="margin-right: 0.3em;">'.__('Table', 'contact-manager').' '.($i + 1).'</span> <select style="margin-right: 0.3em;" name="table'.$i.'" id="table'.$i.'">';
foreach ($tables_names as $key => $value) { echo '<option value="'.$key.'"'.($draggable_tables[$i]['key'] == $key ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; }
echo '</select></label>
<label><input type="checkbox" name="table'.$i.'_displayed" id="table'.$i.'_displayed" value="yes"'.($draggable_tables[$i]['displayed'] ? ' checked="checked"' : '').' /> '.__('Display', 'contact-manager').'</label><br />'; } ?>
</div><div id="draggable-tables"></div>
<div style="margin-top: 1em;"><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></div>
</div>
<?php $tables_displayed = array();
foreach ($displayed_tables as $key => $value) {
if (in_array($tables_slugs[$value], $tables_displayed)) { unset($displayed_tables[$key]); }
$tables_displayed[] = $tables_slugs[$value]; }
$summary = '';
if (count($displayed_tables) > 1) {
for ($i = 0; $i < $max_tables; $i++) {
if (in_array($i, $displayed_tables)) { $summary .= '<li>&nbsp;| <a href="#'.str_replace('_', '-', $tables_slugs[$i]).'">'.$tables_names[$tables_slugs[$i]].'</a></li>'; } }
$summary = '<ul class="subsubsub" style="float: none; text-align: center;">
<li>'.substr($summary, 12).'</ul>'; }
for ($i = 0; $i < $max_tables; $i++) {
if (in_array($i, $displayed_tables)) {
$table_slug = $tables_slugs[$i];
$table_name = contact_manager_table_name($table_slug);
$custom_fields = (array) $back_office_options[contact_manager_single_page_slug($table_slug).'_page_custom_fields'];
foreach ($custom_fields as $key => $value) { $custom_fields[$key] = kleor_do_shortcode($value); }
asort($custom_fields); foreach ($custom_fields as $key => $value) {
$tables[$table_slug]['custom_field_'.$key] = array('modules' => array('custom-fields'), 'name' => $value, 'width' => 18); }
$options = (array) kleor_get_option('contact_manager_'.$table_slug);
$columns = (array) $options['columns'];
$max_columns = count($columns);
for ($k = 0; $k < $max_columns; $k++) {
if (!isset($tables[$table_slug][$columns[$k]])) { $columns[$k] = 'id'; } }
$displayed_columns = (array) $options['displayed_columns'];
$table_ths = '';
for ($j = 0; $j < $max_columns; $j++) { if (in_array($j, $displayed_columns)) { $table_ths .= contact_manager_table_th($tables, $table_slug, $columns[$j]); } }
echo $summary.'
<h3 style="font-size: 1.5em; padding-left: 0.125em;" id="'.str_replace('_', '-', $tables_slugs[$i]).'"><strong>'.$tables_names[$tables_slugs[$i]].'</strong></h3>
<div style="overflow: auto;">
<table class="wp-list-table widefat" style="margin: 1em 0 2em 0;">
<thead><tr>'.$table_ths.'</tr></thead>
<tfoot><tr>'.$table_ths.'</tr></tfoot>
<tbody>';
$boolean = false;
$items = $wpdb->get_results("SELECT * FROM $table_name WHERE $date_criteria $selection_criteria $filter_criteria ORDER BY date DESC", OBJECT);
if ($items) { foreach ($items as $item) {
$table_tds = '';
$first = true; for ($j = 0; $j < $max_columns; $j++) {
if (in_array($j, $displayed_columns)) {
$table_tds .= '<td'.($first ? ' style="height: 6em;"' : '').'>'.contact_manager_table_td($table_slug, $columns[$j], $item).($first ? contact_manager_row_actions($table_slug, $item) : '').'</td>';
$first = false; } }
echo '<tr'.($boolean ? '' : ' class="alternate"').'>'.$table_tds.'</tr>';
$table_tds = ''; $boolean = !$boolean; } }
else { echo '<tr class="no-items"><td class="colspanchange" colspan="'.count($displayed_columns).'">'.contact_manager_no_items($table_slug).'</td></tr>'; }
echo '</tbody></table></div>';
$table_ths = ''; } } ?>
</form>
</div>
</div>

<script>
var div = document.createElement('div');
if (('draggable' in div) || ((('ondragstart' in div) && ('ondrop' in div)))) {
<?php echo file_get_contents(contact_path('libraries/drag-drop-touch.js')); //Useful for mobile devices and touchscreens ?>
document.getElementById('undraggable-tables').style.display = 'none';
table_position = dragged_table = 0;
table_width = 25; table_height = 2.5; table_margin = table_height/5;
draggable_tables = <?php echo json_encode($draggable_tables); ?>;
update_draggable_tables();

function update_draggable_tables() {
if (dragged_table < table_position) { table_position = table_position - 1; }
if (dragged_table != table_position) {
var new_draggable_tables = [];
new_draggable_tables[table_position] = draggable_tables[dragged_table];
var j = 0; for (i = 0; i < <?php echo $max_tables; ?>; i++) {
if (j == table_position) { j = j + 1; }
if (i != dragged_table) { new_draggable_tables[j] = draggable_tables[i]; j = j + 1; } }
draggable_tables = new_draggable_tables;
for (i = 0; i < <?php echo $max_tables; ?>; i++) {
document.getElementById('table'+i).value = draggable_tables[i]['key'];
document.getElementById('table'+i+'_displayed').checked = draggable_tables[i]['displayed']; } }
var content = "<p class=\"description\" style=\"color: #004080; font-size: 1.2em; font-weight: 600;\"><?php _e('You can change the order of the tables by Drag-and-Drop:', 'contact-manager'); ?></p>"+table_dropzone(0);
for (i = 0; i < <?php echo $max_tables; ?>; i++) {
content += '<div id="draggable-table'+i+'" style="background-color: #ffffff; border: 1px solid #c0c0c0; cursor: move; margin: 0 auto; text-align: center; width: '+table_width+'em; min-height: '+table_height+'em; max-width: 80%;" draggable="true" ondragenter="update_table_dropzones('+i+');" ondragstart="dragged_table = '+i+'; dragged_content = this.innerHTML; this.style.color = \'#808080\'; this.style.border = \'1px dashed #c0c0c0\';" ondragend="update_draggable_tables();">'
+'<div style="float: left; max-width: 70%; padding: '+table_margin+'em;"><span style="vertical-align: 25%;">'+draggable_tables[i]['name']+'</span></div>'
+'<div style="float: right; padding: '+table_margin+'em;"><label style="vertical-align: 25%;"><input type="checkbox" value="yes"'+(draggable_tables[i]['displayed'] ? ' checked="checked"' : '')+' onclick="this.checked = !draggable_tables['+i+'][\'displayed\']; document.getElementById(\'table'+i+'_displayed\').checked = draggable_tables['+i+'][\'displayed\'] = this.checked;" /> <?php _e('Display', 'contact-manager'); ?></label></div>'
+'<div style="clear: both;"></div></div>'+table_dropzone(i + 1); }
document.getElementById("draggable-tables").innerHTML = content; }

function table_dropzone(i) { return '<div id="table-dropzone'+i+'" style="margin: 0 auto; text-align: center; width: '+table_width+'em; height: '+table_margin+'em; max-width: 80%;" ondragenter="update_table_dropzones('+i+');" ondragend="update_draggable_tables();"></div>'; }

function update_table_dropzones(j) {
table_position = dragged_table;
for (i = 0; i <= <?php echo $max_tables; ?>; i++) {
var dropzone = document.getElementById('table-dropzone'+i);
if ((i == j) && (dragged_table != i) && ((dragged_table + 1) != i)) {
table_position = i;
dropzone.setAttribute('style', 'background-color: #ffffff; border: 1px dashed #c0c0c0; color: #404040; margin: '+table_margin+'em auto; text-align: center; width: '+table_width+'em; min-height: '+table_height+'em; max-width: 80%;');
dropzone.innerHTML = dragged_content; }
else { dropzone.setAttribute('style', 'margin: 0 auto; text-align: center; width: '+table_width+'em; height: '+table_margin+'em; max-width: 80%;'); dropzone.innerHTML = ''; } }
var table = document.getElementById('draggable-table'+dragged_table);
if (table_position == dragged_table) { table.style.backgroundColor = '#ffffff'; table.style.color = '#404040'; }
else { table.style.backgroundColor = ''; table.style.color = '#808080'; } } }
</script>
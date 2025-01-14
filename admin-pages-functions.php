<?php if (!defined('ABSPATH')) { exit(); }
load_contact_textdomain();
if ((!isset($GLOBALS['device_detected'])) || (!in_array($GLOBALS['device_detected'], array('computer', 'phone', 'tablet')))) {
$GLOBALS['device_detected'] = (function_exists('device_detected') ? device_detected() : (function_exists('wp_is_mobile') ? (wp_is_mobile() ? 'phone' : 'computer') : 'computer')); }
if (is_admin()) {
foreach ($_GET as $key => $value) { if (is_string($value)) { $_GET[$key] = kleor_quotes_entities($_GET[$key]); } }
if (isset($_GET['id'])) { $_GET['id'] = (int) $_GET['id']; if ($_GET['id'] < 1) { unset($_GET['id']); } }
foreach ($_GET as $key => $value) { if ((!isset($GLOBALS[$key])) && (is_scalar($value))) { $GLOBALS[$key] = $value; } } }


function contact_manager_pages_celia($context = '', $default = '') {
$celia = (array) (isset($GLOBALS['kleor_celia']) ? $GLOBALS['kleor_celia'] : array());
echo '<div id="celia'.($context == '' ? '' : '-'.$context).'"'.($context == 'submit' ? ' style="display: none;"' : '').'>'.(empty($celia[$context]) ? $default : $celia[$context]).'</div>'; }


function contact_manager_pages_css() {
$a = kleor_get_option('contact_manager_answers');
if (!$a) { $context = 'set'; $answers = 'none'; }
else {
$context = (string) (isset($_GET['action']) ? $_GET['action'] : (isset($_GET['id']) ? 'edit' : ''));
$answers = ''; $a = (array) $a;
foreach ($a as $key => $value) { if ($value) { $answers .= $key.','; } }
$answers = substr($answers, 0, -1); }
$lang = strtolower(substr(get_locale(), 0, 2)); if ($lang == '') { $lang = 'en'; }
$page = ($_GET['page'] == 'contact-manager' ? '' : str_replace('-', '_', substr($_GET['page'], 16)));
$back_office_options = (array) kleor_get_option('contact_manager_back_office');
$pages_viewed = (array) (((isset($_GET['guided-tour'])) && ($_GET['guided-tour'] == 'yes')) ? array() : $back_office_options['pages_viewed']);
if ((!in_array($page, $pages_viewed)) && (!in_array($context, array('delete', 'reset', 'uninstall'))) && (!isset($_POST['submit']))) {
if (!in_array($context, array('edit', 'set'))) {
$back_office_options['pages_viewed'] = array_merge($pages_viewed, array($page));
kleor_update_option('contact_manager_back_office', $back_office_options); }
$ip = $_SERVER['REMOTE_ADDR'];
@session_start(); $_SESSION['celia_'.$ip] = (array) (isset($_SESSION['celia_'.$ip]) ? $_SESSION['celia_'.$ip] : array()); session_write_close();
if ((defined('KLEOR_DEMO')) && (KLEOR_DEMO == true)) { $file = str_replace('/demo', '', ABSPATH).'celia.php'; }
else {
date_default_timezone_set('UTC');
$celia = (array) kleor_get_option('kleor_celia');
$celia['timestamp'] = (int) (isset($celia['timestamp']) ? $celia['timestamp'] : 0);
if ((time() - $celia['timestamp']) < 86400) { $_SESSION['celia_'.$ip] = $celia; } }
if ((isset($file)) && (file_exists($file))) { $plugin = 'Contact Manager'; $version = CONTACT_MANAGER_VERSION; $pages = implode(',', contact_manager_admin_menu_pages()); include $file; }
else {
$body = wp_remote_retrieve_body(wp_remote_get('https://www.kleor.com/celia.php?lang='.$lang.'&plugin=Contact%20Manager&version='.CONTACT_MANAGER_VERSION.'&page='.$page.'&context='.$context.'&submitted='.(isset($_POST['submit']) ? 'yes' : 'no').'&pages='.implode(',', contact_manager_admin_menu_pages()).'&pages_viewed='.($pages_viewed == array() ? 'none': implode(',', $pages_viewed)).'&answers='.$answers.'&device='.$GLOBALS['device_detected'].'&session='.urlencode(serialize($_SESSION['celia_'.$ip])),
array('timeout' => 2, 'user-agent' => $_SERVER['HTTP_USER_AGENT'])));
$GLOBALS['kleor_celia'] = (array) (is_serialized($body) ? unserialize($body) : array()); }
if (isset($GLOBALS['kleor_celia']['next_page_url'])) { $back_office_options['next_page_url'] = $GLOBALS['kleor_celia']['next_page_url']; kleor_update_option('contact_manager_back_office', $back_office_options); }
if (isset($GLOBALS['kleor_celia']['session'])) {
@session_start(); $_SESSION['celia_'.$ip] = (array) $GLOBALS['kleor_celia']['session']; session_write_close();
if ((!defined('KLEOR_DEMO')) || (KLEOR_DEMO == false)) { kleor_update_option('kleor_celia', (array) $GLOBALS['kleor_celia']['session']); } } }
$GLOBALS['kleor_celia'] = (array) (isset($GLOBALS['kleor_celia']) ? $GLOBALS['kleor_celia'] : array());
if ((empty($GLOBALS['kleor_celia'][''])) && (!empty($back_office_options['next_page_url']))) {
$GLOBALS['kleor_celia'][''] = '<div style="text-align: center;"><a class="celia-button" style="font-size: '.($GLOBALS['device_detected'] == 'phone' ? 3 : 1.5).'vw;" href="'.htmlspecialchars($back_office_options['next_page_url']).'"><svg style="fill: currentColor; vertical-align: middle; width: 1em; height: 1em;" viewBox="0 0 2 2"><polygon points="0,0 0,2 2,1"/></svg> '.__('Click here for the next step of the guided tour', 'contact-manager').'</a></div>'; }
$css = "/* Amaranth font designed by Gesine Todt */
@font-face { font-display: swap; font-family: 'Amaranth'; font-style: normal; font-weight: 400; src: url('https://fonts.gstatic.com/s/amaranth/v10/KtkuALODe433f0j1zMnFHdA.woff2'); }
html.wp-toolbar { padding-top: 0; }
#wpadminbar { height: 32px; position: absolute; }
#wpwrap { background-color: #ffffff; color: #000000; padding-top: 32px; }
.wpdberror { display: none; }
.wrap { margin-top: 0; }
.wrap h1 { float: left; font-family: 'Amaranth', Verdana, sans-serif; font-size: 2em; font-weight: 400; margin: 0.325em 3em 0 0; }
.wrap h2 { float: left; }
.wrap input, .wrap option, .wrap select, .wrap textarea, .wrap .postbox input, .wrap .postbox option, .wrap .postbox select, .wrap .postbox textarea { background-color: #ffffff; color: #000000; }
.wrap input.button-secondary { background-color: #ffffff; border-color: #a0a0a0; color: #000000; vertical-align: 0; }
.wrap input.button-tertiary { background-color: #ffffff; border: 1px solid #a0a0a0; border-radius: 0.25em; color: #000000; cursor: pointer; height: 1.75em; }
.wrap input.button-tertiary:hover { background-color: #f1f1f1; }
.wrap input.date-pick { margin-right: 0.5em; text-align: center; width: 11em; }
.wrap p.submit { margin: 0 auto; text-align: center; }
.wrap textarea { overflow: auto; resize: both; }
.wrap ul.subsubsub, .wrap .description { font-size: 1.2em; text-align: left; }
.wrap .alignleft.actions { display: block; }
.wrap .count, .wrap .description { color: #404040; }
.wrap .description { font-style: italic; }
.wrap .postbox input[disabled], .wrap .postbox textarea[disabled] { color: #606060; }
.wrap .delete, .wrap .delete a { color: #c00000; }
.wrap .delete:hover, .wrap .delete a:hover { color: #ff0000; }
.wrap .disabled { cursor: default; }
.wrap .dp-choose-date { vertical-align: 10%; }
.wrap .postbox { background-color: #e8f0f8; border: 1px solid #b8d0e8; color: #000000; }
.wrap .postbox a { color: #0040ff; outline: none; }
.wrap .postbox h3 { background-color: #e8f0f8; border-bottom: 1px solid #d0e0f0; color: #f0503c; font-family: 'Amaranth', Verdana, sans-serif; font-size: 2em; margin: 0; padding: 0.5em; }
.wrap .postbox h4 { color: #000000; font-family: Verdana, sans-serif; font-size: 1.2em; }
.wrap input.button-primary, .wrap .postbox .inside table.form-table tbody tr td input.button-secondary, .wrap .no-button, .wrap .yes-button { border: none; border-radius: 1.25em; color: #ffffff; cursor: pointer; font-family: 'Amaranth', Verdana, sans-serif; font-weight: bold; height: 2.5em; line-height: 2.5; margin: 0 0 1em 0; padding: 0 1em; }
.wrap input.button-primary:hover, .wrap input.button-primary.yellow:hover, .wrap .postbox .inside table.form-table tbody tr td input.button-secondary:hover { background-color: #00a000; }
.wrap input.button-primary { background-color: #0050a0; font-size: 1.8em; }
.wrap input.button-primary.yellow { background-color: #ffcc00; }
.wrap .no-button, .wrap .yes-button { display: inline-block; opacity: 0.8; width: 3em; }
.wrap .no-button:hover, .wrap .yes-button:hover { opacity: 1; }
.wrap .no-button { background-color: #ff0000; }
.wrap .yes-button { background-color: #00a000; }
.wrap .postbox .inside table.form-table tbody tr td input.button-secondary { background-color: #ffcc00; font-size: 1.2em; }
.wrap .postbox tr th strong { color: #004080; font-weight: 700; }
.wrap .postbox .description { color: #008000; font-size: 1em; font-weight: 600; vertical-align: -10%; }
.wrap .postbox .dp-choose-date { vertical-align: 0%; }
.wrap .postbox .inside table.form-table tbody tr td, .wrap .postbox .inside table.form-table tbody tr th { font-size: 1.1em; }
.wrap .postbox .inside table.form-table tbody tr td input, .wrap .postbox .inside table.form-table tbody tr td select, .wrap .postbox .inside table.form-table tbody tr td textarea { font-size: 1em; }
.wrap .postbox .inside table.form-table tbody tr td input[type=\"text\"], .wrap .postbox .inside table.form-table tbody tr td select { height: 2em; vertical-align: middle; }
.wrap .postbox .select-file, .wrap .postbox .select-image { font-weight: bold; }
.wrap .updated-notice { background-color: #ffffc0; border: 1px solid #f0e080; margin: 1em 1.5em 1em 0; padding: 0 0.5em; }
.wrap .updated-notice p { font-size: 1.2em; }
.wrap .wp-list-table td { color: #000000; }
.wrap .wp-list-table th, .wrap .postbox #email_address_indicator, .wrap .postbox #login_indicator, .wrap .postbox #password_indicator, .wrap .postbox #paypal_email_address_indicator { font-weight: 600; }
.wrap .wp-list-table.fixed th { font-weight: bold; }
.wrap .wp-list-table tr, .wrap .wp-list-table tr .main-row-actions, .wrap .wp-list-table tr .row-actions { background-color: #eef4fa; }
.wrap .wp-list-table tr.alternate, .wrap .wp-list-table tr.alternate .main-row-actions, .wrap .wp-list-table tr.alternate .row-actions, .wrap .wp-list-table tr.no-items { background-color: #ffffff; }
.wrap .wp-list-table tr .main-row-actions { margin-top: 3em; ".($GLOBALS['device_detected'] == "phone" ? "width: 20em;" : "display: none; position: absolute;")." }
.wrap .wp-list-table tr:hover .main-row-actions { display: block; }
.wrap .wp-list-table .description { font-size: 1em; }
*:-ms-input-placeholder { color: #a0a0a0; }";
$fonts = array('https://fonts.gstatic.com/s/amaranth/v10/KtkuALODe433f0j1zMnFHdA');
$celia = false; foreach ($GLOBALS['kleor_celia'] as $value) { if ((is_string($value)) && (strstr($value, 'class="celia'))) { $celia = true; } }
if ($celia) { $fonts[] = 'https://fonts.gstatic.com/s/courgette/v7/wEO_EBrAnc9BLjLQAUk1VvoK'; $css .= file_get_contents(contact_path('libraries/celia.css')); }
$css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css);
$css = preg_replace('/\s{2,}/', ' ', $css);
$css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
$css = preg_replace('/;}/', '}', $css);
$css = str_replace(array("\r", "\n", "\t"), " ", $css);
while (strstr($css, "  ")) { $css = str_replace("  ", " ", $css); }
$css = str_replace(', ', ',', $css);
$css = trim($css);
foreach ($fonts as $font) { echo '<link rel="preload" as="font" href="'.$font.'.woff2" type="font/woff2" crossorigin="anonymous">'; }
echo '<style media="all">'.$css.'</style>'; }

add_action('admin_head', 'contact_manager_pages_css');


function contact_manager_pages_js() {
kleor_format_url_js(); ?>
<script>
function adapt_to_device() {
device = "<?php echo $GLOBALS['device_detected']; ?>";
if (device == "phone") {
var elements = document.querySelectorAll("tr, th, td, textarea");
elements.forEach((entry) => { entry.style.width = "100%"; });
var elements = document.querySelectorAll("td[class=answer]");
elements.forEach((entry) => { entry.style.display = "inline"; entry.style.fontSize = "4vw"; entry.style.margin = "1em 2em 1em 0"; entry.style.padding = "1em"; });
var elements = document.querySelectorAll("input[type=email], input[type=password], input[type=search], input[type=text], input[type=url]");
elements.forEach((entry) => {
if (entry.getAttribute("class") == "date-pick") { entry.style.display = "inline"; entry.style.width = "11em"; }
else { var size = parseInt(entry.getAttribute("size"), 10); if ((size == 0) || (size > 16)) { entry.style.width = "100%"; } } });
var elements = document.querySelectorAll("label[for=start_date], label[for=end_date]");
elements.forEach((entry) => { entry.style.display = "block"; entry.style.margin = 0; entry.style.width = "100%"; }); } }
adapt_to_device();

inputs = document.getElementsByTagName("input");
for (i = 0, n = inputs.length; i < n; i++) {
if (inputs[i].type == "text") {
attributes = ["onblur","onfocus","onkeydown","onkeyup"]; values = [];
values["onblur"] = "if (this.value.length > "+(2*inputs[i].size + 2)+") { this.size = "+(2*inputs[i].size + 1)+"; } else if (this.value.length > "+inputs[i].size+") { this.size = this.value.length - 1; } else { this.size = "+inputs[i].size+"; }";
values["onfocus"] = values["onblur"]; values["onkeydown"] = values["onblur"]; values["onkeyup"] = values["onblur"];
if (inputs[i].value.length > 2*inputs[i].size + 2) { inputs[i].size = 2*inputs[i].size + 1; } else if (inputs[i].value.length > inputs[i].size) { inputs[i].size = inputs[i].value.length - 1; }
for (j = 0; j < 4; j++) {
if (inputs[i].hasAttribute(attributes[j])) { string = inputs[i].getAttribute(attributes[j])+" "; } else { string = ""; }
inputs[i].setAttribute(attributes[j], string+values[attributes[j]]); } } }

textareas = document.getElementsByTagName("textarea");
for (i = 0, n = textareas.length; i < n; i++) {
if (textareas[i].rows == 1) {
attributes = ["onblur","onfocus","onkeydown","onkeyup"]; values = [];
m = 1.2*textareas[i].cols;
values["onblur"] = "this.style.height = '1.75em';";
values["onfocus"] = "lines = this.value.split(\"\\n\"); this.style.height = (1.75*Math.min(5, lines.length + Math.floor(this.value.length/"+m+")))+'em';";
values["onkeydown"] = values["onfocus"]; values["onkeyup"] = values["onfocus"];
for (j = 0; j < 4; j++) {
if (textareas[i].hasAttribute(attributes[j])) { string = textareas[i].getAttribute(attributes[j])+" "; } else { string = ""; }
textareas[i].setAttribute(attributes[j], string+values[attributes[j]]); } } }

descriptions = document.querySelectorAll(".description");
descriptions.forEach((entry) => { entry.innerHTML = entry.innerHTML.replace('<?php _e('insert shortcodes', 'contact-manager'); ?>', '<?php _e('insert cotillions', 'contact-manager'); ?>'); });

if (window.location.hash != '') { setTimeout('window.location = window.location.hash;', 250); }

function kleor_format_number(string) {
string = string.replace(/[à]/g, '0');
string = string.replace(/[&]/g, '1');
string = string.replace(/[é]/g, '2');
string = string.replace(/["]/g, '3');
string = string.replace(/[']/g, '4');
string = string.replace(/[(]/g, '5');
string = string.replace(/[-]/g, '6');
string = string.replace(/[è]/g, '7');
string = string.replace(/[_]/g, '8');
string = string.replace(/[ç]/g, '9');
string = string.replace(/[?,;]/g, '.');
string = string.replace(/[^0-9.]/g, '');
return string; }

function kleor_format_integer(string) {
string = string.replace(/[à]/g, '0');
string = string.replace(/[&]/g, '1');
string = string.replace(/[é]/g, '2');
string = string.replace(/["]/g, '3');
string = string.replace(/[']/g, '4');
string = string.replace(/[(]/g, '5');
string = string.replace(/[-]/g, '6');
string = string.replace(/[è]/g, '7');
string = string.replace(/[_]/g, '8');
string = string.replace(/[ç]/g, '9');
string = string.replace(/[^0-9]/g, '');
return string; }
</script>
<?php }

add_action('admin_footer', 'contact_manager_pages_js');


function contact_manager_pages_links($back_office_options) {
$links = (array) $back_office_options['links'];
$displayed_links = (array) $back_office_options['displayed_links'];
if (($back_office_options['links_displayed'] == 'yes') && (count($displayed_links) > 0)) {
include contact_path('admin-pages.php');
echo '<ul class="subsubsub" style="margin: 1.25em 0 1.5em 0; float: left; white-space: normal;">';
$links_markup = array(
'Documentation' => '<a target="'.$back_office_options['documentations_links_target'].'" href="https://www.kleor.com/contact-manager/">'.$admin_links['Documentation']['name'].'</a>',
'Commerce Manager' => (((function_exists('commerce_data')) && (current_user_can('view_commerce_manager'))) ? '<a href="admin.php?page=commerce-manager'.
(kleor_get_option('commerce_manager_answers') ?
($_GET['page'] == 'contact-manager-form' ? '-product' : '').
($_GET['page'] == 'contact-manager-form-category' ? '-product-category' : '').
($_GET['page'] == 'contact-manager-forms' ? '-products' : '').
($_GET['page'] == 'contact-manager-forms-categories' ? '-products-categories' : '').
($_GET['page'] == 'contact-manager-message' ? '-order' : '').
($_GET['page'] == 'contact-manager-messages' ? '-orders' : '').
(strstr($_GET['page'], 'back-office') ? '-back-office' : '').
(strstr($_GET['page'], 'statistics') ? '-statistics' : '') : '').'">'.$admin_links['Commerce Manager']['name'].'</a>' : '<a target="'.$back_office_options['documentations_links_target'].'" href="https://www.kleor.com/commerce-manager/">'.$admin_links['Commerce Manager']['name'].'</a>'),
'Affiliation Manager' => (((function_exists('affiliation_data')) && (current_user_can('view_affiliation_manager'))) ? '<a href="admin.php?page=affiliation-manager'.
(kleor_get_option('affiliation_manager_answers') ?
($_GET['page'] == 'contact-manager-message' ? '-affiliate' : '').
(strstr($_GET['page'], 'back-office') ? '-back-office' : '').
(strstr($_GET['page'], 'messages') ? '-messages-commissions' : '').
(strstr($_GET['page'], 'statistics') ? '-statistics' : '') : '').'">'.$admin_links['Affiliation Manager']['name'].'</a>' : '<a target="'.$back_office_options['documentations_links_target'].'" href="https://www.kleor.com/affiliation-manager/">'.$admin_links['Affiliation Manager']['name'].'</a>'),
'Membership Manager' => (((function_exists('membership_data')) && (current_user_can('view_membership_manager'))) ? '<a href="admin.php?page=membership-manager'.
(kleor_get_option('membership_manager_answers') ?
($_GET['page'] == 'contact-manager-form' ? '-member-area' : '').
($_GET['page'] == 'contact-manager-form-category' ? '-member-area-category' : '').
($_GET['page'] == 'contact-manager-forms' ? '-members-areas' : '').
($_GET['page'] == 'contact-manager-forms-categories' ? '-members-areas-categories' : '').
($_GET['page'] == 'contact-manager-message' ? '-member' : '').
($_GET['page'] == 'contact-manager-messages' ? '-members' : '').
(strstr($_GET['page'], 'back-office') ? '-back-office' : '').
(strstr($_GET['page'], 'statistics') ? '-statistics' : '') : '').'">'.$admin_links['Membership Manager']['name'].'</a>' : '<a target="'.$back_office_options['documentations_links_target'].'" href="https://www.kleor.com/membership-manager/">'.$admin_links['Membership Manager']['name'].'</a>'),
'Optin Manager' => (((function_exists('optin_data')) && (current_user_can('view_optin_manager'))) ? '<a href="admin.php?page=optin-manager'.
(kleor_get_option('optin_manager_answers') ?
($_GET['page'] == 'contact-manager-form' ? '-form' : '').
($_GET['page'] == 'contact-manager-form-category' ? '-form-category' : '').
($_GET['page'] == 'contact-manager-forms' ? '-forms' : '').
($_GET['page'] == 'contact-manager-forms-categories' ? '-forms-categories' : '').
($_GET['page'] == 'contact-manager-message' ? '-prospect' : '').
($_GET['page'] == 'contact-manager-messages' ? '-prospects' : '').
(strstr($_GET['page'], 'back-office') ? '-back-office' : '').
(strstr($_GET['page'], 'statistics') ? '-statistics' : '') : '').'">'.$admin_links['Optin Manager']['name'].'</a>' : '<a target="'.$back_office_options['documentations_links_target'].'" href="https://www.kleor.com/optin-manager/">'.$admin_links['Optin Manager']['name'].'</a>'));
if (kleor_get_option('contact_manager_answers')) { $links_markup['Guided tour'] = '<a href="admin.php?page=contact-manager-back-office&amp;guided-tour=yes">'.$admin_links['Guided tour']['name'].'</a>'; }
$first = true; $links_displayed = array();
$n = count($admin_links); for ($i = 0; $i < $n; $i++) {
$link = (isset($links[$i]) ? $links[$i] : '');
if ((in_array($i, $displayed_links)) && (isset($links_markup[$link])) && (!in_array($link, $links_displayed))) {
echo '<li>'.($first ? '' : '&nbsp;| ').$links_markup[$link].'</li>'; $first = false; $links_displayed[] = $link; } }
echo '</ul>'; } }


function contact_manager_pages_links_markups($back_office_options) {
foreach (array('default_options', 'documentations', 'ids_fields', 'pages_modules', 'urls_fields') as $string) {
$markups[$string.'_links_markup'] = 'target="'.$back_office_options[$string.'_links_target'].'"'; }
return $markups; }


function contact_manager_pages_menu($back_office_options) {
$menu_items = (array) $back_office_options['menu_items'];
$menu_displayed_items = (array) $back_office_options['menu_displayed_items'];
if (($back_office_options['menu_displayed'] == 'yes') && (count($menu_displayed_items) > 0)) {
include contact_path('admin-pages.php');
echo '<ul class="subsubsub" style="margin: 0 0 1em; white-space: normal;">';
$first = true; $items_displayed = array();
$n = count($admin_pages); for ($i = 0; $i < $n; $i++) {
$item = (isset($menu_items[$i]) ? $menu_items[$i] : '');
if ((isset($admin_pages[$item])) && (in_array($i, $menu_displayed_items)) && (!in_array($item, $items_displayed))) {
$slug = 'contact-manager'.($item == '' ? '' : '-'.str_replace('_', '-', $item));
echo '<li>'.($first ? '' : '&nbsp;| ').'<a href="admin.php?page='.$slug.'"'.($_GET['page'] == $slug ? ' class="current"' : '').'>'.$admin_pages[$item]['menu_title'].'</a></li>';
$first = false; $items_displayed[] = $item; } }
echo '</ul>
<div class="clear"></div>'; }
contact_manager_pages_celia(); }


function contact_manager_pages_module($back_office_options, $module, $undisplayed_modules) {
include contact_path('admin-pages.php');
$page_slug = str_replace('-', '_', str_replace('-page', '', $module));
$page_undisplayed_modules = (array) $back_office_options[$page_slug.'_page_undisplayed_modules']; ?>
<div class="postbox" id="<?php echo $module.'-module'; ?>"<?php if (in_array($module, $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="<?php echo $module; ?>"><strong><?php echo $modules['back_office'][$module]['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<?php if ((strstr($_GET['page'], 'back-office')) && ($page_slug != 'back_office')) { echo '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a target="'.$back_office_options['pages_modules_links_target'].'" href="admin.php?page=contact-manager'.($page_slug == 'options' ? '' : '-'.str_replace('_', '-', $page_slug)).'">'.__('Click here to open this page.', 'contact-manager').'</a></span></td></tr>'; } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="<?php echo $page_slug; ?>_page_summary_displayed" id="<?php echo $page_slug; ?>_page_summary_displayed" value="yes"<?php if ($back_office_options[$page_slug.'_page_summary_displayed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Display the summary', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Modules displayed', 'contact-manager'); ?></strong></th>
<td><?php foreach ($modules[$page_slug] as $key => $value) {
$name = $page_slug.'_page_'.str_replace('-', '_', $key).'_module_displayed';
if ((!isset($value['title'])) || ($value['title'] == '')) {
if ((isset($value['required'])) && ($value['required'] == 'yes')) { $title = ' title="'.__('You can\'t disable the display of this module.', 'contact-manager').'"'; }
elseif (in_array($key, array('affiliation', 'registration-to-affiliate-program'))) { $title = ' title="'.__('Useful only if you use Affiliation Manager', 'contact-manager').'"'; }
elseif ($key == 'registration-as-a-client') { $title = ' title="'.__('Useful only if you use Commerce Manager', 'contact-manager').'"'; }
elseif ($key == 'membership') { $title = ' title="'.__('Useful only if you use Membership Manager', 'contact-manager').'"'; }
elseif ($key == 'wordpress') { $title = ' title="'.__('Allows you to register the sender as a WordPress user', 'contact-manager').'"'; }
elseif ($key == 'custom-instructions') { $title = ' title="'.__('Allows you to execute additional PHP instructions', 'contact-manager').'"'; }
else { $title = ''; } }
else { $title = ' title="'.$value['title'].'"'; }
$link = '<span class="description" style="vertical-align: middle;">'.(((strstr($_GET['page'], 'back-office')) && ($page_slug != 'back_office')) ? '<a target="'.$back_office_options['pages_modules_links_target'].'" href="admin.php?page=contact-manager'.($page_slug == 'options' ? '' : '-'.str_replace('_', '-', $page_slug)) : '<a onclick="document.getElementById(\''.$key.'-module\').style.display = \'block\'; window.location = \'#'.$key.'\';" href="').'#'.$key.'">'.__('Link', 'contact-manager').'</a></span>';
if ((isset($value['required'])) && ($value['required'] == 'yes')) { echo '<label'.$title.'><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes" checked="checked" disabled="disabled" /> '.$value['name'].'</label> '.$link.'<br />'; }
else { echo '<span'.(((!isset($_GET['id'])) || ($page_slug != 'message') || (!in_array($key, $add_message_modules))) ? '' : ' style="display: none;"').'><label'.$title.'><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes"'.(in_array($key, $page_undisplayed_modules) ? '' : ' checked="checked"').' /> '.$value['name'].'</label> '.$link.'<br /></span>'; }
if (isset($value['modules'])) { foreach ($value['modules'] as $module_key => $module_value) {
$module_name = $page_slug.'_page_'.str_replace('-', '_', $module_key).'_module_displayed';
if ((!isset($module_value['title'])) || ($module_value['title'] == '')) {
if ((isset($module_value['required'])) && ($module_value['required'] == 'yes')) { $module_title = ' title="'.__('You can\'t disable the display of this module.', 'contact-manager').'"'; }
elseif ($key == 'affiliation') { $module_title = ' title="'.__('Useful only if you use Affiliation Manager', 'contact-manager').'"'; }
elseif ($key == 'custom-instructions') { $module_title = ' title="'.__('Allows you to execute additional PHP instructions', 'contact-manager').'"'; }
else { $module_title = ''; } }
else { $module_title = ' title="'.$module_value['title'].'"'; }
$link = '<span class="description" style="vertical-align: middle;">'.(((strstr($_GET['page'], 'back-office')) && ($page_slug != 'back_office')) ? '<a target="'.$back_office_options['pages_modules_links_target'].'" href="admin.php?page=contact-manager'.($page_slug == 'options' ? '' : '-'.str_replace('_', '-', $page_slug)) : '<a onclick="document.getElementById(\''.$key.'-module\').style.display = \'block\'; document.getElementById(\''.$module_key.'-module\').style.display = \'block\'; window.location = \'#'.$module_key.'\';" href="').'#'.$module_key.'">'.__('Link', 'contact-manager').'</a></span>';
if ((isset($module_value['required'])) && ($module_value['required'] == 'yes')) { echo '<label'.$module_title.'><input style="margin-left: 2em;" type="checkbox" name="'.$module_name.'" id="'.$module_name.'" value="yes" checked="checked" disabled="disabled" /> '.$module_value['name'].'</label> '.$link.'<br />'; }
else { echo '<label'.$module_title.'><input style="margin-left: 2em;" type="checkbox" name="'.$module_name.'" id="'.$module_name.'" value="yes"'.(in_array($module_key, $page_undisplayed_modules) ? '' : ' checked="checked"').' /> '.$module_value['name'].'</label> '.$link.'<br />'; } } } } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="hidden" name="submit" value="true" />
<input type="submit" class="button-secondary" name="update_back_office_options" value="<?php _e('Update', 'contact-manager'); ?>" onclick="this.setAttribute('data-clicked', 'yes');" /></td></tr>
</tbody></table>
<?php if ((strstr($_GET['page'], 'back-office')) && (isset($modules['back_office'][$module]['modules'][$module.'-custom-fields']))) {
foreach (array('kleor_strip_accents_js', 'kleor_format_nice_name_js') as $function) { add_action('admin_footer', $function); } ?>
<div id="<?php echo $module; ?>-custom-fields-module"<?php if (in_array($module.'-custom-fields', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="<?php echo $module; ?>-custom-fields"><strong><?php echo $modules['back_office'][$module]['modules'][$module.'-custom-fields']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You can create an unlimited number of custom fields to record additional data.', 'contact-manager'); ?> <a target="<?php echo $back_office_options['documentations_links_target']; ?>" href="https://www.kleor.com/contact-manager/#custom-fields"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
</tbody></table>
<table class="form-table" style="margin-left: 7%; width: 90%;"><tbody>
<?php $custom_fields = (array) $back_office_options[$page_slug.'_page_custom_fields'];
asort($custom_fields); $i = 0; foreach ($custom_fields as $key => $value) {
$i = $i + 1; echo '<tr style="vertical-align: top;"><th scope="row" style="width: 4%;"><strong><label for="'.$page_slug.'_page_custom_field_name'.$i.'">'.__('Name', 'contact-manager').'</label></strong></th>
<td style="width: 40%;"><textarea style="vertical-align: 200%; padding: 0 0.25em; height: 1.75em; width: 90%;" name="'.$page_slug.'_page_custom_field_name'.$i.'" id="'.$page_slug.'_page_custom_field_name'.$i.'" rows="1" cols="50">'.htmlspecialchars($value).'</textarea></td>
<th scope="row" style="width: 4%;"><strong><label for="'.$page_slug.'_page_custom_field_key'.$i.'">'.__('Key', 'contact-manager').'</label></strong></th>
<td style="width: 40%;"><textarea style="padding: 0 0.25em; height: 1.75em; width: 67.5%;" name="'.$page_slug.'_page_custom_field_key'.$i.'" id="'.$page_slug.'_page_custom_field_key'.$i.'" rows="1" cols="50" disabled="disabled">'.str_replace('_', '-', $key).'</textarea>
<input type="hidden" name="'.$page_slug.'_page_custom_field_key'.$i.'" value="'.str_replace('_', '-', $key).'" /><input type="hidden" name="submit" value="true" /><input style="vertical-align: top;" type="submit" class="button-tertiary" name="delete_'.$page_slug.'_page_custom_field'.$i.'" value="'.__('Delete', 'contact-manager').'" formaction="'.esc_attr($_SERVER['REQUEST_URI']).'#'.$module.'-custom-fields-module" /><br />
<span class="description">'.__('The key can not be changed.', 'contact-manager').'</span></td></tr>'; }
$n = $i + 5; while ($i < $n) {
$i = $i + 1; echo '<tr style="vertical-align: top;"><th scope="row" style="width: 4%;"><strong><label for="'.$page_slug.'_page_custom_field_name'.$i.'">'.__('Name', 'contact-manager').'</label></strong></th>
<td style="width: 40%;"><textarea style="vertical-align: 200%; padding: 0 0.25em; height: 1.75em; width: 90%;" name="'.$page_slug.'_page_custom_field_name'.$i.'" id="'.$page_slug.'_page_custom_field_name'.$i.'" rows="1" cols="50" onkeydown="this.form.'.$page_slug.'_page_custom_field_key'.$i.'.placeholder = kleor_format_nice_name(this.value.replace(/[_]/g, \'-\'));" onkeyup="this.form.'.$page_slug.'_page_custom_field_key'.$i.'.placeholder = kleor_format_nice_name(this.value.replace(/[_]/g, \'-\'));" onchange="this.form.'.$page_slug.'_page_custom_field_key'.$i.'.placeholder = kleor_format_nice_name(this.value.replace(/[_]/g, \'-\'));"></textarea></td>
<th scope="row" style="width: 4%;"><strong><label for="'.$page_slug.'_page_custom_field_key'.$i.'">'.__('Key', 'contact-manager').'</label></strong></th>
<td style="width: 40%;"><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="'.$page_slug.'_page_custom_field_key'.$i.'" id="'.$page_slug.'_page_custom_field_key'.$i.'" rows="1" cols="50" onkeydown="this.value = kleor_format_nice_name(this.value.replace(/[_]/g, \'-\'));" onkeyup="this.value = kleor_format_nice_name(this.value.replace(/[_]/g, \'-\'));" onchange="this.value = kleor_format_nice_name(this.value.replace(/[_]/g, \'-\'));"></textarea>
<input type="hidden" name="submit" value="true" /><input style="vertical-align: top;" type="submit" class="button-tertiary" name="add_'.$page_slug.'_page_custom_field" value="'.__('Add', 'contact-manager').'" formaction="'.esc_attr($_SERVER['REQUEST_URI']).'#'.$module.'-custom-fields-module" /><br />
<span class="description">'.__('Lowercase letters, numbers and hyphens only', 'contact-manager').'</span></td></tr>'; } ?>
</tbody></table>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div><?php } ?>
</div></div>
<?php }


function contact_manager_pages_module_description($back_office_options, $module) {
extract(contact_manager_pages_links_markups($back_office_options));
$is_category = (strstr($_GET['page'], 'category'));
if (empty($_POST['category_id'])) { $_POST['category_id'] = 0; }
$content = ''; switch ($module) {
case 'affiliation': case 'membership': case 'registration-as-a-client': case 'registration-to-affiliate-program':
switch ($module) {
case 'affiliation': case 'registration-to-affiliate-program': $function = 'affiliation_data'; $string = __('To use affiliation, you must have installed and activated <a href="https://www.kleor.com/affiliation-manager/">Affiliation Manager</a>.', 'contact-manager'); break;
case 'membership': $function = 'membership_data'; $string = __('To use membership, you must have installed and activated <a href="https://www.kleor.com/membership-manager/">Membership Manager</a>.', 'contact-manager'); break;
case 'registration-as-a-client': $function = 'commerce_data'; $string = __('To register the senders as clients, you must have installed and activated <a href="https://www.kleor.com/commerce-manager/">Commerce Manager</a>.', 'contact-manager'); }
$content = '<th scope="row" style="width: 20%;"></th>
<td><span class="description">'.(function_exists($function) ? '<a '.$default_options_links_markup.' href="admin.php?page=contact-manager'.($_POST['category_id'] == 0 ? '#'.$module : '-form-category&amp;id='.$_POST['category_id'].'#'.$module).'">
'.($_POST['category_id'] == 0 ? __('Click here to configure the default options.', 'contact-manager') : ($is_category ? __('Click here to configure the default options of the parent category.', 'contact-manager') : __('Click here to configure the default options of the category.', 'contact-manager'))).'</a>'
 : str_replace('<a', '<a '.$documentations_links_markup, $string)).'</span></td>'; break;
case 'autoresponders': case 'custom-instructions': case 'error-messages': case 'form': case 'message-confirmation-email':
case 'message-notification-email': case 'messages-registration': case 'wordpress': $content = '<th scope="row" style="width: 20%;"></th>
<td><span class="description"><a '.$default_options_links_markup.' href="admin.php?page=contact-manager'.($_POST['category_id'] == 0 ? '#'.($module == 'form' ? 'forms' : $module) : '-form-category&amp;id='.$_POST['category_id'].'#'.$module).'">
'.($_POST['category_id'] == 0 ? __('Click here to configure the default options.', 'contact-manager') : ($is_category ? __('Click here to configure the default options of the parent category.', 'contact-manager') : __('Click here to configure the default options of the category.', 'contact-manager'))).'</a>
'.($module == 'error-messages' ? '<br /><a '.$documentations_links_markup.' href="https://www.kleor.com/contact-manager/#error">'.__('How to display an error message?', 'contact-manager').'</a>' : '').'</span></td>'; break;
case 'general-informations': if (($is_category) || ($_POST['category_id'] > 0)) { $content = '<th scope="row" style="width: 20%;"></th>
<td><span class="description">'.($is_category ? __('The options of this category will apply by default to the forms you assign to this category.', 'contact-manager').($_POST['category_id'] > 0 ? '<br />' : '') : '').'
'.($_POST['category_id'] > 0 ? '<a '.$default_options_links_markup.' href="admin.php?page=contact-manager-form-category&amp;id='.$_POST['category_id'].'">
'.($is_category ? __('Click here to configure the default options of the parent category.', 'contact-manager') : __('Click here to configure the default options of the category.', 'contact-manager')).'</a>' : '').'</span></td>'; } break;
case 'gift': if ($_POST['category_id'] > 0) { $content = '<th scope="row" style="width: 20%;"></th>
<td><span class="description"><a '.$default_options_links_markup.' href="admin.php?page=contact-manager-form-category&amp;id='.$_POST['category_id'].'#'.$module.'">
'.($_POST['category_id'] == 0 ? __('Click here to configure the default options.', 'contact-manager') : ($is_category ? __('Click here to configure the default options of the parent category.', 'contact-manager') : __('Click here to configure the default options of the category.', 'contact-manager'))).'</a></span></td>'; } }
if ($content == '') { $content = '<th style="height: 0; margin: 0; padding: 0;"></th><td style="height: 0; margin: 0; padding: 0;"></td>'; }
return $content; }


function contact_manager_pages_field_description($field, $value) {
$content = ''; $value = (string) $value; if ($value != '') { switch ($field) {
case 'automatic_display_form_id': case 'form_id': $value = (int) preg_replace('/[^0-9]/', '', $value);
$content = ($value == 0 ? __('No form', 'contact-manager') : htmlspecialchars(contact_excerpt(contact_form_data(array(0 => 'name', 'id' => $value)), 50)));
$result = ((isset($GLOBALS['contact_form'.$value.'_data'])) && (isset($GLOBALS['contact_form'.$value.'_data']['id'])));
if ((!$result) && ($value > 0)) { $content = __('Inexistent or deleted form', 'contact-manager'); } break;
case 'sender_members_areas': if ((function_exists('member_area_data')) && (is_numeric($value))) {
$value = (int) preg_replace('/[^0-9]/', '', $value);
$content = ($value == 0 ? __('All members areas', 'contact-manager') : htmlspecialchars(contact_excerpt(member_area_data(array(0 => 'name', 'id' => $value)), 50)));
$result = ((isset($GLOBALS['member_area'.$value.'_data'])) && (isset($GLOBALS['member_area'.$value.'_data']['id'])));
if ((!$result) && ($value > 0)) { $content = __('Inexistent or deleted member area', 'contact-manager'); } } }
if ($content != '') { $content = '('.$content.')'; } }
return $content; }


function contact_manager_pages_field_links($back_office_options, $field, $value) {
global $wpdb;
extract(contact_manager_pages_links_markups($back_office_options));
$is_category = (strstr($_GET['page'], 'category'));
$content = ''; $value = (string) $value; if ($value != '') { switch ($field) {
case 'automatic_display_form_id': case 'form_id': $value = (int) preg_replace('/[^0-9]/', '', $value); if ($value > 0) {
if (contact_form_data(array(0 => 'id', 'id' => $value)) == $value) {
$content = '<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-form&amp;id='.$value.'">'.__('Edit', 'contact-manager').'</a>
 | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-form&amp;id='.$value.'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a> | '; }
$content .= '<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-statistics&amp;form_id='.$value.'">'.__('Statistics', 'contact-manager').'</a>';
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE form_id = ".$value, OBJECT);
$messages_number = (int) (isset($row->total) ? $row->total : 0);
if ($messages_number > 0) { $content .= ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-messages&amp;form_id='.$value.'&amp;start_date=0">'.__('Messages', 'contact-manager').' <span class="count">('.$messages_number.')</span></a>'; } } break;
case 'category_id': case 'sender_affiliate_category_id': case 'sender_client_category_id': case 'sender_member_category_id':
$value = (int) preg_replace('/[^0-9]/', '', $value);
switch ($field) {
case 'category_id': $plugin = 'contact'; $type = 'form'; $string = __('Forms', 'contact-manager'); break;
case 'sender_affiliate_category_id': $plugin = 'affiliation'; $type = 'affiliate'; $string = __('Affiliates', 'contact-manager'); break;
case 'sender_client_category_id': $plugin = 'commerce'; $type = 'client'; $string = __('Clients', 'contact-manager'); break;
case 'sender_member_category_id': $plugin = 'membership'; $type = 'member'; $string = __('Members', 'contact-manager'); }
if (($value > 0) && (function_exists($plugin.'_data')) && (current_user_can('view_'.$plugin.'_manager'))) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix.$plugin."_manager_".$type."s WHERE category_id = ".$value, OBJECT);
$items_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix.$plugin."_manager_".$type."s_categories WHERE category_id = ".$value, OBJECT);
$categories_number = (int) (isset($row->total) ? $row->total : 0);
$content = '<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page='.$plugin.'-manager-'.$type.'-category&amp;id='.$value.'">'.__('Edit', 'contact-manager').'</a>
 | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page='.$plugin.'-manager-'.$type.'-category&amp;id='.$value.'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a>'
.($items_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page='.$plugin.'-manager-'.$type.'s&amp;category_id='.$value.'&amp;start_date=0">'.$string.' <span class="count">('.$items_number.')</span></a>')
.($categories_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page='.$plugin.'-manager-'.$type.'s-categories&amp;category_id='.$value.'&amp;start_date=0">'.__('Subcategories', 'contact-manager').' <span class="count">('.$categories_number.')</span></a>'); } break;
case 'id': $value = (int) preg_replace('/[^0-9]/', '', $value);
$content = '<a style="text-decoration: none;" href="admin.php?page='.$_GET['page'].'&amp;id='.$value.'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a>';
if (strstr($_GET['page'], 'form')) {
if ($is_category) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms WHERE category_id = ".$value, OBJECT);
$forms_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE category_id = ".$value, OBJECT);
$categories_number = (int) (isset($row->total) ? $row->total : 0);
$content .= ($forms_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-forms&amp;category_id='.$value.'&amp;start_date=0">'.__('Forms', 'contact-manager').' <span class="count">('.$forms_number.')</span></a>')
.($categories_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-forms-categories&amp;category_id='.$value.'&amp;start_date=0">'.__('Subcategories', 'contact-manager').' <span class="count">('.$categories_number.')</span></a>'); }
else {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE form_id = ".$value, OBJECT);
$messages_number = (int) (isset($row->total) ? $row->total : 0);
$content .= ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-statistics&amp;form_id='.$value.'">'.__('Statistics', 'contact-manager').'</a>'
.($messages_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-messages&amp;form_id='.$value.'&amp;start_date=0">'.__('Messages', 'contact-manager').' <span class="count">('.$messages_number.')</span></a>'); } } break;
case 'referrer': case 'referrer2': if ((function_exists('affiliation_data')) && (current_user_can('view_affiliation_manager'))) {
$value = kleor_format_email_address($value); if ($value != '') {
if (strstr($value, '@')) { $result = false; }
else { $result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$value."'", OBJECT); }
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."affiliation_manager_clicks WHERE referrer = '".$value."'", OBJECT);
$clicks_number = (int) (isset($row->total) ? $row->total : 0);
if ((!function_exists('commerce_data')) || (!current_user_can('view_commerce_manager'))) { $orders_number = 0; $recurring_payments_number = 0; }
else {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."commerce_manager_orders WHERE referrer = '".$value."'", OBJECT);
$orders_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."commerce_manager_recurring_payments WHERE referrer = '".$value."'", OBJECT);
$recurring_payments_number = (int) (isset($row->total) ? $row->total : 0); }
$content = ($result ? '<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-affiliate&amp;id='.$result->id.'">'.__('Edit', 'contact-manager').'</a>
 | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-affiliate&amp;id='.$result->id.'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a> | ' : '')
.'<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-statistics&amp;referrer='.$value.'">'.__('Statistics', 'contact-manager').'</a>'
.($clicks_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-clicks&amp;referrer='.$value.'&amp;start_date=0">'.__('Clicks', 'contact-manager').' <span class="count">('.$clicks_number.')</span></a>')
.($orders_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=commerce-manager-orders&amp;referrer='.$value.'&amp;start_date=0">'.__('Orders', 'contact-manager').' <span class="count">('.$orders_number.')</span></a>')
.($recurring_payments_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=commerce-manager-recurring-payments&amp;referrer='.$value.'&amp;start_date=0">'.__('Recurring payments', 'contact-manager').' <span class="count">('.$recurring_payments_number.')</span></a>'); } } break;
case 'sender_members_areas': if ((function_exists('membership_data')) && (current_user_can('view_membership_manager')) && (is_numeric($value))) {
$value = (int) preg_replace('/[^0-9]/', '', $value); if (($value > 0) && (member_area_data(array(0 => 'id', 'id' => $value)) == $value)) {
$content = '<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=membership-manager-member-area&amp;id='.$value.'">'.__('Edit', 'contact-manager').'</a> | 
<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=membership-manager-member-area&amp;id='.$value.'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a>'; } } }
if ($content != '') { $content = '<br />'.$content; } }
return $content; }


function contact_manager_pages_search_field($type, $searchby, $searchby_options) { ?>
<p class="search-box" style="float: right; margin-left: 1em;"><label><?php _e(ucfirst($type).' by', 'contact-manager'); ?> <select style="max-width: 15em;" name="<?php echo $type; ?>by" id="<?php echo $type; ?>by">
<?php if ($type == 'search') { echo '<option value=""'.($searchby == '' ? ' selected="selected"' : '').'>'.__('all fields', 'contact-manager').'</option>'; } ?>
<?php foreach ($searchby_options as $key => $value) {
echo '<option value="'.$key.'"'.($searchby == $key ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; } ?>
</select></label><br />
<input type="text" name="s" id="s" size="30" value="<?php if (isset($_GET['s'])) { echo $_GET['s']; } ?>" />
<input type="submit" class="button-secondary" name="submit" id="<?php echo $type; ?>-submit" value="<?php _e(ucfirst($type), 'contact-manager'); ?>" /></p>
<?php }


function contact_manager_pages_selector_default_option_content($field, $default_value) {
$content = __('Default option', 'contact-manager'); $description = $default_value;
if ($_GET['page'] == 'contact-manager') {
switch ($field) {
case 'sender_affiliate_category_id': case 'sender_affiliate_status':
case 'affiliation_registration_confirmation_email_sent':
case 'affiliation_registration_notification_email_sent': $content = __('Affiliation Manager\'s option', 'contact-manager'); break;
case 'sender_client_category_id': case 'sender_client_status':
case 'commerce_registration_confirmation_email_sent':
case 'commerce_registration_notification_email_sent': $content = __('Commerce Manager\'s option', 'contact-manager'); break;
case 'sender_member_category_id': case 'sender_member_status':
case 'membership_registration_confirmation_email_sent':
case 'membership_registration_notification_email_sent': $content = __('Member area\'s option', 'contact-manager'); } }
switch ($field) {
case 'sender_autoresponder': $description = $default_value; break;
case 'sender_affiliate_category_id': $description = ($default_value == 0 ? __('None ', 'contact-manager') : (function_exists('affiliate_category_data') ? htmlspecialchars(contact_excerpt(affiliate_category_data(array(0 => 'name', 'id' => $default_value)), 30)) : '')); break;
case 'sender_client_category_id': $description = ($default_value == 0 ? __('None ', 'contact-manager') : (function_exists('client_category_data') ? htmlspecialchars(contact_excerpt(client_category_data(array(0 => 'name', 'id' => $default_value)), 30)) : '')); break;
case 'sender_member_category_id': $description = ($default_value == 0 ? __('None ', 'contact-manager') : (function_exists('member_category_data') ? htmlspecialchars(contact_excerpt(member_category_data(array(0 => 'name', 'id' => $default_value)), 30)) : '')); break;
case 'sender_user_role': foreach (contact_manager_users_roles() as $role => $name) { if ($default_value == $role) { $description = $name; } } break;
default: $description = __(ucfirst($default_value), 'contact-manager'); }
if ($description != '') { $content .= ' ('.trim($description).')'; }
return $content; }


function contact_manager_pages_summary($back_office_options) {
if ($_GET['page'] == 'contact-manager') { $page_slug = 'options'; }
else { $page_slug = str_replace('-', '_', str_replace('contact-manager-', '', $_GET['page'])); }
$summary_displayed = $back_office_options[$page_slug.'_page_summary_displayed'];
include contact_path('admin-pages.php');
$modules = $modules[$page_slug];
$undisplayed_modules = (array) $back_office_options[$page_slug.'_page_undisplayed_modules'];
$list = ''; foreach ($modules as $key => $value) {
if ((!isset($_GET['id'])) || ($page_slug != 'message') || (!in_array($key, $add_message_modules))) {
if (!in_array($key, $undisplayed_modules)) { $list .= '<li>&nbsp;| <a href="#'.$key.'">'.$value['name'].'</a></li>'; } } }
if ($list != '') {
echo ($summary_displayed == 'yes' ? '' : '<p style="color: #0070c0; cursor: pointer; font-size: 1.2em; margin-left: 0.25em;" onclick="this.style.display = \'none\'; document.getElementById(\'page-summary\').style.display = \'\';">'.__('View the summary of this page', 'contact-manager').' <span style="font-size: 0.8em;">&#x25BC;</span></p>')
.'<ul id="page-summary" class="subsubsub" style="'.($summary_displayed == 'yes' ? '' : 'display: none; ').'float: none; margin-bottom: 1em; white-space: normal;"><li>'.substr($list, 12).'</ul>'; } }


function contact_manager_pages_title($back_office_options) {
if ($back_office_options['title_displayed'] == 'yes') { echo '<h1>'.$back_office_options['title'].'</h1>'; } }


function contact_manager_pages_top($back_office_options) {
contact_manager_pages_title($back_office_options);
contact_manager_pages_links($back_office_options);
echo '<div class="clear"></div>'; }


function contact_manager_users_roles() {
$wp_roles = new WP_Roles();
$roles = $wp_roles->get_names();
if ((defined('KLEOR_DEMO')) && (KLEOR_DEMO == true) && (isset($roles['tester']))) { unset($roles['tester']); }
if (!is_admin()) { load_textdomain('default', WP_LANG_DIR.'/admin-'.get_locale().'.mo'); }
foreach ($roles as $role => $name) { $roles[$role] = translate_user_role($name); }
return $roles; }


function contact_manager_utc_offset($item, $field) {
$item = (array) $item;
$keys = array($field, $field.'_utc');
foreach ($keys as $key) {
if ((!isset($item[$key])) || ($item[$key] == '0000-00-00 00:00:00')) { return 3600*UTC_OFFSET; } }
$timestamps = array();
foreach ($keys as $key) {
$d = preg_split('#[^0-9]#', $item[$key], 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? 1 : 0)); }
$timestamps[$key] = mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0]); }
return ($timestamps[$field] - $timestamps[$field.'_utc']); }


function update_contact_manager_back_office($back_office_options, $page) {
include contact_path('admin-pages.php');
if ((!isset($_POST[$page.'_page_summary_displayed'])) || ($_POST[$page.'_page_summary_displayed'] != 'yes')) { $_POST[$page.'_page_summary_displayed'] = 'no'; }
if ((strstr($_GET['page'], 'back-office')) && (isset($back_office_options[$page.'_page_custom_fields']))) {
$custom_fields = (array) $back_office_options[$page.'_page_custom_fields'];
$_POST[$page.'_page_custom_fields'] = array();
$n = count($custom_fields) + 5; for ($i = 1; $i <= $n; $i++) {
if (isset($_POST['delete_'.$page.'_page_custom_field'.$i])) { $_POST['delete_'.$page.'_page_custom_field'] = 'yes'; }
elseif ((isset($_POST[$page.'_page_custom_field_name'.$i])) && ($_POST[$page.'_page_custom_field_name'.$i] != '')) {
if ((!isset($_POST[$page.'_page_custom_field_key'.$i])) || ($_POST[$page.'_page_custom_field_key'.$i] == '')) {
$_POST[$page.'_page_custom_field_key'.$i] = $_POST[$page.'_page_custom_field_name'.$i]; }
$_POST[$page.'_page_custom_field_key'.$i] = str_replace('-', '_', kleor_format_nice_name($_POST[$page.'_page_custom_field_key'.$i]));
if ($_POST[$page.'_page_custom_field_key'.$i] != '') {
$_POST[$page.'_page_custom_fields'][$_POST[$page.'_page_custom_field_key'.$i]] = $_POST[$page.'_page_custom_field_name'.$i];
if (!isset($custom_fields[$_POST[$page.'_page_custom_field_key'.$i]])) { $_POST[$page.'_page_custom_fields_module_displayed'] = 'yes'; } } } } }
$_POST[$page.'_page_undisplayed_modules'] = array();
foreach ($modules[$page] as $key => $value) {
if (((!isset($_POST[$page.'_page_'.str_replace('-', '_', $key).'_module_displayed'])) || ($_POST[$page.'_page_'.str_replace('-', '_', $key).'_module_displayed'] != 'yes'))
 && ((!isset($value['required'])) || ($value['required'] != 'yes'))) { $_POST[$page.'_page_undisplayed_modules'][] = $key; }
if (isset($value['modules'])) { foreach ($value['modules'] as $module_key => $module_value) {
if (((!isset($_POST[$page.'_page_'.str_replace('-', '_', $module_key).'_module_displayed'])) || ($_POST[$page.'_page_'.str_replace('-', '_', $module_key).'_module_displayed'] != 'yes'))
 && ((!isset($module_value['required'])) || ($module_value['required'] != 'yes'))) { $_POST[$page.'_page_undisplayed_modules'][] = $module_key; } } } }
if (!strstr($_GET['page'], 'back-office')) {
foreach (array('custom_fields', 'summary_displayed', 'undisplayed_modules') as $option) {
if (isset($_POST[$page.'_page_'.$option])) { $back_office_options[$page.'_page_'.$option] = $_POST[$page.'_page_'.$option]; } }
kleor_update_option('contact_manager_back_office', $back_office_options);
return $back_office_options; } }


function contact_manager_pages_date_picker($start_date, $end_date) {
echo '<p style="margin: 0 0 1em 0; float: left;">
<input type="hidden" name="old_start_date" value="'.$start_date.'" /><label for="start_date"><strong>'.__('Start', 'contact-manager').'</strong>
<input class="date-pick" style="margin: 0.5em;" type="text" name="start_date" id="start_date" size="20" value="'.$start_date.'" onchange="this.value = kleor_format_date(this.value, \'start\');" /></label>
<input type="hidden" name="old_end_date" value="'.$end_date.'" /><label for="end_date" style="margin-left: 3em;"><strong>'.__('End', 'contact-manager').'</strong>
<input class="date-pick" style="margin: 0.5em;" type="text" name="end_date" id="end_date" size="20" value="'.$end_date.'" onchange="this.value = kleor_format_date(this.value, \'end\');" /></label>
<input style="margin-left: 3em; vertical-align: middle;" type="submit" class="button-secondary" name="submit" value="'.__('Display', 'contact-manager').'" /></p>
<div class="clear"></div>

<script>
function kleor_format_date(date, type) {
if (date.replace(/[^0-9]/g, "") == "") { date = ""; }
if (date == "") { if (type == "start") { date = "'.$start_date.'"; } else { date = "'.$end_date.'"; } }
else {
var a = date.split(/[^0-9]/g);
d = []; for (i = 0, n = a.length; i < n; i++) { if (a[i] != "") { d.push(a[i]); } }
for (i = 0; i < 6; i++) {
if (i >= d.length) {
if (i < 3) { d[i] = 1; }
else {
if (type == "start") { d[i] = 0; }
else { if (i == 3) { d[i] = 23; } else { d[i] = 59; } } } }
d[i] = parseInt(d[i]); }
if (d[0] < 1000) { d[0] = d[0] + 2000; }
var time = new Date(d[0], d[1] - 1, d[2], d[3], d[4], d[5], 0).getTime();
d[0] = new Date(time).getFullYear(time);
d[1] = new Date(time).getMonth(time) + 1;
d[2] = new Date(time).getDate(time);
d[3] = new Date(time).getHours(time);
d[4] = new Date(time).getMinutes(time);
d[5] = new Date(time).getSeconds(time);
for (i = 0; i < 6; i++) { if (d[i] < 10) { d[i] = "0"+d[i]; } }
date = d[0]+"-"+d[1]+"-"+d[2]+" "+d[3]+":"+d[4]+":"+d[5]; }
return date; }
</script>'; }


function contact_manager_date_picker_js() { ?>
<script>
Date.dayNames = ['<?php _e('Sunday', 'contact-manager'); ?>', '<?php _e('Monday', 'contact-manager'); ?>', '<?php _e('Tuesday', 'contact-manager'); ?>', '<?php _e('Wednesday', 'contact-manager'); ?>', '<?php _e('Thursday', 'contact-manager'); ?>', '<?php _e('Friday', 'contact-manager'); ?>', '<?php _e('Saturday', 'contact-manager'); ?>'];
Date.abbrDayNames = ['<?php _e('Sun', 'contact-manager'); ?>', '<?php _e('Mon', 'contact-manager'); ?>', '<?php _e('Tue', 'contact-manager'); ?>', '<?php _e('Wed', 'contact-manager'); ?>', '<?php _e('Thu', 'contact-manager'); ?>', '<?php _e('Fri', 'contact-manager'); ?>', '<?php _e('Sat', 'contact-manager'); ?>'];
Date.monthNames = ['<?php _e('January', 'contact-manager'); ?>', '<?php _e('February', 'contact-manager'); ?>', '<?php _e('March', 'contact-manager'); ?>', '<?php _e('April', 'contact-manager'); ?>', '<?php _e('May', 'contact-manager'); ?>', '<?php _e('June', 'contact-manager'); ?>', '<?php _e('July', 'contact-manager'); ?>', '<?php _e('August', 'contact-manager'); ?>', '<?php _e('September', 'contact-manager'); ?>', '<?php _e('October', 'contact-manager'); ?>', '<?php _e('November', 'contact-manager'); ?>', '<?php _e('December', 'contact-manager'); ?>'];
Date.abbrMonthNames = ['<?php _e('Jan', 'contact-manager'); ?>', '<?php _e('Feb', 'contact-manager'); ?>', '<?php _e('Mar', 'contact-manager'); ?>', '<?php _e('Apr', 'contact-manager'); ?>', '<?php _e('May', 'contact-manager'); ?>', '<?php _e('Jun', 'contact-manager'); ?>', '<?php _e('Jul', 'contact-manager'); ?>', '<?php _e('Aug', 'contact-manager'); ?>', '<?php _e('Sep', 'contact-manager'); ?>', '<?php _e('Oct', 'contact-manager'); ?>', '<?php _e('Nov', 'contact-manager'); ?>', '<?php _e('Dec', 'contact-manager'); ?>'];
jQuery.dpText = {
TEXT_PREV_YEAR : '<?php _e('Previous year', 'contact-manager'); ?>',
TEXT_PREV_MONTH : '<?php _e('Previous month', 'contact-manager'); ?>',
TEXT_NEXT_YEAR : '<?php _e('Next year', 'contact-manager'); ?>',
TEXT_NEXT_MONTH : '<?php _e('Next month', 'contact-manager'); ?>',
TEXT_CLOSE : '<?php _e('Close', 'contact-manager'); ?>',
TEXT_CHOOSE_DATE : '<?php _e('Choose a date', 'contact-manager'); ?>',
DATE_PICKER_ALT : '<?php _e('Date', 'contact-manager'); ?>',
DATE_PICKER_URL : '<?php echo contact_url('images/date-picker.png'); ?>',
HEADER_FORMAT : 'mmmm yyyy'};
jQuery(function(){ jQuery('.date-pick').datePicker({startDate:'2000-01-01'}); });
</script>
<?php }


function contact_manager_format_members_areas_modifications($content) {
$array = explode(',', strtoupper($content));
$modifications = array();
foreach ($array as $string) {
$string = explode('(', trim(str_replace(')', '', $string)));
if (count($string) == 2) {
$id = (int) preg_replace('/[^0-9]/', '', $string[0]);
$sign = (substr($string[0], 0, 1) == '-' ? '-' : '+');
$d = preg_split('#[^0-9]#', trim($string[1]), 0, PREG_SPLIT_NO_EMPTY);
if ((strstr($string[1], '/')) || (strstr($string[1], '-'))) {
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? 1 : 0)); }
$date = date('Y-m-d H:i:s', mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0])); }
else {
for ($i = 0; $i < 4; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : 0); }
if (strstr($string[1], 'Y')) { $date = $d[0].'Y'; }
elseif (strstr($string[1], 'M')) { $date = $d[0].'M'; }
elseif (strstr($string[1], 'W')) { $date = $d[0].'W'; }
else {
$date = $d[0].':'.$d[1].':'.$d[2].':'.$d[3];
$n = 0; for ($i = 0; $i < 3; $i++) { $n = $n + $d[3 - $i]; if ($n == 0) { $date = substr($date, 0, -2); } } } }
$modifications[$id] = array('sign' => $sign, 'date' => $date); } }
$members_areas = array();
foreach ($modifications as $key => $value) { $members_areas[] = $key; }
sort($members_areas, SORT_NUMERIC);
$content = '';
foreach ($members_areas as $key) { $content .= $modifications[$key]['sign'].$key." (".$modifications[$key]['date']."),\n" ; }
return $content; }


if ((is_admin()) && ((!isset($_GET['action'])) || (!in_array($_GET['action'], array('delete', 'reset', 'uninstall'))))) {
add_action('admin_enqueue_scripts', function() { wp_enqueue_script("jquery"); });
if (in_array($_GET['page'], array('contact-manager-back-office', 'contact-manager-form', 'contact-manager-form-category'))) { add_action('admin_enqueue_scripts', 'wp_enqueue_media'); }
if (!in_array($_GET['page'], array('contact-manager', 'contact-manager-back-office'))) {
add_action('admin_enqueue_scripts', function() {
wp_enqueue_style("contact-manager-date-picker", contact_url("libraries/date-picker.css"), array(), CONTACT_MANAGER_VERSION, "all");
wp_enqueue_script("contact-manager-date-picker", contact_url("libraries/date-picker.js"), array("jquery"), CONTACT_MANAGER_VERSION, true); });
add_action('admin_footer', 'contact_manager_date_picker_js'); } }
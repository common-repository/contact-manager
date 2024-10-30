<?php function contact_subscribe_to_autoresponder($autoresponder, $list, $contact) {
if ($list != '') {
include contact_path('libraries/personal-informations.php');
foreach (array_merge($personal_informations, array('ip_address', 'referrer')) as $field) { if (!isset($contact[$field])) { $contact[$field] = ''; } }
$contact['email_address'] = kleor_format_email_address($contact['email_address']);
$contact['website_url'] = kleor_format_url($contact['website_url']);
$autoresponders = contact_autoresponders();
if ((isset($autoresponders[$autoresponder])) && (isset($autoresponders[$autoresponder]['function']))) {
$function = $autoresponders[$autoresponder]['function'];
if (function_exists($function)) { $function($list, $contact); } } } }


function contact_subscribe_to_aweber($list, $contact) {
$original_list = $list;
$contact['first_name'] = kleor_strip_accents($contact['first_name']);
$array = explode('|', contact_data('aweber_api_key'));
if (count($array) != 4) { $email_parser = true; }
else {
$email_parser = false;
list($consumerKey, $consumerSecret, $accessToken, $accessSecret) = $array;
try {
if (!class_exists('AWeberAPI')) { include_once contact_path('libraries/aweber.php'); }
$aweber = new AWeberAPI($consumerKey, $consumerSecret);
$account = $aweber->getAccount($accessToken, $accessSecret);
if (!is_numeric(str_replace('awlist', '', $list))) {
$lists = $account->lists->find(array('name' => $list))->data;
if ((isset($lists['entries'])) && (isset($lists['entries'][0])) && (isset($lists['entries'][0]['id']))) { $list = $lists['entries'][0]['id']; } else { $email_parser = true; } }
if (!$email_parser) {
$list = $account->loadFromUrl('/accounts/'.$account->id.'/lists/'.preg_replace('/[^0-9]/', '', $list));
$list->subscribers->create(array(
'email' => $contact['email_address'],
'name' => $contact['first_name'],
'ip_address' => $contact['ip_address'],
'ad_tracking' => $contact['referrer'])); } }
catch(Exception $exc) { $email_parser = true; } }
if ($email_parser) {
$list = $original_list;
if (is_numeric($list)) { $list = 'awlist'.$list; }
$list = str_replace('à', '@', $list);
if (!strstr($list, '@')) { $list = $list.'@aweber.com'; }
$subject = 'AWeber Subscription';
$body =
"\nEmail: ".$contact['email_address'].
"\nName: ".$contact['first_name'].
"\nReferrer: ".$contact['referrer'];
$domain = $_SERVER['SERVER_NAME'];
if (substr($domain, 0, 4) == 'www.') { $domain = substr($domain, 4); }
if (strlen($domain) < 36) { $sender = 'wordpress@'.$domain; } else { $sender = 'w@'.$domain; }
foreach (array($sender, $contact['first_name'].' <'.$contact['email_address'].'>') as $string) {
mail($list, $subject, $body, 'From: '.$string); } } }


function contact_subscribe_to_cybermailing($list, $contact) {
wp_remote_get('https://www.cybermailing.com/mailing/subscribe.php?'.
'Liste='.$list.'&'.
'ListName='.$list.'&'.
'Identifiant='.$contact['login'].'&'.
'Name='.$contact['first_name'].'&'.
'Email='.$contact['email_address'].'&'.
'WebSite='.$contact['website_url'], array('timeout' => 10)); }


function contact_subscribe_to_getresponse($list, $contact) {
ini_set('display_errors', 0);
if (!class_exists('GetResponse')) { include_once contact_path('libraries/getresponse.php'); }
$api_key = contact_data('getresponse_api_key');
$getresponse = new GetResponse($api_key);
try {
$result = $getresponse->addContact(array(
'name' => $contact['first_name'],
'email' => $contact['email_address'],
'campaign' => array('campaignId' => $list),
'ipAddress' => $contact['ip_address'])); }
catch(Exception $e) { echo $e; }
return $result; }


function contact_subscribe_to_mailchimp($list, $contact) {
if (!class_exists('MailChimp')) { include_once contact_path('libraries/mailchimp.php'); }
$api_key = contact_data('mailchimp_api_key');
$MailChimp = new MailChimp($api_key);
$result = $MailChimp->call('lists/subscribe', array(
'id' => $list,
'email' => array('email' => $contact['email_address']),
'merge_vars' => array('FNAME' => $contact['first_name'], 'LNAME' => $contact['last_name']),
'double_optin' => false,
'update_existing' => true,
'replace_interests' => false)); }


function contact_subscribe_to_mailerlite($list, $contact) {
$body = array(
'email' => $contact['email_address'],
'name' => $contact['first_name'],
'fields' => array(
'last_name' => $contact['last_name'],
'company' => $contact['website_name'],
'country' => $contact['country'],
'city' => $contact['town'],
'phone' => $contact['phone_number'],
'state' => $contact['country'],
'zip' => $contact['postcode']));
wp_remote_post('https://api.mailerlite.com/api/v2/groups/'.$list.'/subscribers', array(
'headers' => array('x-mailerlite-apikey' => contact_data('mailerlite_api_key'), 'content-type' => 'application/json'),
'timeout' => 45, 'body' => json_encode($body))); }


function contact_subscribe_to_sendinblue($list, $contact) {
$a = array();
foreach (array('first_name', 'firstname', 'fname', 'prenom') as $key) { $a[$key] = $contact['first_name']; $a[strtoupper($key)] = $a[$key]; }
foreach (array('last_name', 'lastname', 'lname', 'nom') as $key) { $a[$key] = $contact['last_name']; $a[strtoupper($key)] = $a[$key]; }
$body = array(
'listIds' => array_unique(array_map('intval', preg_split('#[^0-9]#', $list, 0, PREG_SPLIT_NO_EMPTY))),
'email' => $contact['email_address'],
'attributes' => $a,
'updateEnabled'	=> true);
wp_remote_post('https://api.sendinblue.com/v3/contacts', array(
'headers' => array('accept' => 'application/json', 'api-key' => contact_data('sendinblue_api_key'), 'content-type' => 'application/json'),
'timeout' => 45, 'body' => json_encode($body))); }


function contact_subscribe_to_sendy($list, $contact) {
$body = array(
'api_key' => contact_data('sendy_api_key'),
'list' => $list,
'email' => $contact['email_address'],
'name' => $contact['first_name'].' '.$contact['last_name'],
'country' => $contact['country_code'],
'ipaddress' => $contact['ip_address'],
'referrer' => $contact['referrer'],
'gdpr' => 'true');
wp_remote_post(contact_data('sendy_url').'/subscribe', array('timeout' => 45, 'body' => $body)); }


function contact_subscribe_to_sg_autorepondeur($list, $contact) {
$body = array(
'action' => 'set_subscriber',
'membreid' => trim(contact_data('sg_autorepondeur_account_id')),
'codeactivation' => trim(contact_data('sg_autorepondeur_activation_code')),
'listeid' => trim($list),
'nom' => $contact['last_name'],
'prenom' => $contact['first_name'],
'email' => $contact['email_address'],
'adresse' => $contact['address'],
'codepostal' => $contact['postcode'],
'ville' => $contact['town'],
'pays'  => $contact['country'],
'siteweb' => $contact['website_url'],
'telephone' => $contact['phone_number'],
'parrain' => $contact['referrer'],
'pseudo' => $contact['login'],
'ip' => $contact['ip_address']);
if (is_serialized($contact['custom_fields'])) { $custom_fields = (array) unserialize(stripslashes($contact['custom_fields'])); }
else { $custom_fields = (array) $contact['custom_fields']; }
for ($i = 1; $i <= 16; $i++) {
if (isset($custom_fields['"'.$i.'"'])) { $body["champs_$i"] = $custom_fields['"'.$i.'"']; }
elseif (isset($custom_fields[$i])) { $body["champs_$i"] = $custom_fields[$i]; } }
wp_remote_post('https://sg-autorepondeur.com/API_V2/', array('timeout' => 45, 'body' => $body)); }


function contact_display_aweber($options, $back_office_options) { ?>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="aweber_api_key"><?php _e('API key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 50%;" name="aweber_api_key" id="aweber_api_key" rows="2" cols="50"><?php echo $options['aweber_api_key']; ?></textarea> 
<span class="description"><a target="<?php echo $back_office_options['documentations_links_target']; ?>" href="https://www.kleor.com/contact-manager/#aweber"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
<?php }


function contact_display_cybermailing($options, $back_office_options) { ?>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You have no adjustment to make so that the subscription works with CyberMailing, but do not enable the protection against bots.', 'contact-manager'); ?></span></td></tr>
</tbody></table>
<?php }


function contact_display_getresponse($options, $back_office_options) { ?>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="getresponse_api_key"><?php _e('API key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="getresponse_api_key" id="getresponse_api_key" rows="1" cols="50"><?php echo $options['getresponse_api_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a target="<?php echo $back_office_options['documentations_links_target']; ?>" href="https://www.kleor.com/contact-manager/#getresponse"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
<?php }


function contact_display_mailchimp($options, $back_office_options) { ?>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="mailchimp_api_key"><?php _e('API key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="mailchimp_api_key" id="mailchimp_api_key" rows="1" cols="50"><?php echo $options['mailchimp_api_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a target="<?php echo $back_office_options['documentations_links_target']; ?>" href="https://www.kleor.com/contact-manager/#mailchimp"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
<?php }


function contact_display_mailerlite($options, $back_office_options) { ?>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="mailerlite_api_key"><?php _e('API key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="mailerlite_api_key" id="mailerlite_api_key" rows="1" cols="50"><?php echo $options['mailerlite_api_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a target="<?php echo $back_office_options['documentations_links_target']; ?>" href="https://www.kleor.com/contact-manager/#mailerlite"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
<?php }


function contact_display_sendinblue($options, $back_office_options) { ?>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sendinblue_api_key"><?php _e('API key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sendinblue_api_key" id="sendinblue_api_key" rows="1" cols="50"><?php echo $options['sendinblue_api_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a target="<?php echo $back_office_options['documentations_links_target']; ?>" href="https://www.kleor.com/contact-manager/#sendinblue"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
<?php }


function contact_display_sendy($options, $back_office_options) { ?>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sendy_url"><?php _e('URL', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sendy_url" id="sendy_url" rows="1" cols="50"><?php echo $options['sendy_url']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a target="<?php echo $back_office_options['documentations_links_target']; ?>" href="https://www.kleor.com/contact-manager/#sendy"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sendy_api_key"><?php _e('API key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sendy_api_key" id="sendy_api_key" rows="1" cols="50"><?php echo $options['sendy_api_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a target="<?php echo $back_office_options['documentations_links_target']; ?>" href="https://www.kleor.com/contact-manager/#sendy"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
<?php }


function contact_display_sg_autorepondeur($options, $back_office_options) { ?>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sg_autorepondeur_account_id"><?php _e('Account ID', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="sg_autorepondeur_account_id" id="sg_autorepondeur_account_id" rows="1" cols="25"><?php echo $options['sg_autorepondeur_account_id']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a target="<?php echo $back_office_options['documentations_links_target']; ?>" href="https://www.kleor.com/contact-manager/#sg-autorepondeur"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sg_autorepondeur_activation_code"><?php _e('Activation code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sg_autorepondeur_activation_code" id="sg_autorepondeur_activation_code" rows="1" cols="50"><?php echo $options['sg_autorepondeur_activation_code']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a target="<?php echo $back_office_options['documentations_links_target']; ?>" href="https://www.kleor.com/contact-manager/#sg-autorepondeur"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
<?php }


function contact_update_aweber($options) {
if (isset($_POST['aweber_api_key'])) {
$_POST['aweber_api_key'] = trim($_POST['aweber_api_key']);
if ((isset($_POST['submit'])) && (substr_count($_POST['aweber_api_key'], '|') > 3)) {
if (!class_exists('AWeberAPI')) { include_once contact_path('libraries/aweber.php'); }
$array = AWeberAPI::getDataFromAweberID($_POST['aweber_api_key']);
if ((is_array($array)) && (count($array) == 4)) { $_POST['aweber_api_key'] = $array[0].'|'.$array[1].'|'.$array[2].'|'.$array[3]; } }
$options['aweber_api_key'] = $_POST['aweber_api_key']; }
return $options; }


function contact_update_sendy($options) {
if (isset($_POST['sendy_url'])) {
$url = trim($_POST['sendy_url']);
if (substr($url, -1) == '/') { $url = substr($url, 0, -1); }
if (substr($url, -10) == '/subscribe') { $url = substr($url, 0, -10); }
$options['sendy_url'] = $url; }
if (isset($_POST['sendy_api_key'])) { $options['sendy_api_key'] = trim($_POST['sendy_api_key']); }
return $options; }
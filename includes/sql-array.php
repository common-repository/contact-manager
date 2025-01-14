<?php if (!defined('ABSPATH')) { exit(); }
$sql = array();
foreach ($table as $key => $value) {
if (!isset($array[$key])) { $array[$key] = ''; }
$sql[$key] = ($key == 'password' ? contact_hash($array[$key]) : $array[$key]);
if (isset($value['type'])) {
if (strstr($value['type'], 'int')) { $sql[$key] = (int) $sql[$key]; }
elseif ((strstr($value['type'], 'dec')) && (!is_numeric($sql[$key]))) { $sql[$key] = round((float) $sql[$key], 2); }
elseif ((strstr($value['type'], 'text')) || (strstr($value['type'], 'date'))) {
while (strstr($sql[$key], "\'")) { $sql[$key] = str_replace("\'", "'", $sql[$key]); }
$sql[$key] = "'".str_replace("'", "''", $sql[$key])."'"; } } }
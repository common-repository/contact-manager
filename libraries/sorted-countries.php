<?php if (!defined('ABSPATH')) { exit(); }
include contact_path('libraries/countries.php');
$unsorted_countries = $countries;
$formatted_countries = array_map('kleor_format_nice_name', $countries);
asort($formatted_countries);
$countries = array(); foreach ($formatted_countries as $country_code => $country) { $countries[$country_code] = $unsorted_countries[$country_code]; }
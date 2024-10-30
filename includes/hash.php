<?php if (!defined('ABSPATH')) { exit(); }
$hash = $string;
$salt1 = (string) (defined('KLEOR_SALT1') ? KLEOR_SALT1 : '');
$salt2 = (string) (defined('KLEOR_SALT2') ? KLEOR_SALT2 : '');
$n = (int) (defined('KLEOR_HASH_ITERATIONS_NUMBER') ? KLEOR_HASH_ITERATIONS_NUMBER : 1);
if ($n < 1) { $n = 1; }
for ($i = 0; $i < $n; $i++) { $hash = hash('sha256', $salt1.$hash.$salt2); }
<?php function kleor_add_option($name, $value, $autoload = true) { return add_option($name, $value, '', ($autoload ? 'yes' : 'no')); }


function kleor_delete_option($name) { return delete_option($name); }


function kleor_get_option($name) { return get_option($name); }


function kleor_update_option($name, $value) { return update_option($name, $value); }
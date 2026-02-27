<?php
// includes/lang.php
// Language Translation Engine

// 1. Determine active language
// Set default language if not set (Default to Thai)
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'th';
}

$current_lang = $_SESSION['lang'];

// 2. Load the appropriate dictionary
$lang_file = __DIR__ . "/../lang/{$current_lang}.php";

if (file_exists($lang_file)) {
    $_L = require $lang_file;
} else {
    // Fallback to English if file is missing
    $_L = require __DIR__ . "/../lang/en.php";
}

// 3. Helper function to translate keys in views
if (!function_exists('__')) {
    function __($key) {
        global $_L;
        // Return the translated string if it exists, otherwise return the key itself
        return isset($_L[$key]) ? $_L[$key] : $key;
    }
}
?>

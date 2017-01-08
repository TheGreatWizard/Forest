<?php
// This is a preload script to be used with
// the Eclipse makegood continuous integration plugin
// see https://github.com/piece/makegood/releases
//error_reporting(E_ALL);

// Linux and Windows operating systems
define('DS', DIRECTORY_SEPARATOR);

// The root path of the site
define('ROOT', dirname(dirname(__FILE__)));
//define ( 'ROOT', dirname ( __FILE__ ) );

// Database connection
define('DB_NAME', 'UserData');
define('DB_USER', 'root');
define('DB_PASSWORD', 'new_password');
define('DB_HOST', '127.0.0.1');



require 'vendor/autoload.php';

echo "Hello from preload\n";

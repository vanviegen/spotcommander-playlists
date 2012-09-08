<?php

// Full path to the folder containing index.php, without trailing slash and whitespaces
// Do NOT remove the single quotes around the path
define('config_path', '/var/www/spotcommander');

// How often to automatically refresh what's playing (in seconds)
// Must be >= 30. Lower will disable it. Certain actions will refresh it immediately
define('config_nowplaying_update_interval', 60);

// Set to false to disable the Facebook like button
// You can still share to Facebook manually
define('config_facebook_like_button', true);

// Set to false to not check for updates and notify if a newer version is available
// It checks once daily (only when you are using the remote)
define('config_check_for_updates', true);

// Set to false to not send information about your system when checking for updates and when there is a fatal error
// By default it sends your browser's user agent and the output of the command 'uname -mrsv' on your server
// This information is useful when developing and testing SpotCommander
define('config_send_system_information', true);

// Set to false to disable keyboard shortcuts
// Check out the wiki for a complete list of keyboard shortcuts
define('config_keyboard_shortcuts', true);

?>

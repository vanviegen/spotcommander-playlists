<?php

/*

Copyright 2012 Ole Jon Bjørkum

This file is part of SpotCommander.

SpotCommander is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

SpotCommander is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with SpotCommander.  If not, see <http://www.gnu.org/licenses/>.

*/

require_once('main.php');

if(isset($_GET['error_code']))
{
	$error_code = intval($_GET['error_code']);
}
else
{
	$error_code = 0;
}

if($error_code == 1)
{
	$error_message = 'There is something wrong with your config.php file. Start over with a fresh config.php, and make sure that you read the text for an option carefully before changing it.';
}
elseif($error_code == 2)
{
	$error_message = 'It looks like you haven\'t configured the correct path in config.php.';
}
elseif($error_code == 3)
{
	$error_message = 'It looks like the daemon is not running.';
}
elseif($error_code == 4)
{
	$error_message = 'It looks like files and/or folders that should be writeable can\'t be written to. If your system is running SELinux, check out the SELinux page on the wiki.';
}
elseif($error_code == 5)
{
	$error_message = 'You must enable cookies in your browser.';
}
elseif($error_code == 6)
{
	$error_message = 'You must enable JavaScript in your browser.';
}
else
{
	$error_message = 'Unknown error.';
}

?>

<!DOCTYPE html>

<html>

<head>

<title><?php echo global_name; ?></title>

<meta http-equiv="content-type" content="text/html;charset=utf-8">
<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0, maximum-scale=1.0">

<link href="css/style-error.css?<?php echo global_serial ?>" rel="stylesheet" type="text/css">

<link rel="shortcut icon" href="img-nopreload/favicon.ico?<?php echo global_serial ?>">

</head>

<body>

<p class="center_p"><img src="img/error-64.png?<?php echo global_serial ?>" alt="Image"></p>

<div id="content_div"><div id="content_inner_div">

<h1><?php echo global_name; ?> error</h1>

<p><?php echo $error_message; ?></p>

<p>When the problem is fixed, click the link below.</p>

<p class="center_p"><a href="." onclick="window.location.replace('.'); return false">Back to remote</a> | <a href="http://code.google.com/p/spotcommander/w/list" target="_blank">Help on wiki</a></p>

</div></div>

</body>

</html>

<?php

$error_send_context = stream_context_create(array('http'=>array('timeout'=>5)));
@$error_send = file_get_contents(global_website . 'error.php?version=' . urlencode(global_version) . '&error_code=' . urlencode($error_code) . '&uname=' . urlencode($uname_send) . '&ua=' . urlencode($ua_send), false, $error_send_context);

?>

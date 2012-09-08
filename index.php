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

?>

<!DOCTYPE html>

<html>

<head>

<title><?php echo global_name ?></title>

<meta http-equiv="content-type" content="text/html;charset=utf-8">

<noscript><meta http-equiv="refresh" content="0; url=error.php?error_code=6"></noscript>

<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">

<script src="js/jquery.js?<?php echo global_serial; ?>" type="text/javascript"></script>
<script src="js/jquery-easing.js?<?php echo global_serial; ?>" type="text/javascript"></script>
<script src="js/modernizr.js?<?php echo global_serial; ?>" type="text/javascript"></script>
<script src="js/main.js?<?php echo global_serial; ?>" type="text/javascript"></script>

<link href="css/style.css?<?php echo global_serial; ?>" rel="stylesheet" type="text/css">

<style type="text/css"></style>

<link rel="shortcut icon" href="img-nopreload/favicon.ico?<?php echo global_serial ?>">

<link rel="apple-touch-icon" href="img-nopreload/touch-icon-57.png?<?php echo global_serial ?>">
<link rel="apple-touch-icon" sizes="72x72" href="img-nopreload/touch-icon-72.png?<?php echo global_serial ?>">
<link rel="apple-touch-icon" sizes="114x114" href="img-nopreload/touch-icon-114.png?<?php echo global_serial ?>">
<link rel="apple-touch-startup-image" href="img-nopreload/splash-ipad-landscape.png?<?php echo global_serial ?>" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape) and (-webkit-min-device-pixel-ratio: 1)">
<link rel="apple-touch-startup-image" href="img-nopreload/splash-ipad-portrait.png?<?php echo global_serial ?>" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait) and (-webkit-min-device-pixel-ratio: 1)">
<link rel="apple-touch-startup-image" href="img-nopreload/splash-iphone.png?<?php echo global_serial ?>" media="screen and (max-device-width: 320px) and (-webkit-min-device-pixel-ratio: 1)">

</head>

<body>

<input type="hidden" id="spotify_ps_input" value="<?php echo $spotify_ps; ?>">

<div id="transparent_cover_div" onclick="void(0)"></div>
<div id="black_cover_div" onclick="void(0)"></div>

<div id="topbar_div" onclick="void(0)">
<div id="topbar_bar_div">
<div id="topbar_table_div">
<div id="topbar_table_left_div"><div id="menu_click_div" title="Menu" onclick="void(0)"><img src="img/menu-32.png?<?php echo global_serial; ?>" alt="Image"></div></div>
<div id="topbar_table_vertical_line_div"><div></div></div>
<div id="topbar_table_center_div"><div id="page_title_div"></div></div>
<div id="topbar_table_right_div"><div id="more_menu_click_div" title="More" onclick="void(0)"><img src="img/more-32.png?<?php echo global_serial; ?>" alt="Image"></div></div>
</div>
</div>
<div id="topbar_shadow_div"></div>
</div>

<div id="more_menu_div">
<div id="more_menu_arrow_div"></div>
<div id="more_menu_inner_div"></div>
</div>

<div id="menu_div">
<div id="menu_inner_div">
<div class="menu_item_click_div" title="Playlists" onclick="void(0)"><div><img src="img/playlist-48.png?<?php echo global_serial; ?>" alt="Image"></div><div>Playlists</div></div>
<div class="menu_item_click_div" title="Starred" onclick="void(0)"><div><img src="img/star-48.png?<?php echo global_serial; ?>" alt="Image"></div><div>Starred</div></div>
<div class="menu_item_click_div" title="Search" onclick="void(0)"><div><img src="img/search-48.png?<?php echo global_serial; ?>" alt="Image"></div><div>Search</div></div>
<div class="menu_item_click_div" title="About" onclick="void(0)"><div><img src="img/about-48.png?<?php echo global_serial; ?>" id="about_img" alt="Image"></div><div>About</div></div>
</div>
</div>

<div id="page_div" onclick="void(0)"></div>
<div id="message_div"></div>
<div id="nowplaying_div"></div>

<div id="bottombar_div" onclick="void(0)">
<div id="bottombar_table_div">
<div id="bottombar_table_left_div"><div id="nowplaying_remote_click_div" title="Now playing + remote control" onclick="void(0)"><img src="img/nowplaying-details-open-32.png?<?php echo global_serial; ?>" alt="Image"></div></div>
<div id="bottombar_table_center_div"><div id="bottombar_nowplaying_div"></div></div>
<div id="bottombar_table_right_div"><div id="nowplaying_refresh_click_div" title="Refresh now playing" onclick="void(0)"><img src="img/refresh-32.png?<?php echo global_serial; ?>" alt="Image"></div></div>
</div>
</div>

<div id="preload_div">

<?php

$preload_files = glob('img/*.{png,gif}', GLOB_BRACE);

foreach($preload_files as $preload_file)
{
	echo '<img src="' . $preload_file . '?' . global_serial . '" alt="Image">';
}

?>

</div>

</body>

</html>


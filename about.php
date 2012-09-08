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

<span id="page_title_content_span">About</span>

<div id="more_menu_content_div">
<div class="open_window_click_div" onclick="void(0)">Visit website<span class="hidden_value_span"><?php echo global_website; ?></span></div>
<div id="check_for_updates_click_div" onclick="void(0)">Check for updates<span class="hidden_value_span"></span></div>
<div id="delete_cookies_click_div" onclick="void(0)">Delete cookies<span class="hidden_value_span"></span></div>
</div>

<div id="about_div">

<?php

if(isset($_COOKIE['latest_version']))
{
	$latest_version = $_COOKIE['latest_version'];
}
else
{
	$latest_version = 'Unknown';
}

echo '
	<div>' . global_name . ' ' . global_version . '</div>
	<div>Latest version: ' . $latest_version . '</div>
';

if(is_numeric($latest_version) && $latest_version > global_version)
{
	echo '<div><b>There is an update available</b></div>';
}

?>

</div>

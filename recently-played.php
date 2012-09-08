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

if(isset($_GET['clear']))
{
	echo clear_recently_played();
}
else
{
	echo '
		<span id="page_title_content_span">Recently played</span>
		<div id="more_menu_content_div">
		<div id="clear_recently_played_click_div" onclick="void(0)">Clear<span class="hidden_value_span"></span></div>
		</div>
	';

	$db = new SQLite3('db/recently-played.db');

	$query = $db->query("SELECT * FROM recently_played ORDER BY id DESC");
	$row = array();
	$i = 0;

	while($track = $query->fetchArray(SQLITE3_ASSOC))
	{
		$i++; 

		$row[$i]['artist'] = $track['artist'];
		$row[$i]['title'] = $track['title'];
		$row[$i]['uri'] = $track['uri'];
	}

	if(empty($row))
	{
		echo '<div id="page_message_div">No recently played tracks</div>';
	}
	else
	{
		echo '<div class="category_title_div">ALL</div>';

		$i = 0;

		foreach($row as $track)
		{
			$i++;

			$artist = $track['artist'];
			$title = $track['title'];
			$uri = $track['uri'];

			echo '
				<div class="media_div">
				<div class="media_arrow_div" id="media_inner_div_' . $i . '_arrow"></div>
				<div class="media_corner_arrow_div" id="media_inner_div_' . $i . '_corner_arrow"></div>
				<div class="media_inner_div" id="media_inner_div_' . $i . '" title="' . hsc($artist . ' - ' . $title) . '" onclick="void(0)"><div class="media_left_div"><img src="img/track-24.png?' . global_serial . '" alt="Image"></div><div class="media_right_div"><div class="media_right_upper_div">' . hsc($title) . '</div><div class="media_right_lower_div">' . hsc($artist) . '</div></div></div>
				</div>
				<div class="media_options_div" id="media_inner_div_' . $i . '_options">
				<div class="media_options_inner_div">
				<div class="media_play_uri_click_div" title="Play" onclick="void(0)"><img src="img/play-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
				<div class="media_star_click_div" title="Star" onclick="void(0)"><img src="img/' . is_starred($uri) . '-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">track|:|' . urlencode($artist) . '|:|' . urlencode($title) . '|:|' . $uri . '</span></div>
				<div class="media_share_click_div" title="Share" onclick="void(0)"><img src="img/share-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . urlencode(uri_to_url($uri)) . '</span></div>
				</div>
				</div>
			';
		}
	}
}

?>

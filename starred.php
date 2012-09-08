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

if(isset($_GET['star']))
{
	$type = sqlite_escape($_POST['type']);
	$artist = sqlite_escape($_POST['artist']);
	$title = sqlite_escape($_POST['title']);
	$value = sqlite_escape($_POST['uri']);

	echo star($type, $artist, $title, $value);
}
elseif(isset($_GET['unstar']))
{
	$uri = sqlite_escape($_POST['uri']);

	echo unstar($uri);
}
else
{
	echo '
		<span id="page_title_content_span">Starred</span>

		<div id="more_menu_content_div">
		<div class="media_browse_playlist_click_div" onclick="void(0)">Top tracks<span class="hidden_value_span">spotify:user:spotify:playlist:4hOKQuZbraPDIfaGbM3lKI</span></div>
		</div>

		<div class="category_title_div">TRACKS</div>
	';

	$db = new SQLite3('db/starred.db');

	$query = $db->query("SELECT * FROM starred WHERE type='track' ORDER BY type, artist COLLATE NOCASE");
	$row = array();
	$i = 0;

	while($starred = $query->fetchArray(SQLITE3_ASSOC))
	{
		$i++; 

		$row[$i]['artist'] = urldecode($starred['artist']);
		$row[$i]['title'] = urldecode($starred['title']);
		$row[$i]['uri'] = $starred['uri'];
	}

	$i = 0;

	if(count($row) == 0)
	{
		echo '<div class="media_empty_div">No tracks. You can always check out the <span class="show_page_click_span">top tracks<span class="hidden_value_span">1|:|playlists|:|browse|:|uri=spotify:user:spotify:playlist:4hOKQuZbraPDIfaGbM3lKI</span></span> currently on Spotify.</div>';
	}
	else
	{
		foreach($row as $starred)
		{
			$i++;

			$artist = $starred['artist'];
			$title = $starred['title'];
			$uri = $starred['uri'];

			echo '
				<div class="media_div">
				<div class="media_arrow_div" id="media_inner_div_' . $i . '_arrow"></div>
				<div class="media_corner_arrow_div" id="media_inner_div_' . $i . '_corner_arrow"></div>
				<div class="media_inner_div" id="media_inner_div_' . $i . '" title="' . hsc($artist . ' - ' . $title) . '" onclick="void(0)"><div class="media_left_div"><img src="img/track-24.png?' . global_serial . '" alt="Image"></div><div class="media_right_div"><div class="media_right_upper_div">' . hsc($title) . '</div><div class="media_right_lower_div">' . hsc($artist) . '</div></div></div>
				</div>
				<div class="media_options_div" id="media_inner_div_' . $i . '_options">
				<div class="media_options_inner_div">
				<div class="media_play_uri_click_div" title="Play" onclick="void(0)"><img src="img/play-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
				<div class="media_share_click_div" title="Share" onclick="void(0)"><img src="img/share-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . urlencode(uri_to_url($uri)) . '</span></div>
				<div class="media_unstar_click_div" title="Remove" onclick="void(0)"><img src="img/remove-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
				</div>
				</div>
			';
		}
	}

	echo '<div class="category_title_div category_title_below_div">ALBUMS</div>';

	$query = $db->query("SELECT * FROM starred WHERE type='album' ORDER BY type, artist COLLATE NOCASE");
	$row = array();

	while($starred = $query->fetchArray(SQLITE3_ASSOC))
	{
		$i++; 

		$row[$i]['artist'] = urldecode($starred['artist']);
		$row[$i]['title'] = urldecode($starred['title']);
		$row[$i]['uri'] = $starred['uri'];
	}

	if(count($row) == 0)
	{
		echo '<div class="media_empty_div">No albums.</div>';
	}
	else
	{
		foreach($row as $starred)
		{
			$i++;

			$artist = $starred['artist'];
			$title = $starred['title'];
			$uri = $starred['uri'];

			echo '
				<div class="media_div">
				<div class="media_arrow_div" id="media_inner_div_' . $i . '_arrow"></div>
				<div class="media_corner_arrow_div" id="media_inner_div_' . $i . '_corner_arrow"></div>
				<div class="media_inner_div" id="media_inner_div_' . $i . '" title="' . hsc($artist . ' - ' . $title) . '" onclick="void(0)"><div class="media_left_div"><img src="img/album-24.png?' . global_serial . '" alt="Image"></div><div class="media_right_div"><div class="media_right_upper_div">' . hsc($title) . '</div><div class="media_right_lower_div">' . hsc($artist) . '</div></div></div>
				</div>
				<div class="media_options_div" id="media_inner_div_' . $i . '_options">
				<div class="media_options_inner_div">
				<div class="media_play_uri_click_div" title="Play" onclick="void(0)"><img src="img/play-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
				<div class="media_play_random_uri_click_div" title="Play random" onclick="void(0)"><img src="img/play-random-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
				<div class="media_browse_album_click_div" title="Browse" onclick="void(0)"><img src="img/browse-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
				<div class="media_share_click_div" title="Share" onclick="void(0)"><img src="img/share-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . urlencode(uri_to_url($uri)) . '</span></div>
				<div class="media_unstar_click_div" title="Remove" onclick="void(0)"><img src="img/remove-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
				</div>
				</div>
			';
		}
	}
}

?>

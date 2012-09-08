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

if(isset($_GET['add']))
{
	echo '
		<span id="page_title_content_span">Add playlist</span>

		<div id="more_menu_content_div">
		<div class="show_page_click_div" onclick="void(0)">How to<span class="hidden_value_span">0|:|playlists|:|how_to_add|:|</span></div>
		</div>

		<div id="add_playlist_div">
		<form method="post" action="." id="add_playlist_form">
		<div class="input_text_div"><input type="text" id="add_playlist_uri_input" value="URIs/URLs..." autocapitalize="off"></div>
		<div class="hidden_div"><input type="submit" value="Add"></div>
		</form>
		</div>

		<div id="input_below_div"><span class="show_page_click_span">Learn how to add playlists<span class="hidden_value_span">0|:|playlists|:|how_to_add|:|</span></span></div>
	';
}
elseif(isset($_GET['how_to_add']))
{
	echo '
		<span id="page_title_content_span">How to add playlists</span>

		<div id="how_to_add_playlists_android_app_div">

		<div class="category_title_div category_title_below_div">USING ANDROID APP</div>

		<ol>
		<li>Open the Spotify app</li>
		<li>Select the playlist you want to add</li>
		<li>Choose to share it</li>
		<li>Choose ' . global_name . ' in the list</li>
		</ol>

		</div>

		<div class="category_title_div category_title_below_div">ON DESKTOP COMPUTERS</div>

		<ol>
		<li>Open Spotify</li>
		<li>Right-click on a playlist and choose "Copy Spotify URI"</li>
		<li>Paste it in the URI/URL field</li>
		<li>Save with the return key on the keyboard</li>
		</ol>

		<div class="category_title_div category_title_below_div">ON MOBILE DEVICES</div>

		<ol>
		<li>Open the Spotify app</li>
		<li>Select the playlist you want to add</li>
		<li>Choose to share it</li>
		<li>Copy the URI/URL</li>
		<li>Paste it in the URI/URL field</li>
		<li>Save with the return key on the keyboard</li>
		</ol>

		<div class="category_title_div category_title_below_div">NOTES</div>

		<ul>
		<li>Multiple playlists can be added at once, separated by a comma</li>
		<li>Discover music by checking out the <span class="show_page_click_span">top playlists<span class="hidden_value_span">1|:|playlists|:|top|:|</span></span> currently on the web</li>
		<li>There are many websites with Spotify playlists. Just copy & paste the URL</li>
		<li>Unfortunately, there is no way to fetch your playlists from Spotify automatically</li>
		</ul>
	';
}
elseif(isset($_GET['save']))
{
	$uris = $_POST['uris'];
	echo save_playlist(sqlite_escape($uris));
}
elseif(isset($_GET['remove']))
{
	$id = sqlite_escape($_POST['id']);
	echo remove_playlist($id);
}
elseif(isset($_GET['top']))
{
	echo '<span id="page_title_content_span">Top playlists</span>';

	$get_context = stream_context_create(array('http'=>array('timeout'=>5)));
	@$get = file_get_contents('http://sharemyplaylists.com/chart', false, $get_context);

	if(empty($get))
	{
		$no_match = true;
	}
	else
	{
		preg_match_all("'<a class=\"position\" title=\"(.*?)\" \b[^>]*>'si", $get, $titles);
		preg_match_all("'<a \b[^>]* rel=\"playlist play\" href=\"(.*?)\">'si", $get, $uris);

		if($uris[1] && $titles[1])
		{
			echo '<div class="category_title_div">ALL</div>';

			$i = 0;
			$n = 0;

			foreach($uris[1] as $uri)
			{
				$i++;

				$name = ltrim($titles[1][$n], 'View ');
				$user = explode(':', $uri);
				$user = $user[2];

				$add = '';

				if(!playlist_exists($uri))
				{
					$add .= '<div class="media_add_playlist_click_div" title="Add" onclick="void(0)"><img src="img/add-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>';
				}

				echo '
					<div class="media_div">
					<div class="media_arrow_div" id="media_inner_div_' . $i . '_arrow"></div>
					<div class="media_corner_arrow_div" id="media_inner_div_' . $i . '_corner_arrow"></div>
					<div class="media_inner_div" id="media_inner_div_' . $i . '" title="' . $name . '" onclick="void(0)"><div class="media_left_div"><img src="img/playlist-24.png?' . global_serial . '" alt="Image"></div><div class="media_right_div"><div class="media_right_upper_div">' . $name . '</div><div class="media_right_lower_div">' . $user . '</div></div></div>
					</div>
					<div class="media_options_div" id="media_inner_div_' . $i . '_options">
					<div class="media_options_inner_div">
					<div class="media_play_uri_click_div" title="Play" onclick="void(0)"><img src="img/play-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
					<div class="media_play_random_uri_click_div" title="Play random" onclick="void(0)"><img src="img/play-random-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
					<div class="media_browse_playlist_click_div" title="Browse" onclick="void(0)"><img src="img/browse-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
					<div class="media_share_click_div" title="Share" onclick="void(0)"><img src="img/share-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . urlencode(uri_to_url($uri)) . '</span></div>
					' . $add . '
					</div>
					</div>
				';

				$n++;
			}
		}
		else
		{
			$no_match = true;
		}
	}

	if(isset($no_match))
	{
		echo '<div id="page_message_div">Couldn\'t load playlists. Try again later.</div>';
	}
}
elseif(isset($_GET['browse']))
{
	$uri = $_GET['uri'];

	$initial_results = 20;

	$get_context = stream_context_create(array('http'=>array('timeout'=>5)));
	@$get = file_get_contents('https://embed.spotify.com/?uri=' . $uri, false, $get_context);

	if(empty($get))
	{
		$no_match = true;
	}
	else
	{
		preg_match_all("'<title>(.*?)</title>'si", $get, $name);
		preg_match_all("'<li class=\"artist \b[^>]*>(.*?)</li>'si", $get, $artists);
		preg_match_all("'<li class=\"track-title \b[^>]*>(.*?)</li>'si", $get, $titles);
		preg_match_all("'<li \b[^>]* data-track=\"(.*?)\" \b[^>]*>'si", $get, $uris);

		if($name[1] && $artists[1] && $titles[1] && $uris[1])
		{
			$name = strstr($name[1][0], ' by', true);

			$add = '';

			if(!playlist_exists($uri))
			{
				$add .= '<div class="media_add_playlist_click_div" onclick="void(0)">Add to my playlists<span class="hidden_value_span">' . $uri . '</span></div>';
			}

			echo '
				<span id="page_title_content_span">' . $name . '</span>

				<div id="more_menu_content_div">
				<div class="media_play_uri_click_div" onclick="void(0)">Play<span class="hidden_value_span">' . $uri . '</span></div>
				<div class="media_play_random_uri_click_div" onclick="void(0)">Play random<span class="hidden_value_span">' . $uri . '</span></div>
				<div class="media_share_click_div" onclick="void(0)">Share<span class="hidden_value_span">' . urlencode(uri_to_url($uri)) . '</span></div>
				' . $add . '
				</div>

				<div class="category_title_div">ALL</div>
			';

			$count = count($uris[1]);

			$i = 0;
			$n = 0;

			foreach($uris[1] as $uri)
			{
				$i++;

				$artist = $artists[1][$n];
				$title = substr($titles[1][$n], strpos($titles[1][$n], ' '));
				$uri = 'spotify:track:' . $uri;

				if($i > 20)
				{
					$style = 'display: none';
				}
				else
				{
					$style = '';
				}

				echo '
					<div class="media_div" style="' . $style . '">
					<div class="media_arrow_div" id="media_inner_div_' . $i . '_arrow"></div>
					<div class="media_corner_arrow_div" id="media_inner_div_' . $i . '_corner_arrow"></div>
					<div class="media_inner_div" id="media_inner_div_' . $i . '" title="' . $artist . ' - ' . $title . '" onclick="void(0)"><div class="media_left_div"><img src="img/track-24.png?' . global_serial . '" alt="Image"></div><div class="media_right_div"><div class="media_right_upper_div">' . hsc($title) . '</div><div class="media_right_lower_div">' . hsc($artist) . '</div></div></div>
					</div>
					<div class="media_options_div" id="media_inner_div_' . $i . '_options">
					<div class="media_options_inner_div">
					<div class="media_play_uri_click_div" title="Play" onclick="void(0)"><img src="img/play-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
					<div class="media_star_click_div" title="Star" onclick="void(0)"><img src="img/' . is_starred($uri) . '-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">track|:|' . urlencode($artist) . '|:|' . urlencode($title) . '|:|' . $uri . '</span></div>
					<div class="media_share_click_div" title="Share" onclick="void(0)"><img src="img/share-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . urlencode(uri_to_url($uri)) . '</span></div>
					</div>
					</div>
				';

				$n++;
			}

			if($count > $initial_results)
			{
				echo '<div class="media_show_more_click_div" onclick="void(0)">Show all tracks</div>';
			}
		}
		else
		{
			$no_match = true;
		}
	}

	if(isset($no_match))
	{
		echo '<div id="page_message_div" title="Error">Couldn\'t get playlist. Try again later.</div>';
	}
}
else
{
	echo '
		<span id="page_title_content_span">Playlists</span>

		<div id="more_menu_content_div">
		<div class="show_page_click_div" onclick="void(0)">Add playlist<span class="hidden_value_span">0|:|playlists|:|add|:|</span></div>
		<div class="show_page_click_div" onclick="void(0)">Top playlists<span class="hidden_value_span">1|:|playlists|:|top|:|</span></div>
		</div>
	';

	$db = new SQLite3('db/playlists.db');

	$query = $db->query("SELECT * FROM playlists ORDER BY name COLLATE NOCASE");
	$row = array();
	$i = 0;

	while($playlist = $query->fetchArray(SQLITE3_ASSOC))
	{
		$i++; 

		$row[$i]['id'] = $playlist['id'];
		$row[$i]['name'] = $playlist['name'];
		$row[$i]['uri'] = urldecode($playlist['uri']);
	}

	if(count($row) == 0)
	{
		echo '<div id="page_message_div">You have not added any playlists yet. <span class="show_page_click_span">Add playlists<span class="hidden_value_span">0|:|playlists|:|add|:|</span></span> or check out the <span class="show_page_click_span">top playlists<span class="hidden_value_span">1|:|playlists|:|top|:|</span></span> currently on the web.</div>';
	}
	else
	{
		echo '<div class="category_title_div">ALL</div>';

		$i = 0;

		foreach($row as $playlist)
		{
			$i++;

			$id = $playlist['id'];
			$name = $playlist['name'];
			$uri = $playlist['uri'];
			$user = explode(':', $uri);
			$user = $user[2];

			if($name == 'Unknown')
			{
				$name = 'Unknown (ID: ' . $id . ')';
			}

			echo '
				<div class="media_div">
				<div class="media_arrow_div" id="media_inner_div_' . $i . '_arrow"></div>
				<div class="media_corner_arrow_div" id="media_inner_div_' . $i . '_corner_arrow"></div>
				<div class="media_inner_div" id="media_inner_div_' . $i . '" title="' . hsc($name) . '" onclick="void(0)"><div class="media_left_div"><img src="img/playlist-24.png?' . global_serial . '" alt="Image"></div><div class="media_right_div"><div class="media_right_upper_div">' . hsc($name) . '</div><div class="media_right_lower_div">' . hsc($user) . '</div></div></div>
				</div>
				<div class="media_options_div" id="media_inner_div_' . $i . '_options">
				<div class="media_options_inner_div">
				<div class="media_play_uri_click_div" title="Play" onclick="void(0)"><img src="img/play-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
				<div class="media_play_random_uri_click_div" title="Play random" onclick="void(0)"><img src="img/play-random-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
				<div class="media_browse_playlist_click_div" title="Browse" onclick="void(0)"><img src="img/browse-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
				<div class="media_share_click_div" title="Share" onclick="void(0)"><img src="img/share-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . urlencode(uri_to_url($uri)) . '</span></div>
				<div class="media_remove_playlist_click_div" title="Remove" onclick="void(0)"><img src="img/remove-24.png?' . global_serial . '" alt="' . $id . '"><span class="hidden_value_span">' . $id . '</span></div>
				</div>
				</div>
			';
		}
	}
}

?>

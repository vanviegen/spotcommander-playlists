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

if(isset($_GET['search']))
{
	$string = urldecode($_GET['string']);

	$initial_results = 5;
	$total_results = 50;

	echo '<span id="page_title_content_span">&quot;' . hsc(ucfirst($string)) . '&quot;</span>';

	require_once('lib/config.php');
	$metatune = MetaTune::getInstance();

	try
	{
		$tracks = $metatune->searchTrack($string);
		$tracks = array_slice($tracks, 0, $total_results);
		$i = 0;

		echo '<div class="category_title_div" id="media_more_tracks_div_title">TRACKS</div>';

		$count = count($tracks);

		if($count == 0)
		{
			echo '<div class="media_empty_div">No tracks.</div>';
		}
		else
		{
			foreach ($tracks as $track)
			{
				$i++;

				$artist = $track->getArtistAsString();
				$title = $track->getTitle();
				$uri = $track->getURI();

				if($i > $initial_results)
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

			if($count > $initial_results)
			{
				echo '<div class="media_show_more_click_div" id="media_more_tracks_div" onclick="void(0)">Show all tracks</div>';
			}
		}

		$albums = $metatune->searchAlbum($string);
		$albums = array_slice($albums, 0, $total_results);

		echo '<div class="category_title_div category_title_below_div" id="media_more_albums_div_title">ALBUMS</div>';

		$count = count($albums);

		if($count == 0)
		{
			echo '<div class="media_empty_div">No albums.</div>';
		}
		else
		{
			foreach ($albums as $album)
			{
				$i++;

				$artist = $album->getArtist();
				$title = $album->getName();
				$uri = $album->getURI();

				if($i > $total_results + $initial_results)
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
					<div class="media_inner_div" id="media_inner_div_' . $i . '" title="' . hsc($artist . ' - ' . $title) . '" onclick="void(0)"><div class="media_left_div"><img src="img/album-24.png?' . global_serial . '" alt="Image"></div><div class="media_right_div"><div class="media_right_upper_div">' . hsc($title) . '</div><div class="media_right_lower_div">' . hsc($artist) . '</div></div></div>
					</div>
					<div class="media_options_div" id="media_inner_div_' . $i . '_options">
					<div class="media_options_inner_div">
					<div class="media_play_uri_click_div" title="Play" onclick="void(0)"><img src="img/play-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
					<div class="media_play_random_uri_click_div" title="Play random" onclick="void(0)"><img src="img/play-random-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
					<div class="media_browse_album_click_div" title="Browse" onclick="void(0)"><img src="img/browse-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . $uri . '</span></div>
					<div class="media_star_click_div" title="Star" onclick="void(0)"><img src="img/' . is_starred($uri) . '-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">album|:|' . urlencode($artist) . '|:|' . urlencode($title) . '|:|' . $uri . '</span></div>
					<div class="media_share_click_div" title="Share" onclick="void(0)"><img src="img/share-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . urlencode(uri_to_url($uri)) . '</span></div>
					</div>
					</div>
				';
			}

			if($count > $initial_results)
			{
				echo '<div class="media_show_more_click_div" id="media_more_albums_div" onclick="void(0)">Show all albums</div>';
			}
		}

		save_search_history(sqlite_escape($string));
	}

	catch (MetaTuneException $ex)
	{
		die('<div id="page_message_div">Search API error. Try again.</div>');
	}
}
elseif(isset($_GET['browse_album']))
{
	$uri = $_GET['uri'];

	require_once('lib/config.php');
	$metatune = MetaTune::getInstance();

	try
	{
		if(strstr($uri, 'spotify:track:'))
		{
			$details = $metatune->lookupTrack($uri, true);
			$uri = $details->getAlbum()->getURI();
		}
		else
		{
			$uri = $uri;
		}

		$tracks = $metatune->lookupAlbum($uri, true);

		$artist = $tracks->getArtist();
		$title = $tracks->getName();

		if(is_starred($uri) == 'starred')
		{
			$starred_text = 'Unstar';
		}
		else
		{
			$starred_text = 'Star';
		}

		echo '
			<span id="page_title_content_span">' . hsc($title) . '</span>

			<div id="more_menu_content_div">
			<div class="media_play_uri_click_div" onclick="void(0)">Play<span class="hidden_value_span">' . $uri . '</span></div>
			<div class="media_play_random_uri_click_div" onclick="void(0)">Play random<span class="hidden_value_span">' . $uri . '</span></div>
			<div class="media_star_click_div" onclick="void(0)"><span class="text_span">' . $starred_text . '</span><span class="hidden_value_span">album|:|' . urlencode($artist) . '|:|' . urlencode($title) . '|:|' . $uri . '</span></div>
			<div class="media_share_click_div" onclick="void(0)">Share<span class="hidden_value_span">' . urlencode(uri_to_url($uri)) . '</span></div>
			</div>

			<div class="category_title_div">ALL</div>
		';

		$i = 0;

		foreach ($tracks->getTracks() as $track)
		{
			$i++;

			$artist = $track->getArtistAsString();
			$title = $track->getTitle();
			$uri = $track->getURI();

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

	catch (MetaTuneException $ex)
	{
		die('<div id="page_message_div" title="Error">Search API error. Try again.</div>');
	}
}
elseif(isset($_GET['history']))
{
	if(isset($_GET['clear']))
	{
		echo clear_search_history();
	}
	else
	{
		echo '
			<span id="page_title_content_span">Search history</span>

			<div id="more_menu_content_div">
			<div id="clear_search_history_click_div" onclick="void(0)">Clear<span class="hidden_value_span"></span></div>
			</div>
		';

		$db = new SQLite3('db/search-history.db');

		$query = $db->query("SELECT * FROM search_history ORDER BY id DESC");
		$row = array();
		$i = 0;

		while($history = $query->fetchArray(SQLITE3_ASSOC))
		{
			$i++;

			$row[$i]['string'] = $history['string'];
		}

		if(empty($row))
		{
			echo '<div id="page_message_div">Empty search history</div>';
		}
		else
		{
			echo '<div class="category_title_div">ALL</div>';

			$i = 0;

			foreach($row as $history)
			{
				$i++;

				$string = $history['string'];

				echo '
					<div class="media_div">
					<div class="media_arrow_div" id="media_inner_div_' . $i . '_arrow"></div>
					<div class="media_corner_arrow_div" id="media_inner_div_' . $i . '_corner_arrow"></div>
					<div class="media_inner_div" id="media_inner_div_' . $i . '" title="' . hsc($string) . '" onclick="void(0)"><div class="media_left_div"><img src="img/search-24.png?' . global_serial . '" alt="Image"></div><div class="media_right_div"><div class="media_right_upper_div">' . hsc(ucfirst($string)) . '</div></div></div>
					</div>
					<div class="media_options_div" id="media_inner_div_' . $i . '_options">
					<div class="media_options_inner_div">
					<div class="media_search_click_div" title="Search" onclick="void(0)"><img src="img/search-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">' . urlencode($string) . '</span></div>
					</div>
					</div>
				';
			}
		}
	}
}
else
{
	echo '
		<span id="page_title_content_span">Search</span>

		<div id="more_menu_content_div">
		<div class="show_page_click_div" onclick="void(0)">History<span class="hidden_value_span">0|:|search|:|history|:|</span></div>
		</div>

		<div id="search_div">
		<form method="post" action="." id="search_form">
		<div class="input_text_div"><input type="text" id="search_input" value="Search..."></div>
		<div class="hidden_div"><input type="submit" value="Search"></div>
		</form>
		</div>
	';
}

?>

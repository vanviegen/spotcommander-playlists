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

$nowplaying = get_nowplaying();

$artist = $nowplaying['artist'];
$title = $nowplaying['title'];
$album = $nowplaying['album'];
$albumart = $nowplaying['artUrl'];
$uri = $nowplaying['uri'];
$length = $nowplaying['length'];
$year = $nowplaying['year'];
$playbackstatus = $nowplaying['playbackstatus'];

if($spotify_ps == 1 && $playbackstatus == 'Playing')
{
	save_recently_played(sqlite_escape($artist), sqlite_escape($title), sqlite_escape($uri));
}

if(empty($albumart))
{
	$albumart = 'img/no-albumart.png?' . global_serial;
}
else
{
	$albumart = str_replace('/thumb/', '/image/', $albumart);
}

if(empty($year))
{
	$year = 'Unknown';
}

if($playbackstatus != 'Playing')
{
	$artist = 'Unknown';
	$title = 'No music is playing';
	$uri = '';
	$albumart = 'img/no-albumart.png?' . global_serial;	
}


if($spotify_ps == 0)
{
	$artist = 'Unknown';
	$title = 'Spotify is not running';
	$uri = '';
	$albumart = 'img/no-albumart.png?' . global_serial;
}

$nowplaying_more_menu_html = '<div id="recently_played_click_div" onclick="void(0)">Recently played<span class="hidden_value_span"></span></div>';

if($spotify_ps == 1 && $playbackstatus == 'Playing')
{
	if(is_starred($uri) == 'starred')
	{
		$starred_text = 'Unstar';
	}
	else
	{
		$starred_text = 'Star';
	}

	$nowplaying_more_menu_html .= '
		<div class="media_star_click_div" onclick="void(0)"><span class="text_span">' . $starred_text . '</span><span class="hidden_value_span">track|:|' . urlencode($artist) . '|:|' . urlencode($title) . '|:|' . $uri . '</span></div>
		<div class="media_search_click_div" onclick="void(0)">Search artist<span class="hidden_value_span">' . urlencode($artist) . '</span></div>
		<div class="media_lyrics_click_div" onclick="void(0)">Lyrics<span class="hidden_value_span">' . urlencode($artist) . '|:|' . urlencode($title) . '</span></div>
		<div class="media_share_click_div" onclick="void(0)">Share<span class="hidden_value_span">' . urlencode(uri_to_url($uri)) . '</span></div>
	';
}

echo '
	<span id="nowplaying_track_span">' . hsc($title) .'</span><span id="nowplaying_uri_span">' . $uri .'</span>

	<div id="nowplaying_cover_div"></div>

	<div id="nowplaying_more_menu_div">
	<div id="nowplaying_more_menu_arrow_div"></div>
	<div id="nowplaying_more_menu_inner_div">
	' . $nowplaying_more_menu_html . '
	</div>
	</div>

	<div id="nowplaying_power_click_div" title="Launch/quit Spotify" onclick="void(0)"><img src="img/power-32.png?' . global_serial . '" alt="Image"></div>
	<div id="nowplaying_more_menu_click_div" title="More" onclick="void(0)"><img src="img/more-32.png?' . global_serial . '" alt="Image"></div>

	<div id="nowplaying_upper_div">
	<div id="nowplaying_upper_inner_div">
	<div id="nowplaying_albumart_div" title="' . hsc($album) . ' (' . $year . ')' . '" style="background-image: url(\'' . $albumart . '\')" onclick="void(0)"></div>
	</div>
	</div>

	<div id="nowplaying_lower_div">
	<div id="nowplaying_lower_inner_div">

	<div id="nowplaying_track_div" title="' . hsc($title . ' (' . $length . ')') . '" onclick="void(0)">' . hsc($title) . '</div>
	<div id="nowplaying_artist_div" title="' . hsc($artist) . '" onclick="void(0)">' . hsc($artist) . '</div>

	<div id="nowplaying_volume_div">
	<div id="nowplaying_volume_select_div">
	<div id="nowplaying_volume_slider_div"><input id="nowplaying_volume_slider" type="range" min="0" max="100" step="1" value="' . current_volume() . '"></div>
	<div id="nowplaying_volume_buttons_div">
	<div id="nowplaying_volume_buttons_inner_div">
	<div class="media_volume_click_div" title="Mute" onclick="void(0)"><img src="img/volume-mute-32.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">mute</span></div>
	<div class="media_volume_click_div" title="Volume down" onclick="void(0)"><img src="img/volume-down-32.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">down</span></div>
	<div class="media_volume_click_div" title="Volume up" onclick="void(0)"><img src="img/volume-up-32.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">up</span></div>
	</div>	
	</div>
	</div>
	<div id="nowplaying_volume_level_div"><span id="nowplaying_volume_level_span">' . current_volume() . '</span> %</div>
	</div>

	<div id="nowplaying_remote_div">
	<div class="media_toggle_shuffle_repeat_click_div" title="Toggle shuffle" onclick="void(0)"><img src="img/shuffle-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">toggle-shuffle</span></div>
	<div class="media_remote_click_div" title="Previous" onclick="void(0)"><img src="img/previous-48.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">previous</span></div>
	<div class="media_remote_click_div" title="Play/pause" onclick="void(0)"><img src="img/play-64.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">play-pause</span></div>
	<div class="media_remote_click_div" title="Next" onclick="void(0)"><img src="img/next-48.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">next</span></div>
	<div class="media_toggle_shuffle_repeat_click_div" title="Toggle repeat" onclick="void(0)"><img src="img/repeat-24.png?' . global_serial . '" alt="Image"><span class="hidden_value_span">toggle-repeat</span></div>
	</div>

	</div>
	</div>
';

?>

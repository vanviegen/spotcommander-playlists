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

$artist = urldecode($_GET['artist']);
$title = urldecode($_GET['title']);

echo '<span id="page_title_content_span">' . hsc($title) . '</span>';

$search_artist = $artist;
$search_title = $title;

if(stristr($artist, 'feat.'))
{
	$artist = stristr($artist, 'feat.', true);
}
elseif(stristr($artist, 'featuring'))
{
	$artist = stristr($artist, 'featuring', true);
}
elseif(stristr($title, ' con '))
{
	$title = stristr($title, ' con ', true);
}
elseif(stristr($artist, ' & '))
{
	$artist = stristr($artist, ' & ', true);
}

$artist = str_replace('&', 'and', $artist);
$artist = str_replace('$', 's', $artist);
$artist = strip_string(trim($artist));
$artist = str_replace(' - ', '-', $artist);
$artist = str_replace(' ', '-', $artist);

$title_remove = array('acoustic version', 'new album version', 'original album version', 'album version', 'bonus track', 'clean version', 'club mix', 'demo version', 'extended mix', 'extended outro', 'extended version', 'extended', 'explicit version', 'explicit', '(live)', '- live', 'live version', 'lp mix', '(original)', 'original edit', 'original mix edit', 'original version', '(radio)', 'radio edit', 'radio mix', 'remastered version', 're-mastered version', 'remastered digital version', 're-mastered digital version', 'remastered', 'remaster', 'remixed version', 'remix', 'single version', 'studio version', 'version acustica', 'versión acústica', 'vocal edit');
$title = str_ireplace($title_remove, '', $title);

if(stristr($title, 'feat.'))
{
	$title = stristr($title, 'feat.', true);
}
elseif(stristr($title, 'featuring'))
{
	$title = stristr($title, 'featuring', true);
}
elseif(stristr($title, ' con '))
{
	$title = stristr($title, ' con ', true);
}
elseif(stristr($title, '(includes'))
{
	$title = stristr($title, '(includes', true);
}
elseif(stristr($title, '(live at'))
{
	$title = stristr($title, '(live at', true);
}
elseif(stristr($title, '(19'))
{
	$title = stristr($title, '(19', true);
}
elseif(stristr($title, '(20'))
{
	$title = stristr($title, '(20', true);
}
elseif(stristr($title, '- 19'))
{
	$title = stristr($title, '- 19', true);
}
elseif(stristr($title, '- 20'))
{
	$title = stristr($title, '- 20', true);
}

$title = str_replace('&', 'and', $title);
$title = str_replace('$', 's', $title);
$title = strip_string(trim($title));
$title = str_replace(' - ', '-', $title);
$title = str_replace(' ', '-', $title);
$title = rtrim($title, '-');

$url = strtolower('http://www.lyrics.com/' . $title .'-lyrics-' . $artist . '.html');

$db = new SQLite3('db/lyrics.db');

$query = $db->query("SELECT COUNT(*) as count FROM lyrics WHERE md5='" . md5($url) . "'");
$row = $query->fetchArray(SQLITE3_ASSOC);

if($row['count'] == 1)
{
	$query = $db->query("SELECT * FROM lyrics WHERE md5='" . md5($url) . "'");
	$row = $query->fetchArray(SQLITE3_ASSOC);

	$lyrics = $row['lyrics'];
}
else
{
	$url_context = stream_context_create(array('http'=>array('timeout'=>5)));
	@$lyrics = file_get_contents($url, false, $url_context);

	if(empty($lyrics))
	{
		$no_match = true;
	}
	else
	{
		$lyrics = preg_match('"<div id=\"lyric_space\">(.*?)</div>"si', $lyrics, $match);

		if($match)
		{
			$lyrics = $match[1];

			if(stristr($lyrics, 'we do not have the lyric for this song') || stristr($lyrics, 'lyrics are currently unavailable') || stristr($lyrics, 'your name will be printed as part of the credit'))
			{
				$no_match = true;
			}
			else
			{
				if(strstr($lyrics, 'Ã') && strstr($lyrics, '©'))
				{
					$lyrics = utf8_decode($lyrics);
				}

				$lyrics = trim(str_replace('<br />', '<br>', $lyrics));

				if(strstr($lyrics, '<br>---'))
				{
					$lyrics = strstr($lyrics, '<br>---', true);
				}

				$db->exec("INSERT INTO lyrics (md5,lyrics) VALUES ('" . md5($url) . "','" . sqlite_escape($lyrics) . "')");
			}
		}
		else
		{
			$no_match = true;	
		}
	}
}

if(isset($no_match))
{
	echo '
		<div id="more_menu_content_div">
		<div class="open_window_click_div" onclick="void(0)">Search the web<span class="hidden_value_span">http://www.google.com/search?q=' . urlencode($search_artist . ' ' . $search_title . ' Lyrics') . '</span></div>
		</div>

		<div id="page_message_div">No match or timeout reached</div>
	';
}
else
{
	echo '<div id="lyrics_div">' . $lyrics . '</div>';
}

?>

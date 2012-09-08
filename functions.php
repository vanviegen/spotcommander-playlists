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

// Remote control

function remote($action)
{
	global $spotify_ps;

	if($spotify_ps == 1 || $spotify_ps == 0 && $action == 'spotify-launch')
	{
		file_write('sh/spotcommander-remote.txt', $action);
	}
}

function current_volume()
{
	$volume = trim(file_get_contents('sh/volume.txt'));

	if(is_numeric($volume))
	{
		$volume = intval($volume);
		$volume = $volume / 65537;
		$volume = $volume * 100;
		$volume = intval(round($volume));

		if($volume < 0 || $volume > 100)
		{
			$volume = 50;
		}
	}
	else
	{
		$volume = 50;
	}

	return($volume);
}

function volume_before_mute()
{
	$volume = trim(file_get_contents('sh/volume-before-mute.txt'));
	return($volume);
}

// Now playing

function get_nowplaying()
{
	sleep(1);

	remote('metadata-get');

	sleep(1);

	$artist = trim(file_get_contents('sh/metadata-artist.txt'));
	$title = trim(file_get_contents('sh/metadata-title.txt'));
	$album = trim(file_get_contents('sh/metadata-album.txt'));
	$albumart = trim(file_get_contents('sh/metadata-albumart.txt'));
	$uri = trim(file_get_contents('sh/metadata-uri.txt'));
	$length = trim(file_get_contents('sh/metadata-length.txt')) / 1000000;
	$year = trim(file_get_contents('sh/metadata-year.txt'));
	$playbackstatus = trim(file_get_contents('sh/playbackstatus.txt'));

	$nowplaying['artist'] = $artist;
	$nowplaying['title'] = $title;
	$nowplaying['album'] = $album;
	$nowplaying['albumart'] = $albumart;
	$nowplaying['uri'] = $uri;
	$nowplaying['length'] = floor($length / 60) . ':' . sprintf("%02s", $length % 60);
	$nowplaying['year'] = substr($year, 0, 4);
	$nowplaying['playbackstatus'] = $playbackstatus;

	return($nowplaying);
}

// Recently played

function save_recently_played($artist, $title, $uri)
{
	$db = new SQLite3('db/recently-played.db');

	$query = $db->query("SELECT COUNT(*) as count FROM recently_played WHERE uri='$uri'");
	$row = $query->fetchArray(SQLITE3_ASSOC);

	if($row['count'] > 0)
	{
		$db->exec("DELETE FROM recently_played WHERE uri = '$uri'");
	}

	$query = $db->query("SELECT COUNT(*) as count FROM recently_played");
	$row = $query->fetchArray(SQLITE3_ASSOC);

	if($row['count'] == 10)
	{
		$db->exec("DELETE FROM recently_played WHERE id = (SELECT id FROM recently_played ORDER BY id LIMIT 1)");
	}
	elseif($row['count'] > 10)
	{
		$db->exec("DELETE FROM recently_played");
	}

	$db->exec("INSERT INTO recently_played (artist,title,uri) VALUES ('$artist','$title','$uri')");
}

function clear_recently_played()
{
	$db = new SQLite3('db/recently-played.db');
	$db->exec("DELETE FROM recently_played");
}

// Playlists

function save_playlist($uris)
{
	$uris = trim($uris);
	$uris = preg_replace('/\s+/', '', $uris);
	$uris = explode(',', $uris);

	foreach($uris as $uri)
	{
		$uri = url_to_uri($uri);

		if(!playlist_exists($uri))
		{
			$get_context = stream_context_create(array('http'=>array('timeout'=>5)));
			@$get = file_get_contents('https://embed.spotify.com/?uri=' . $uri, false, $get_context);

			if(empty($get))
			{
				$no_match = true;
			}
			else
			{
				preg_match_all("'<title>(.*?)</title>'si", $get, $name);

				if($name[1])
				{
					$name = hscd(strstr($name[1][0], ' by', true));
				}
				else
				{
					$no_match = true;
				}
			}

			if(isset($no_match))
			{
				$name = 'Unknown';
			}

			$db = new SQLite3('db/playlists.db');

			$name = sqlite_escape($name);

			$query = $db->query("SELECT COUNT(*) as count FROM playlists WHERE name='$name' AND uri='$uri' COLLATE NOCASE");
			$row = $query->fetchArray(SQLITE3_ASSOC);

			if($row['count'] == 0)
			{
				$db->exec("INSERT INTO playlists (name,uri) VALUES ('$name','$uri')");
			}
		}
	}

	if(isset($no_match))
	{
		return(0);
	}
	else
	{
		return(1);
	}
}

function playlist_exists($uri)
{
	$db = new SQLite3('db/playlists.db');

	$query = $db->query("SELECT COUNT(*) as count FROM playlists WHERE uri='$uri' COLLATE NOCASE");
	$row = $query->fetchArray(SQLITE3_ASSOC);

	if($row['count'] != 0)
	{
		return(true);
	}
	else
	{
		return(false);
	}
}

function remove_playlist($id)
{
	$db = new SQLite3('db/playlists.db');
	$db->exec("DELETE FROM playlists WHERE id='$id'");
}

// Starred

function star($type, $artist, $title, $uri)
{
	$db = new SQLite3('db/starred.db');

	$query = $db->query("SELECT COUNT(*) as count FROM starred WHERE uri='$uri' COLLATE NOCASE");
	$row = $query->fetchArray(SQLITE3_ASSOC);

	if($row['count'] == 0)
	{
		$db->exec("INSERT INTO starred (type,artist,title,uri) VALUES ('$type','$artist','$title','$uri')");
	}
}

function unstar($uri)
{
	$db = new SQLite3('db/starred.db');
	$db->exec("DELETE FROM starred WHERE uri='$uri'");
}

function is_starred($uri)
{
	$db = new SQLite3('db/starred.db');

	$query = $db->query("SELECT COUNT(*) as count FROM starred WHERE uri='$uri' COLLATE NOCASE");
	$row = $query->fetchArray(SQLITE3_ASSOC);

	if($row['count'] == 1)
	{
		return('starred');
	}
	else
	{
		return('star');
	}
}

// Search

function save_search_history($string)
{
	$db = new SQLite3('db/search-history.db');

	$query = $db->query("SELECT COUNT(*) as count FROM search_history WHERE string='$string' COLLATE NOCASE");
	$row = $query->fetchArray(SQLITE3_ASSOC);

	if($row['count'] > 0)
	{
		$db->exec("DELETE FROM search_history WHERE string='$string' COLLATE NOCASE");
	}

	$query = $db->query("SELECT COUNT(*) as count FROM search_history");
	$row = $query->fetchArray(SQLITE3_ASSOC);

	if($row['count'] == 10)
	{
		$db->exec("DELETE FROM search_history WHERE id = (SELECT id FROM search_history ORDER BY id LIMIT 1)");
	}
	elseif($row['count'] > 10)
	{
		$db->exec("DELETE FROM search_history");
	}

	$db->exec("INSERT INTO search_history (string) VALUES ('$string')");
}

function clear_search_history()
{
	$db = new SQLite3('db/search-history.db');
	$db->exec("DELETE FROM search_history");
}

// Files

function file_write($file, $content)
{
	$fwrite = fopen($file, 'w');
	fwrite($fwrite, $content);
	fclose($fwrite);
}

// Strings

function true_or_false($var)
{
	if($var)
	{
		return('true');
	}
	else
	{
		return('false');
	}
}

function strip_string($string)
{
	$string = preg_replace('/[^a-zA-Z0-9-\s]/', '', $string);
	return($string);
}

function uri_to_url($uri)
{
	if(strstr($uri, 'spotify:track:'))
	{
		$uri = str_replace('spotify:track:', 'http://open.spotify.com/track/', $uri);
	}
	if(strstr($uri, 'spotify:local:'))
	{
		$uri = 'local';
	}
	if(strstr($uri, 'spotify:album:'))
	{
		$uri = str_replace('spotify:album:', 'http://open.spotify.com/album/', $uri);
	}
	elseif(strstr($uri, 'spotify:user:'))
	{
		$uri = explode(':', $uri);
		$uri = 'http://open.spotify.com/user/' . $uri[2] . '/playlist/' . $uri[4];
	}

	return($uri);
}

function url_to_uri($url)
{
	if(strstr($url, 'http://open.spotify.com/user/'))
	{
		$url = str_replace('http://open.spotify.com/user/', '', $url);
		$url = str_replace('/', ':', $url);
		$url = 'spotify:user:' . $url;
	}

	return($url);
}

function hsc($string)
{
	$string = htmlspecialchars($string, ENT_QUOTES);
	return($string);
}

function hscd($string)
{
	$string = htmlspecialchars_decode($string, ENT_QUOTES);
	return($string);	
}

function sqlite_escape($string)
{
	return(SQLite3::escapeString($string));
}

?>

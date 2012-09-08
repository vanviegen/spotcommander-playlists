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

function remote()
{
	global $spotify_ps;

	if($spotify_ps == 1 || $action == 'spotify-launch')
	{
		$spath = "unix://".getcwd()."/sh/spotcommander.socket";
		$conn = stream_socket_client($spath,$errno,$errstr);
		if (!$conn) {
			echo "<h2>Error connecting to spotcommander daemon</h2>$errstr<br />$spath<br />";
			exit;
		}
		fwrite($conn, json_encode(func_get_args())."\n");
		$res = '';
		while(!feof($conn))
			$res .= fread($conn,4096);
		fclose($conn);
		return json_decode($res,true);
	}
}

function current_volume()
{
	return remote('volume-get');
}

function volume_before_mute()
{
	$volume = trim(file_get_contents('sh/volume-before-mute.txt'));
	return($volume);
}

// Now playing

function get_nowplaying()
{
	$res = remote('metadata-get');

	$length = $res['length']/1000000;
	$res['length'] = floor($length / 60) . ':' . sprintf("%02s", $length % 60);
	$res['year'] = substr($res['year'], 0, 4);
	return($res);
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

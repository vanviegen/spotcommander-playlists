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

// Configuration

require_once('config.php');

// About

define('global_name', 'SpotCommander');
define('global_version', '7.3');
define('global_serial', '641');
define('global_website', 'http://www.olejon.net/code/spotcommander/');

// Server user agent

ini_set('user_agent', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:14.0) Gecko/20100101 Firefox/14.0.1');

// Client user agent

$ua = $_SERVER['HTTP_USER_AGENT'];

// Send system information?

if(defined('config_send_system_information') && config_send_system_information)
{
	$uname_send = exec('uname -mrsv');
	$ua_send = $ua;
}
else
{
	$uname_send = 'Disabled';
	$ua_send = 'Disabled';
}

// Functions

require_once('functions.php');

// Errors

if(isset($_GET['check_for_errors']))
{
	if(!defined('config_path') || !defined('config_nowplaying_update_interval') || !defined('config_facebook_like_button') || !defined('config_check_for_updates') || !defined('config_send_system_information') || !defined('config_keyboard_shortcuts') || !is_string(config_path) || !is_int(config_nowplaying_update_interval) || !is_bool(config_facebook_like_button) || !is_bool(config_check_for_updates) || !is_bool(config_send_system_information) || !is_bool(config_keyboard_shortcuts))
	{
		echo 1;
	}
	elseif(!file_exists(config_path . '/sh/spotcommander-ps.sh'))
	{
		echo 2;
	}
	elseif(!file_exists(config_path . '/sh/spotify-ps.sh'))
	{
		echo 3;
	}
	elseif(!is_writeable('sh/spotcommander-remote.txt') || !is_writeable('sh/uri.txt') || !is_writeable('db/starred.db') || !is_writeable('db/playlists.db') || !is_writeable('db/lyrics.db') || !is_writeable('db/recently-played.db') || !is_writeable('db/search-history.db') || !is_writeable('lib/cache'))
	{
		echo 4;
	}
	else
	{
		echo 0;
	}
}

// Variables

if(isset($_GET['variables']))
{
	echo global_name . '|:|' . global_version . '|:|' . global_serial . '|:|' . global_website . '|:|' . config_nowplaying_update_interval . '|:|' . true_or_false(config_facebook_like_button) . '|:|' . true_or_false(config_check_for_updates) . '|:|' . true_or_false(config_keyboard_shortcuts);
}

// Updates

if(isset($_GET['check_for_updates']))
{
	$url = global_website . 'latest-version.php?version=' . urlencode(global_version) . '&uname=' . urlencode($uname_send) . '&ua=' . urlencode($ua_send);
	$url_context = stream_context_create(array('http'=>array('timeout'=>5)));
	@$latest_version = file_get_contents($url, false, $url_context);
	$latest_version = trim($latest_version);

	if(empty($latest_version) || !is_numeric($latest_version))
	{
		echo 0;
	}
	else
	{
		echo $latest_version;
	}
}	

// Remote control

$spotify_ps = intval(exec(config_path . '/sh/spotify-ps.sh'));

if(isset($_GET['spotify_ps']))
{
	echo $spotify_ps;
}

if(isset($_POST['remote']))
{
	$remote = $_POST['remote'];
}

if(isset($remote))
{
	if($remote == 'on-off')
	{
		if($spotify_ps == 0)
		{
			remote('spotify-launch');
		}
		elseif($spotify_ps == 1)
		{
			remote('spotify-quit');
		}
	}
	elseif($remote == 'play-pause')
	{
		remote('play-pause');
	}
	elseif($remote == 'next')
	{
		remote('next');
	}
	elseif($remote == 'previous')
	{
		remote('previous');
	}
	elseif($remote == 'up')
	{
		remote('up');
	}
	elseif($remote == 'down')
	{
		remote('down');
	}
	elseif($remote == 'tab')
	{
		remote('tab');
	}
	elseif($remote == 'return')
	{
		remote('return');
	}
	elseif($remote == 'toggle-shuffle')
	{
		remote('toggle-shuffle');
	}
	elseif($remote == 'toggle-repeat')
	{
		remote('toggle-repeat');
	}
	elseif($remote == 'volume')
	{
		if(isset($_POST['volume']))
		{
			$volume = $_POST['volume'];
			$current_volume = current_volume();

			if(is_numeric($volume))
			{
				$volume = intval($volume);

				if($volume == 0)
				{
					file_write('sh/volume-before-mute.txt', $current_volume);
				}
			}
			elseif($volume == 'up')
			{
				$volume = intval($current_volume + 10);
			}
			elseif($volume == 'down')
			{
				$volume = intval($current_volume - 10);
			}
			elseif($volume == 'mute')
			{
				if($current_volume == 0)
				{
					$volume = intval(volume_before_mute());
				}
				else
				{
					$volume = 0;
					file_write('sh/volume-before-mute.txt', $current_volume);
				}
			}

			if(is_int($volume))
			{
				if($volume < 0)
				{
					$volume = 0;
				}
				elseif($volume > 100)
				{
					$volume = 100;
				}

				remote('volume', round($volume));
				echo $volume;
			}
		}
	}
	elseif($remote == 'play-uri')
	{
		remote('play-uri', $_POST['uri']);
	}
	elseif($remote == 'focus')
	{
		remote('focus');
	}
}

?>

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

// Remote

function spotifyPs()
{
	if($('img#toggle_power_loading_img').length == 0)
	{
		$.get('main.php?spotify_ps', function(data)
		{
			var spotify_ps = $('input:hidden#spotify_ps_input').val();

			if(data != spotify_ps)
			{
				refreshNowplaying();
				$('input:hidden#spotify_ps_input').val(data);
			}
		});
	}
}

function togglePower()
{
	if($('img#toggle_power_loading_img').length == 0)
	{
		nowplaying_xhr.abort();

		remoteControl('on-off');

		$('div#bottombar_nowplaying_div').html('<img src="img/loading-grey.gif?'+global_serial+'" id="toggle_power_loading_img" alt="Image">');

		var spotify_ps = $('input:hidden#spotify_ps_input').val();

		if(spotify_ps == '0')
		{
			$('input:hidden#spotify_ps_input').val('1');
		}
		else if(spotify_ps == '1')
		{
			$('input:hidden#spotify_ps_input').val('0');
		}

		refreshNowplaying();
	}
}

function remoteControl(action)
{
	if($('img#toggle_power_loading_img').length == 0)
	{
		$.post('main.php', { remote: action }, function()
		{
			if(action == 'play-pause' || action == 'next' || action == 'previous')
			{
				refreshNowplaying();
			}	
		});
	}
}

function changeVolume(action)
{
	if(action == 'slider')
	{
		var volume = parseInt($('input#nowplaying_volume_slider').val());
	}
	else if(action == 'up')
	{
		var volume = 'up';
	}
	else if(action == 'down')
	{
		var volume = 'down';
	}
	else if(action == 'mute')
	{
		var volume = 'mute';
	}

	$.post('main.php',{ remote: 'volume', volume: volume }, function(data)
	{
		$('span#nowplaying_volume_level_span').html(data);
		$('input#nowplaying_volume_slider').val(data);	
	});
}

function toggleShuffleRepeat(action)
{
	var cookie_name = 'hide_shuffle_repeat_information_'+global_version;
	var cookie_days = 365;
	var cookie = getCookie(cookie_name);

	if(typeof cookie == 'undefined')
	{
		showMessageDiv('Shuffle & repeat', 'Shuffle & repeat can be toggled, but it is not possible to get the current status. Toggling requires the Spotify window to have focus. It will try to focus automatically.', cookie_name, cookie_days, '', '');
	}

	remoteControl(action);
}

function playUri(uri)
{
	if(typeof playrandomuri_timeout != 'undefined')
	{
		clearTimeout(playrandomuri_timeout);
	}

	$.get('main.php?spotify_ps', function(data)
	{
		if(data == 1)
		{
			$.post('main.php', { remote: 'play-uri', uri: uri }, function()
			{
				refreshNowplaying();
			});
		}
		else
		{
			showMessageDiv('Error', 'Spotify is not running. Open now playing + remote to launch it.', '', '', '', '');
		}
	});
}

function playRandomUri(uri)
{
	$.get('main.php?spotify_ps', function(data)
	{
		if(data == 1)
		{
			var cookie_name = 'hide_play_random_uri_information_'+global_version;
			var cookie_days = 365;
			var cookie = getCookie(cookie_name);

			if(typeof cookie == 'undefined')
			{
				showMessageDiv('Play random', 'Play random toggles shuffle off/on, plays the playlist and skips one track to ensure random playback. It takes a few seconds. Shuffle must already be enabled.', cookie_name, cookie_days, '', '');
			}

			remoteControl('toggle-shuffle');

			setTimeout(function()
			{
				remoteControl('toggle-shuffle');
			}, 2000);

			setTimeout(function()
			{
				playUri(uri);

				playrandomuri_timeout = setTimeout(function()
				{
					remoteControl('next');
				}, 2000);
			}, 3000);
			
		}
		else
		{
			showMessageDiv('Error', 'Spotify is not running. Open now playing + remote to launch it.', '', '', '', '');
		}
	});
}

// Pages

function hashChange()
{
	var hash = window.location.hash.slice(1);

	if(hash == '')
	{
		pageLoading(0);
		showPage('playlists', '', '');
	}
	else
	{
		var array = hash.split('/');

		if(array[0] == 1)
		{
			pageLoading(1);
		}
		else
		{
			pageLoading(0);
		}

		showPage(array[1], array[2], array[3]);
	}
}

function changePage(animate, page, subpage, args)
{
	var time = new Date().getTime();
	var time = parseInt(time / 100);
	var url  = '#'+animate+'/'+page+'/'+subpage+'/'+args+'/'+time;

	window.location.href=url;

	if(ua_standalone)
	{
		var cookie_name = 'current_page_'+global_version;
		var cookie_days = 1;

		setCookie(cookie_name, animate+'|:|'+page+'|:|'+subpage+'|:|'+args, cookie_days);
	}
}

function showPage(page, subpage, arg)
{
	hideNowplayingMoreMenu();
	hideNowplaying();

	page_xhr = $.get(page+'.php?'+subpage+'&'+arg, function(data)
	{
		divHide('div#page_div');
		$('div#page_div').html(data);
		divFadeIn('div#page_div');
		pageLoaded(page, subpage);
	}).error(function()
	{
		$('div#page_div').html('<div id="page_message_div" title="Error">Request failed. Try again. Make sure you are connected.</div>');
		divFadeIn('div#page_div');
		pageLoaded('', '');
	});
}

function pageLoading(animate)
{
	abortPageLoad();

	scrollToTop();
	hideMenu();

	if(animate == 1)
	{
		$('div#more_menu_click_div').css('visibility', 'hidden');
		$('div#page_title_div').html('Wait...');
		$('div#page_div').html('<div id="page_loading_div"><img src="img/loading-green.gif?'+global_serial+'" alt="Image"></div>');
	}
	else
	{
		divHide('div#page_div');
	}
}

function pageLoaded(page, subpage)
{
	// All
	checkForUpdates('auto');

	hideMoreMenu();

	if(!ua_standalone)
	{
		scrollToTop();
	}

	checkForMessages();

	if($('span#page_title_content_span').length)
	{
		var title = $('span#page_title_content_span').html();
		$('div#page_title_div').html(title).attr('title', title);
	}
	else if($('div#page_message_div').length && $('div#page_message_div').attr('title') != '')
	{
		$('div#page_title_div').html($('div#page_message_div').attr('title'));
	}

	if($('div#more_menu_content_div').length)
	{
		$('div#more_menu_click_div').css('visibility', 'visible');
		$('div#more_menu_inner_div').html($('div#more_menu_content_div').html());
		$('div#more_menu_content_div').empty();
	}
	else
	{
		$('div#more_menu_click_div').css('visibility', 'hidden');
	}

	// Text fields
	if($('div.input_text_div').length)
	{
		if(ua_touch)
		{
			setTimeout(function()
			{
				$('div.input_text_div').css('visibility', 'visible');

				if(ua_supported_csstransitions && ua_supported_csstransforms3d)
				{
					$('div.input_text_div').addClass('input_text_div_scale');

					setTimeout(function()
					{
						$('div.input_text_div').addClass('show_input_text_div_animation');
					}, 25);
				}
				else
				{
					divFadeIn('div.input_text_div');
				}
			}, 250);
		}
		else
		{
			$('div.input_text_div').css('visibility', 'visible').css('opacity', '1');
		}
	}

	// Individual
	if(page == 'search' && subpage == '')
	{
		focusInput('input:text#search_input');	
	}
	else if(page == 'playlists' && subpage == 'how_to_add')
	{
		if(ua_android && ua_standalone)
		{
			$('div#how_to_add_playlists_android_app_div').show();
		}
	}
}

function abortPageLoad()
{
	if(typeof page_xhr != 'undefined')
	{
		page_xhr.abort();
	}
}

function goBack()
{
	if(!isDisplayed('div#transparent_cover_div') && !isDisplayed('div#black_cover_div') && !isVisible('div#nowplaying_div'))
	{
		history.go(-1);
	}
}

// Menus

function toggleMenu()
{
	if(!isVisible('div#menu_div'))
	{
		showMenu();
	}
	else
	{
		hideMenu();
	}
}

function showMenu()
{
	if(!isVisible('div#menu_div') && !isDisplayed('div#transparent_cover_div') && !isDisplayed('div#black_cover_div') && !isVisible('div#nowplaying_div'))
	{
		hideMoreMenu();
		$('div#transparent_cover_div').show();
		$('div#menu_div').css('visibility', 'visible');

		if(ua_supported_csstransitions && ua_supported_csstransforms3d)
		{
			$('div#menu_div').addClass('show_menu_animation').on(events_transition_end, function()
			{
				$('div#menu_div').off(events_transition_end).css('left', '0').removeClass('show_menu_animation');
			});
		}
		else
		{
			$('div#menu_div').animate({ left: '0' }, 250, 'easeOutExpo');
		}
	}
}

function hideMenu()
{
	if(isVisible('div#menu_div'))
	{
		if(ua_supported_csstransitions && ua_supported_csstransforms3d)
		{
			$('div#menu_div').addClass('hide_menu_animation').on(events_transition_end, function()
			{
				$('div#menu_div').off(events_transition_end).css('visibility', 'hidden').css('left', menu_css_left).removeClass('hide_menu_animation');
				$('div#transparent_cover_div').hide();
			});
		}
		else
		{
			$('div#menu_div').animate({ left: menu_css_left }, 250, 'easeOutExpo', function()
			{
				$('div#menu_div').css('visibility', 'hidden');
				$('div#transparent_cover_div').hide();
			});
		}
	}
}

function showMoreMenu()
{
	if(!isDisplayed('div#more_menu_div'))
	{
		$('div#transparent_cover_div').show();
		$('div#more_menu_div').show();

		if(ua_supported_csstransitions && ua_supported_csstransforms3d)
		{
			$('div#more_menu_div').addClass('more_menu_scale');

			setTimeout(function()
			{		
				$('div#more_menu_div').addClass('show_more_menu_animation');
			}, 25);
		}
		else
		{
			divFadeIn('div#more_menu_div');
		}
	}
}

function hideMoreMenu()
{
	if(isDisplayed('div#more_menu_div'))
	{
		$('div#more_menu_div').hide();

		if(ua_supported_csstransitions && ua_supported_csstransforms3d)
		{
			$('div#more_menu_div').removeClass('more_menu_scale show_more_menu_animation');
		}
		else if(ua_supported_csstransitions)
		{
			divHide('div#more_menu_div');
		}
		else
		{
			$('div#more_menu_div').css('opacity', '');
		}

		$('div#transparent_cover_div').hide();
	}
}

function showNowplayingMoreMenu()
{
	if(!isDisplayed('div#nowplaying_more_menu_div'))
	{
		$('div#nowplaying_cover_div').show();
		$('div#nowplaying_more_menu_div').show();

		if(ua_supported_csstransitions && ua_supported_csstransforms3d)
		{
			$('div#nowplaying_more_menu_div').addClass('more_menu_scale');

			setTimeout(function()
			{		
				$('div#nowplaying_more_menu_div').addClass('show_more_menu_animation');
			}, 25);
		}
		else
		{
			divFadeIn('div#nowplaying_more_menu_div');
		}
	}
}

function hideNowplayingMoreMenu()
{
	if(isDisplayed('div#nowplaying_more_menu_div'))
	{
		$('div#nowplaying_more_menu_div').hide();

		if(ua_supported_csstransitions && ua_supported_csstransforms3d)
		{
			$('div#nowplaying_more_menu_div').removeClass('more_menu_scale show_more_menu_animation');
		}
		else
		{
			divHide('div#nowplaying_more_menu_div');
		}

		$('div#nowplaying_cover_div').hide();
	}
}

// Now playing

function toggleNowplaying()
{
	spotifyPs();

	if(!isVisible('div#nowplaying_div'))
	{
		showNowplaying();
	}
	else
	{
		hideNowplaying();
	}
}

function showNowplaying()
{
	if(!isVisible('div#nowplaying_div') && !isDisplayed('div#transparent_cover_div') && !isVisible('div#menu_div'))
	{
		nowplaying_button_src = $('img', 'div#nowplaying_remote_click_div').attr('src');

		if(ua_android && ua_standalone && window.Android)
		{
			Android.JSsetSetting('NOWPLAYING_DIV', 'visible');
		}

		if(ua_supported_csstransitions && ua_supported_csstransforms3d)
		{
			$('div#nowplaying_div').css('visibility', 'visible').addClass('show_nowplaying_animation').on(events_transition_end, function()
			{
				$('div#nowplaying_div').off(events_transition_end).css('bottom', '0').removeClass('show_nowplaying_animation');
			});
		}
		else
		{
			$('div#nowplaying_div').css('visibility', 'visible').animate({ bottom: '0' }, 500, 'easeOutExpo');
		}

		$('img', 'div#nowplaying_remote_click_div').attr('src', 'img/nowplaying-details-close-32.png?'+global_serial);
	}
}

function hideNowplaying()
{
	if(isVisible('div#nowplaying_div') && !isDisplayed('div#nowplaying_cover_div'))
	{
		if(ua_android && ua_standalone && window.Android)
		{
			Android.JSsetSetting('NOWPLAYING_DIV', 'hidden');
		}

		if(ua_supported_csstransitions && ua_supported_csstransforms3d)
		{
			$('div#nowplaying_div').addClass('hide_nowplaying_animation').on(events_transition_end, function()
			{
				$('div#nowplaying_div').off(events_transition_end).css('visibility', 'hidden').css('bottom', nowplaying_css_bottom).removeClass('hide_nowplaying_animation');
			});
		}
		else
		{
			$('div#nowplaying_div').animate({ bottom: nowplaying_css_bottom }, 500, 'easeOutExpo', function() { $('div#nowplaying_div').css('visibility', 'hidden'); });
		}

		$('img', 'div#nowplaying_remote_click_div').attr('src', nowplaying_button_src);
	}
}

var lastData;
function refreshNowplaying(mode)
{
	autoRefreshNowplaying('reset');

	if (mode!='auto') {
		hideNowplayingMoreMenu();

		if(typeof nowplaying_xhr != 'undefined')
		{
			nowplaying_xhr.abort();
		}

		$('div#nowplaying_more_menu_click_div').css('pointer-events', 'none').css('opacity', '0.5');

		$('div#bottombar_nowplaying_div').html('<img src="img/loading-grey.gif?'+global_serial+'" alt="Image">');

		if(ua_supported_csstransitions && ua_supported_csstransforms3d)
		{
			$('div#nowplaying_albumart_div').addClass('albumart_slideout_animation');
		}
		else
		{
			$('div#nowplaying_albumart_div').animate({ left: '-'+window_width+'px' }, 500, 'easeOutExpo');
		}
	}

	nowplaying_xhr = $.get('nowplaying.php', function(data)
	{
		if (lastData==data && mode=='auto') return;
		lastData = data;
		$('div#nowplaying_div').html(data);

		if(ua_supported_inputtype_range)
		{
			$('div#nowplaying_volume_slider_div').show();
		}
		else
		{
			$('div#nowplaying_volume_buttons_div').show();
		}

		if(ua_supported_csstransitions && ua_supported_csstransforms3d)
		{
			$('div#nowplaying_albumart_div').addClass('albumart_changeside_transform');
		
			setTimeout(function()
			{
				$('div#nowplaying_albumart_div').addClass('albumart_slidein_animation').on(events_transition_end, function()
				{
					$('div#nowplaying_albumart_div').off(events_transition_end).removeClass('albumart_slideout_animation albumart_changeside_transform albumart_slidein_animation');
				});
			}, 25);
		}
		else
		{
			var changeside = window_width * 2;
			$('div#nowplaying_albumart_div').css('left', changeside+'px').animate({ left: '0' }, 500, 'easeOutExpo');
		}

		var track = $('span#nowplaying_track_span').html();

		divHide('div#bottombar_nowplaying_div');
		$('div#bottombar_nowplaying_div').attr('title', track).html(track);
		divFadeIn('div#bottombar_nowplaying_div');

		getRecentlyPlayed();

	}).error(function()
	{
		$('div#bottombar_nowplaying_div').html('Connection failed');
	});
}

function autoRefreshNowplaying(action)
{
	if(action == 'start' && config_nowplaying_update_interval >= 1)
	{
		var time = new Date().getTime();
		setCookie('nowplaying_last_update', time);

		nowplaying_interval = setInterval(function()
		{
			var time = new Date().getTime();
			var cookie_time = parseInt(getCookie('nowplaying_last_update'));

			if(time - cookie_time > config_nowplaying_update_interval * 1000 && $('img#toggle_power_loading_img').length == 0)
			{
				nowplaying_timeout = setTimeout(function()
				{
					refreshNowplaying('auto');
				}, 5000);

				var time = new Date().getTime();
				setCookie('nowplaying_last_update', time);
			}
		}, 2000);
	}
	else if(action == 'reset' && typeof nowplaying_interval != 'undefined')
	{
		clearInterval(nowplaying_interval);

		if(typeof nowplaying_timeout != 'undefined')
		{
			clearTimeout(nowplaying_timeout);
		}

		setTimeout(function()
		{
			autoRefreshNowplaying('start');
		}, 250);
	}
}

// Recently played

function getRecentlyPlayed()
{
	if($('span#page_title_content_span').html() == 'Recently played')
	{
		$.get('recently-played.php', function(data) { $('div#page_div').html(data); });
	}
}

function clearRecentlyPlayed()
{
	$.get('recently-played.php?clear', function()
	{
		getRecentlyPlayed();
	});
}

// Playlists

function getPlaylists()
{
	$.get('playlists.php', function(data) { $('div#page_div').html(data); });
}

function savePlaylist(uris)
{
	var array = uris.split(',');
	var count = array.length;

	var i = 0;

	while(i < count)
	{
		if(!hasCharacters(array[i], 'spotify:user:|:|http://open.spotify.com/user/') || !hasCharacters(array[i], 'playlist'))
		{
			var invalid = true;
		}

		i++;
	}

	if(typeof invalid != 'undefined')
	{
		$('div#input_below_div').html('One or more invalid URIs/URLs!');
		$('input:text#add_playlist_uri_input').css('border-color', '#669900');
		$('input:text#add_playlist_uri_input').css('border-color', '#cc0000');
		focusInput('input:text#add_playlist_uri_input');
	}
	else
	{
		pageLoading(1);

		page_xhr = $.post('playlists.php?save', { uris: uris }, function(data)
		{
			changePage('0', 'playlists', '', '');

			if(data == 0)
			{
				showMessageDiv('Warning', 'Couldn\'t get the name for one or more of the playlists. They may be invalid. Try again.', '', '', '', '');
			}
		});
	}
}

function removePlaylist(id)
{
	$.post('playlists.php?remove', { id: id }, function()
	{
		getPlaylists();
	});
}

// Starred

function getStarred()
{
	if($('span#page_title_content_span').html() == 'Starred')
	{
		$.get('starred.php', function(data) { $('div#page_div').html(data); });
	}
}

function star(type, artist, title, uri)
{
	$.post('starred.php?star', { type: type, artist: artist, title: title, uri: uri }, function()
	{
		getStarred();
	});
}

function unstar(uri)
{
	$.post('starred.php?unstar', { uri: uri }, function()
	{
		getStarred();
	});
}

// Search

function searchSpotify(string)
{
	if(string != '')
	{
		focusInput();
		changePage('1', 'search', 'search', 'string='+encodeURIComponent(string));
	}
	else
	{
		focusInput('input:text#search_input');
		$('input:text#search_input').css('border-color', '#cc0000');
	}
}

function browseAlbum(uri)
{
	if(uri != '')
	{
		if(isLocalFile(uri))
		{
			showMessageDiv('Error', 'Not possible for local files.', '', '', '', '');
		}
		else
		{
			changePage('1', 'search', 'browse_album', 'uri='+uri);
		}
	}
}

function getSearchHistory()
{
	$.get('search.php?history', function(data) { $('div#page_div').html(data); });
}

function clearSearchHistory()
{
	$.get('search.php?history&clear', function()
	{
		getSearchHistory();
	});
}

// Window load

$(window).load(function()
{
	$.get('main.php?variables', function(system_variables)
	{
		setSystemVariables(system_variables);
		setCss();

		// Settings
		$.ajaxSetup({ cache: false });

		// Orientation
		if(window.orientation == 90 & window_height < 400 || window.orientation == -90 && window_height < 400)
		{
			showMessageDiv('Warning', 'You should rotate your screen to portrait mode', '', '', '', '');
		}

		$(window).on('orientationchange', function()
		{
			hideMenu();
			hideMoreMenu();
			hideNowplaying();
			hideNowplayingMoreMenu();

			setTimeout(function()
			{
				setCss();

				if(window.orientation == -90 && window_height < 400 || window.orientation == 90 && window_height < 400)
				{
					showMessageDiv('Warning', 'You should rotate your screen to portrait mode', '', '', '', '');
				}
			}, 750);
		});

		// Resize
		$(window).on('resize', function()
		{
			if(!ua_touch)
			{
				hideNowplaying();
				setTimeout(function() { setCss(); }, 750);
			}
		});

		// Movement
		if(ua_touch)
		{
			$(document).on(onclick_event, function(e)
			{
				touched = true;
				moved = false;
				touch_gesture_finished = false;

				click_start_x = e.originalEvent.touches[0].pageX;
				click_start_y = e.originalEvent.touches[0].pageY;
			});

			$(document).on(onmove_event, function(e)
			{
				if(typeof click_start_x != 'undefined' && typeof click_start_y != 'undefined')
				{
					click_end_x = e.originalEvent.touches[0].pageX;
					click_end_y = e.originalEvent.touches[0].pageY;

					click_moved_x = click_end_x - click_start_x;
					click_moved_y = click_end_y - click_start_y;

					if(isDisplayed('div#transparent_cover_div') || isDisplayed('div#black_cover_div') || isVisible('div#nowplaying_div'))
					{
						if($(e.target).attr('id') != 'nowplaying_volume_slider')
						{
							e.preventDefault();
						}
					}

					if(Math.abs(click_moved_x) > 25 || Math.abs(click_moved_y) > 25)
					{
						moved = true;
					}

					if(click_start_x < 25)
					{
						e.preventDefault();

						if(!touch_gesture_finished && click_moved_x > 50 && Math.abs(click_moved_y) < 50)
						{
							showMenu();
							touch_gesture_finished = true;
						}
					}

					if(click_start_x > window_width - 25 && ua_ios && ua_standalone)
					{
						e.preventDefault();

						if(!touch_gesture_finished && click_moved_x < -50 && Math.abs(click_moved_y) < 50)
						{
							goBack();
							touch_gesture_finished = true;
						}
					}
				}
			});

			$(document).on(offclick_event, function()
			{
				touched = false;
			});
		}
		else
		{
			$(document).on(onclick_event, function(e)
			{
				moved = false;

				click_start_x = e.pageX;
				click_start_y = e.pageY;
			});

			$(document).on(onmove_event, function(e)
			{
				if(typeof click_start_x != 'undefined' && typeof click_start_y != 'undefined')
				{
					click_end_x = e.pageX;
					click_end_y = e.pageY;

					click_moved_x = click_end_x - click_start_x;
					click_moved_y = click_end_y - click_start_y;

					if(Math.abs(click_moved_x) > 25 || Math.abs(click_moved_y) > 25)
					{
						moved = true;
					}
				}
			});
		}

		if(ua_touch)
		{
			$(document).on(onmove_event, 'div#topbar_div', function(e)
			{
				e.preventDefault();
			});

			$(document).on(onclick_event, 'div#bottombar_div', function(e)
			{
				click_bottombar_start_x = e.originalEvent.touches[0].pageX;
				click_bottombar_start_y = e.originalEvent.touches[0].pageY;
			});

			$(document).on(onmove_event, 'div#bottombar_div', function(e)
			{
				e.preventDefault();

				click_bottombar_end_x = e.originalEvent.touches[0].pageX;
				click_bottombar_end_y = e.originalEvent.touches[0].pageY;
				click_bottombar_moved_x = click_bottombar_end_x - click_bottombar_start_x;
				click_bottombar_moved_y = click_bottombar_end_y - click_bottombar_start_y;

				if(!touch_gesture_finished && click_bottombar_moved_y < -100 && Math.abs(click_bottombar_moved_x) < 50)
				{
					showNowplaying();
					touch_gesture_finished = true;
				}
			});

			$(document).on(onclick_event, 'div#nowplaying_div', function(e)
			{
				click_nowplaying_start_x = e.originalEvent.touches[0].pageX;
				click_nowplaying_start_y = e.originalEvent.touches[0].pageY;
			});

			$(document).on(onmove_event, 'div#nowplaying_div', function(e)
			{
				click_nowplaying_end_x = e.originalEvent.touches[0].pageX;
				click_nowplaying_end_y = e.originalEvent.touches[0].pageY;
				click_nowplaying_moved_x = click_nowplaying_end_x - click_nowplaying_start_x;
				click_nowplaying_moved_y = click_nowplaying_end_y - click_nowplaying_start_y;

				if(!touch_gesture_finished && $(e.target).attr('id') != 'nowplaying_volume_slider' && click_nowplaying_moved_y > 100 && Math.abs(click_nowplaying_moved_x) < 50)
				{	
					hideNowplaying();
					touch_gesture_finished = true;
				}
			});

			$(document).on(onclick_event, 'div#nowplaying_albumart_div', function(e)
			{
				click_albumart_start_x = e.originalEvent.touches[0].pageX;
				click_albumart_start_y = e.originalEvent.touches[0].pageY;
				click_albumart_moved_x = 0;
				click_albumart_moved_y = 0;
			});

			$(document).on(onmove_event, 'div#nowplaying_albumart_div', function(e)
			{
				click_albumart_end_x = e.originalEvent.touches[0].pageX;
				click_albumart_end_y = e.originalEvent.touches[0].pageY;
				click_albumart_moved_x = click_albumart_end_x - click_albumart_start_x;
				click_albumart_moved_y = click_albumart_end_y - click_albumart_start_y;
			});

			$(document).on(offclick_event, 'div#nowplaying_albumart_div', function(e)
			{
				if(click_albumart_moved_x < -100 && Math.abs(click_albumart_moved_y) < 200)
				{
					remoteControl('next');
				}
			});
		}

		// Scrolling
		if(ua_touch && !ua_standalone)
		{
			$(document).on('scroll', function(e)
			{
					scroll_top = $(window).scrollTop();

					if(scroll_top == 0)
					{
						setTimeout(function()
						{
							if(!touched && scroll_top == 0)
							{
								scrollToTop();
							}
						}, 250);
					}
			});
		}

		// Highlight
		highlight_green_elements = 'div.menu_item_click_div, div#menu_click_div, div#back_click_div, div#more_menu_click_div, div#message_buttons_div div, div#nowplaying_remote_click_div, div#nowplaying_refresh_click_div, div#more_menu_inner_div div, div#nowplaying_power_click_div, div#nowplaying_more_menu_click_div, div#nowplaying_more_menu_inner_div div, div#nowplaying_remote_div div, div#nowplaying_volume_buttons_inner_div div';
		highlight_dark_grey_elements = 'div#message_share_inner_div div, div.media_options_inner_div div';
		highlight_light_grey_elements = 'div.media_show_more_click_div';
		highlight_opacity_elements = 'span.show_page_click_span';
		highlight_other_elements = 'div#nowplaying_albumart_div, div.media_div';

		$(document).on(onclick_event, highlight_green_elements, function()
		{
			$(this).css('background-color', '#669900');
			$(this).css('color', '#fff');

			if($(this).parent().attr('id') == 'more_menu_inner_div' && $(this).is(':first-child'))
			{
				$('div#more_menu_arrow_div').css('border-bottom-color', '#669900');
			}
			else if($(this).parent().attr('id') == 'nowplaying_more_menu_inner_div' && $(this).is(':first-child'))
			{
				$('div#nowplaying_more_menu_arrow_div').css('border-bottom-color', '#669900');
			}
		});

		$(document).on(onmove_event, highlight_green_elements, function()
		{
			if(moved)
			{
				$(this).css('background-color', '');
				$(this).css('color', '');

				if($(this).parent().attr('id') == 'more_menu_inner_div' && $(this).is(':first-child'))
				{
					$('div#more_menu_arrow_div').css('border-bottom-color', '');
				}
				else if($(this).parent().attr('id') == 'nowplaying_more_menu_inner_div' && $(this).is(':first-child'))
				{
					$('div#nowplaying_more_menu_arrow_div').css('border-bottom-color', '');
				}
			}
		});

		$(document).on(offclick_event+' mouseout', highlight_green_elements, function()
		{
			$(this).css('background-color', '');
			$(this).css('color', '');

			if($(this).parent().attr('id') == 'more_menu_inner_div' && $(this).is(':first-child'))
			{
				$('div#more_menu_arrow_div').css('border-bottom-color', '');
			}
			else if($(this).parent().attr('id') == 'nowplaying_more_menu_inner_div' && $(this).is(':first-child'))
			{
				$('div#nowplaying_more_menu_arrow_div').css('border-bottom-color', '');
			}
		});

		$(document).on(onclick_event, highlight_dark_grey_elements, function()
		{
			$(this).css('background-color', '#aaa');

			if($(this).parent().hasClass('media_options_inner_div') && $(this).is(':first-child'))
			{
				$('div.media_arrow_div').css('border-bottom-color', '#aaa');
			}
		});

		$(document).on(onmove_event, highlight_dark_grey_elements, function()
		{
			if(moved)
			{
				$(this).css('background-color', '');

				if($(this).parent().hasClass('media_options_inner_div') && $(this).is(':first-child'))
				{
					$('div.media_arrow_div').css('border-bottom-color', '');
				}
			}
		});

		$(document).on(offclick_event+' mouseout', highlight_dark_grey_elements, function()
		{
			$(this).css('background-color', '');

			if($(this).parent().hasClass('media_options_inner_div') && $(this).is(':first-child'))
			{
				$('div.media_arrow_div').css('border-bottom-color', '');
			}
		});

		$(document).on(onclick_event, highlight_light_grey_elements, function()
		{
			$(this).css('background-color', '#ccc');
		});

		$(document).on(onmove_event, highlight_light_grey_elements, function()
		{
			if(moved)
			{
				$(this).css('background-color', '');
			}
		});

		$(document).on(offclick_event+' mouseout', highlight_light_grey_elements, function()
		{
			$(this).css('background-color', '');
		});

		$(document).on(onclick_event, highlight_opacity_elements, function()
		{
			$(this).css('opacity', '0.5');
		});

		$(document).on(onmove_event, highlight_opacity_elements, function()
		{
			if(moved)
			{
				$(this).css('opacity', '');
			}
		});

		$(document).on(offclick_event+' mouseout', highlight_opacity_elements, function()
		{
			$(this).css('opacity', '');
		});

		$(document).on(onclick_event, 'div.media_div', function()
		{
			$('div.media_corner_arrow_div', this).css('border-color', 'transparent transparent #aaa transparent');
		});

		$(document).on(onmove_event, 'div.media_div', function()
		{
			if(moved)
			{
				$('div.media_corner_arrow_div', this).css('border-color', '');
			}
		});

		$(document).on(offclick_event+' mouseout', 'div.media_div', function()
		{
			$('div.media_corner_arrow_div', this).css('border-color', '');
		});

		// Mouse pointer
		$(document).on('mouseover', highlight_green_elements+', '+highlight_dark_grey_elements+', '+highlight_light_grey_elements+', '+highlight_opacity_elements+', '+highlight_other_elements, function()
		{
			this.style.cursor = 'pointer';
		});

		// Divisions
		$(document).on(click_event, 'div.show_page_click_div', function()
		{
			if(!moved)
			{
				var array = $('span.hidden_value_span', this).html().split('|:|');

				changePage(array[0], array[1], array[2], array[3]);
			}
		});

		$(document).on(click_event, 'div#transparent_cover_div', function()
		{
			if(!moved)
			{
				hideMenu();
				hideMoreMenu();
			}
		});

		$(document).on(click_event, 'div.open_window_click_div', function()
		{
			if(!moved)
			{
				var url = $('span.hidden_value_span', this).html();
				openWindow(url);
			}
		});

		$(document).on(click_event, 'div#menu_click_div', function()
		{
			if(!moved)
			{
				showMenu();
			}
		});

		$(document).on(click_event, 'div.menu_item_click_div', function()
		{
			if(!moved)
			{
				changePage('0', this.title.toLowerCase(), '', '');
			}
		});

		$(document).on(click_event, 'div#more_menu_click_div', function()
		{
			if(!moved)
			{
				showMoreMenu();
			}
		});

		$(document).on(click_event, 'div#more_menu_inner_div div', function()
		{
			if(!moved)
			{
				hideMoreMenu();
			}
		});

		$(document).on(click_event, 'div.hide_message_click_div', function()
		{
			if(!moved)
			{
				var array = $('span.hidden_value_span', this).html().split('|:|');
				hideMessage(array[0], array[1]);
			}
		});

		$(document).on(click_event, 'div#check_for_updates_click_div', function()
		{
			if(!moved)
			{
				checkForUpdates('manual');
			}
		});

		$(document).on(click_event, 'div#delete_cookies_click_div', function()
		{
			if(!moved)
			{
				deleteAllCookies();
			}
		});

		$(document).on(click_event, 'div#nowplaying_remote_click_div', function()
		{
			if(!moved)
			{
				toggleNowplaying();
			}
		});
		
		$(document).on(click_event, 'div#bottombar_nowplaying_div', function()
		{
			if(!moved)
			{
				toggleNowplaying();
			}
		});

		$(document).on(click_event, 'div#nowplaying_refresh_click_div', function()
		{
			if(!moved)
			{
				refreshNowplaying();
			}
		});

		$(document).on(click_event, 'div#nowplaying_power_click_div', function()
		{
			if(!moved)
			{
				togglePower();
			}
		});

		$(document).on(click_event, 'div#nowplaying_more_menu_click_div', function()
		{
			if(!moved)
			{
				showNowplayingMoreMenu();
			}
		});

		$(document).on(click_event, 'div#nowplaying_more_menu_inner_div div', function()
		{
			if(!moved)
			{
				hideNowplayingMoreMenu();
			}
		});

		$(document).on(click_event, 'div#nowplaying_cover_div', function()
		{
			if(!moved)
			{
				hideNowplayingMoreMenu();
			}
		});

		$(document).on(click_event, 'div#nowplaying_albumart_div', function()
		{
			if(!moved)
			{
				var uri = $('span#nowplaying_uri_span').html();
				browseAlbum(uri);
			}
		});

		$(document).on(click_event, 'div#recently_played_click_div', function()
		{
			if(!moved)
			{
				hideNowplaying();
				changePage('0', 'recently-played', '', '');
			}
		});

		$(document).on(click_event, 'div#clear_recently_played_click_div', function()
		{
			if(!moved)
			{
				clearRecentlyPlayed();
			}
		});

		$(document).on(click_event, 'div.media_inner_div', function()
		{
			var media_arrow_div = 'div#'+this.id+'_arrow';
			var media_corner_arrow_div = 'div#'+this.id+'_corner_arrow';
			var media_options_div = 'div#'+this.id+'_options';
			var media_options_div_display = $(media_options_div).css('display');

			if(!moved)
			{
				$('div.media_arrow_div').hide();
				$('div.media_options_div').hide();
				$('div.media_corner_arrow_div').show();

				setTimeout(function()
				{
					if(media_options_div_display == 'none')
					{
						$(media_corner_arrow_div).hide();
						$(media_arrow_div).show();

						$(media_options_div).show();
						divHide(media_options_div);
						divFadeIn(media_options_div);
					}
				}, 250);
			}
		});

		$(document).on(click_event, 'div.media_remote_click_div', function()
		{
			if(!moved)
			{
				var action = $('span.hidden_value_span', this).html();
				remoteControl(action);
			}
		});

		$(document).on(click_event, 'div.media_volume_click_div', function()
		{
			if(!moved)
			{
				var action = $('span.hidden_value_span', this).html();
				changeVolume(action);
			}
		});

		$(document).on(click_event, 'div.media_toggle_shuffle_repeat_click_div', function()
		{
			if(!moved)
			{
				var action = $('span.hidden_value_span', this).html();
				toggleShuffleRepeat(action);
			}
		});

		$(document).on(click_event, 'div.media_play_uri_click_div', function()
		{
			if(!moved)
			{
				var uri = $('span.hidden_value_span', this).html();
				playUri(uri);
			}
		});

		$(document).on(click_event, 'div.media_play_random_uri_click_div', function()
		{
			if(!moved)
			{
				var uri = $('span.hidden_value_span', this).html();
				playRandomUri(uri);
			}
		});

		$(document).on(click_event, 'div.media_browse_playlist_click_div', function()
		{
			if(!moved)
			{
				var uri = $('span.hidden_value_span', this).html();
				changePage('1', 'playlists', 'browse', 'uri='+uri);
			}
		});

		$(document).on(click_event, 'div.media_add_playlist_click_div', function()
		{
			if(!moved)
			{
				var uri = $('span.hidden_value_span', this).html();
				savePlaylist(uri);
			}
		});

		$(document).on(click_event, 'div.media_remove_playlist_click_div', function()
		{
			if(!moved)
			{
				var id = $('span.hidden_value_span', this).html();
				removePlaylist(id);
			}
		});

		$(document).on(click_event, 'div.media_star_click_div', function()
		{
			if(!moved)
			{	
				var array = $('span.hidden_value_span', this).html().split('|:|');

				if($('img', this).length)
				{
					var src = $('img', this).attr('src');

					if(src == 'img/starred-24.png?'+global_serial)
					{
						unstar(array[3]);
						$('img', this).attr('src', 'img/star-24.png?'+global_serial);
					}
					else
					{
						star(array[0], array[1], array[2], array[3]);
						$('img', this).attr('src', 'img/starred-24.png?'+global_serial);
					}
				}
				else
				{
					if($('span.text_span', this).html() == 'Star')
					{
						star(array[0], array[1], array[2], array[3]);
						$('span.text_span', this).html('Unstar');
					}
					else
					{
						unstar(array[3]);
						$('span.text_span', this).html('Star');
					}	
				}
			}
		});

		$(document).on(click_event, 'div.media_unstar_click_div', function()
		{
			if(!moved)
			{
				var uri = $('span.hidden_value_span', this).html();
				unstar(uri);
			}
		});

		$(document).on(click_event, 'div.media_search_click_div', function()
		{
			if(!moved)
			{
				var string = $('span.hidden_value_span', this).html();
				hideNowplaying();
				searchSpotify(string);
			}
		});

		$(document).on(click_event, 'div#clear_search_history_click_div', function()
		{
			if(!moved)
			{
				clearSearchHistory();
			}
		});

		$(document).on(click_event, 'div.media_browse_album_click_div', function()
		{
			if(!moved)
			{
				var uri = $('span.hidden_value_span', this).html();
				browseAlbum(uri);
			}
		});

		$(document).on(click_event, 'div.media_share_click_div', function()
		{
			if(!moved)
			{
				var url = $('span.hidden_value_span', this).html();
				shareUrl(url);
			}
		});

		$(document).on(click_event, 'div.media_share_android_click_div', function()
		{
			if(!moved)
			{
				var url = $('span.hidden_value_span', this).html();
				shareToAndroid(url);
			}
		});

		$(document).on(click_event, 'div.media_lyrics_click_div', function()
		{
			if(!moved)
			{
				hideNowplaying();

				var array = $('span.hidden_value_span', this).html().split('|:|');
				changePage('1', 'lyrics', '', '&artist='+array[0]+'&title='+array[1]);
			}
		});

		$(document).on(click_event, 'div.media_show_more_click_div', function()
		{
			if(!moved)
			{
				$('div.media_div').show();
				$('div.media_show_more_click_div').hide();

				if(this.id != '')
				{
					var margin_top = $('div#page_div').css('margin-top');
					var margin_top = parseInt(margin_top.slice(0,-2));
					var scroll_top = $('div#'+this.id+'_title').offset().top - margin_top;

					if(scroll_top > 0)
					{
						setTimeout(function() { $('html, body').scrollTop(scroll_top); }, 250);
					}
				}
			}
		});

		// Spans
		$(document).on(click_event, 'span.show_page_click_span', function()
		{
			if(!moved)
			{
				var array = $('span.hidden_value_span', this).html().split('|:|');
				changePage(array[0], array[1], array[2], array[3]);
			}
		});

		// Sliders
		$(document).on('change', 'input#nowplaying_volume_slider', function()
		{
			$('span#nowplaying_volume_level_span').html(this.value);

			if(typeof slider_timeout != 'undefined')
			{
				clearTimeout(slider_timeout);
			}

			slider_timeout = setTimeout(function()
			{
				changeVolume('slider');
			}, 250);
		});

		// Forms
		$(document).on('submit', 'form#add_playlist_form', function()
		{
			savePlaylist($('input:text#add_playlist_uri_input').val());
			return false;
		});

		$(document).on('submit', 'form#search_form', function()
		{
			searchSpotify($('input:text#search_input').val());
			return false;
		});

		// Text fields
		$(document).on('focus', 'input:text', function()
		{
			var val = $(this).val();

			if(hasCharacters(val, '...'))
			{
				$(this).val('').css('color', '#000');
			}
		});

		// Hash
		$(window).bind('hashchange', function ()
		{
			hashChange();
		});

		// Load page
		setCookie('test_cookies', '1');
		var cookie_name = 'test_cookies';
		var cookie = getCookie(cookie_name);

		if(typeof cookie == 'undefined')
		{
			window.location.replace('error.php?error_code=5');
		}
		else
		{
			setCookie('test_cookies', '1', 0);

			$.get('main.php?check_for_errors', function(data)
			{
				if(data != 0)
				{
					window.location.replace('error.php?error_code='+data);
				}
				else
				{
					var cookie_name = 'hide_first_time_information_'+global_version;
					var cookie_days = 365;
					var cookie = getCookie(cookie_name);

					if(typeof cookie == 'undefined')
					{
						setCookie(cookie_name, '1', cookie_days);

						pageLoading(0);
						showPage('first-time', '', '');
					}
					else
					{
						if(ua_standalone)
						{
							var cookie_name = 'current_page_'+global_version;
							var cookie = getCookie(cookie_name);

							if(typeof cookie != 'undefined')
							{
								var array = cookie.split('|:|');

								if(array[0] == 1)
								{
									var timeout = 250;
								}
								else
								{
									var timeout = 0;
								}
								
								setTimeout(function()
								{
									pageLoading(array[0]);
									showPage(array[1], array[2], array[3]);
								}, timeout);
							}
							else
							{
								pageLoading(0);
								showPage('playlists', '', '');
							}
						}
						else
						{
							hashChange();
						}
					}

					setTimeout(function()
					{
						refreshNowplaying();
						autoRefreshNowplaying('start');
						onAppLoad();
					}, 1000);

					if(config_keyboard_shortcuts)
					{
						$.getScript('js/keyboard-shortcuts.js?'+global_serial);
					}

					if(ua_webkit)
					{
						$('head').append('<link href="css/style-webkit.css?'+global_serial+'" rel="stylesheet" type="text/css">');
					}
				}
			});
		}
	});
});

// Variables

function setSystemVariables(array)
{
	array = array.split('|:|');

	// Global
	global_name = array[0];
	global_version = array[1];
	global_serial = array[2];
	global_website = array[3];

	// Config
	config_nowplaying_update_interval = array[4];
	config_facebook_like_button = trueOrFalse(array[5]);
	config_check_for_updates = trueOrFalse(array[6]);
	config_keyboard_shortcuts = trueOrFalse(array[7]);

	// Events
	events_transition_end = 'webkitTransitionEnd transitionend otransitionend oTransitionEnd MSTransitionEnd';

	// Movement
	moved = false;

	// User agent
	ua = navigator.userAgent;

	// Feature detection
	ua_supported_inputtype_range = false;
	ua_supported_csstransitions = false;
	ua_supported_csstransforms3d = false;

	if(Modernizr.touch)
	{
		ua_touch = true;

		click_event = 'touchend';
		onclick_event = 'touchstart';
		onmove_event = 'touchmove';
		offclick_event = 'touchend';
	}
	else
	{
		ua_touch = false;

		click_event = 'mouseup';
		onclick_event = 'mousedown';
		onmove_event = 'mousemove';
		offclick_event = 'mouseup';
	}

	if(Modernizr.inputtypes.range)
	{
		ua_supported_inputtype_range = true;
	}

	if(Modernizr.csstransitions)
	{
		ua_supported_csstransitions = true;
	}

	if(Modernizr.csstransforms3d)
	{
		ua_supported_csstransforms3d = true;
	}

	// Device & browser
	ua_ios = false;
	ua_android = false;
	ua_webkit = false;
	ua_standalone = false;
	ua_supported_browser = false;

	if(hasCharacters(ua, 'iPhone|:|iPod|:|iPad'))
	{
		ua_ios = true;
	}

	if(hasCharacters(ua, 'Android'))
	{
		ua_android = true;
	}

	if(hasCharacters(ua, 'AppleWebKit'))
	{
		ua_webkit = true;
	}

	if(hasCharacters(ua, 'SpotCommander') || ('standalone' in window.navigator) && window.navigator.standalone)
	{
		ua_standalone = true;
	}

	if(hasCharacters(ua, 'Windows Phone|:|ZuneWP7|:|NokiaBrowser|:|Symbian|:|SymbOS|:|Series60|:|Series40|:|Bada|:|webOS|:|BlackBerry|:|Fennec|:|Opera Mobi'))
	{
		ua_supported_browser = false;
	}
	else if(ua_ios)
	{
		if(ua_webkit && hasCharacters(ua, 'OS 5_|:|OS 6_'))
		{
			ua_supported_browser = true;
		}
	}
	else if(ua_android)
	{
		if(ua_webkit && hasCharacters(ua, 'Android 2.3|:|Android 4'))
		{
			ua_supported_browser = true;
		}
	}
	else if(hasCharacters(ua, 'Chrome|:|Firefox|:|Opera|:|MSIE 9|:|MSIE 10') || hasCharacters(ua, 'Safari') && hasCharacters(ua, 'Macintosh') || hasCharacters(ua, 'Safari') && hasCharacters(ua, 'Windows'))
	{
		ua_supported_browser = true;
	}
}

// UI

function divFadeIn(id)
{
	setTimeout(function()
	{
		if($(id).css('opacity') == 0)
		{
			if(ua_supported_csstransitions)
			{
				$(id).addClass('div_fadein_animation');
			}
			else
			{
				$(id).animate({ opacity: '1' }, 250, 'easeInCubic');
			}
		}
	}, 25);
}

function divHide(id)
{
	if($(id).css('opacity') == 1)
	{
		if(ua_supported_csstransitions)
		{
			$(id).removeClass('div_fadein_animation');
		}

		$(id).css('opacity', '0');
	}
}

function openWindow(url)
{
	if(ua_ios && ua_standalone || ua_android && hasCharacters(ua, 'Android 2.3'))
	{
		var a = document.createElement('a');
		a.setAttribute('href', url);
		a.setAttribute('target', '_blank');
		var dispatch = document.createEvent('HTMLEvents');
		dispatch.initEvent('click', true, true);
		a.dispatchEvent(dispatch);
	}
	else
	{
		window.open(url);
	}
}

function focusInput(id)
{
	if(ua_touch)
	{
		$('input').blur();
	}
	else if(typeof id != 'undefined')
	{
		$(id).focus();
	}
}

function setCss()
{
	// Start
	$('head style').empty();

	// Window
	window_width = $(window).width();
	window_height = $(window).height();

	// Page
	if(ua_touch && !ua_standalone)
	{
		var margin = $('div#page_div').css('margin-top');
		var margin = parseInt(margin.slice(0, -2)) * 2;
		var height = window_height - margin + 120;
		$('div#page_div').css('min-height', height+'px');
	}

	// Menu
	menu_css_left = $('div#menu_div').css('left');

	// Now playing
	$('div#nowplaying_div').css('bottom', '-'+window_height+'px');
	nowplaying_css_bottom = $('div#nowplaying_div').css('bottom');

	if(ua_supported_csstransforms3d)
	{
		$('head style').append('.show_nowplaying_animation{transform: translate3d(0,-'+window_height+'px,0);-webkit-transform: translate3d(0,-'+window_height+'px,0);-moz-transform: translate3d(0,-'+window_height+'px,0);-o-transform: translate3d(0,-'+window_height+'px,0);-ms-transform: translate3d(0,-'+window_height+'px,0);}');
		$('head style').append('.hide_nowplaying_animation{transform: translate3d(0,'+window_height+'px,0);-webkit-transform: translate3d(0,'+window_height+'px,0);-moz-transform: translate3d(0,'+window_height+'px,0);-o-transform: translate3d(0,'+window_height+'px,0);-ms-transform: translate3d(0,'+window_height+'px,0);}');
	}

	// Album art
	var changeside = window_width * 2;

	if(ua_supported_csstransforms3d)
	{
		$('head style').append('.albumart_slideout_animation{transform: translate3d(-'+window_width+'px,0,0);-webkit-transform: translate3d(-'+window_width+'px,0,0);-moz-transform: translate3d(-'+window_width+'px,0,0);-o-transform: translate3d(-'+window_width+'px,0,0);-ms-transform: translate3d(-'+window_width+'px,0,0);}');
		$('head style').append('.albumart_changeside_transform{transform: translate3d('+changeside+'px,0,0);-webkit-transform: translate3d('+changeside+'px,0,0);-moz-transform: translate3d('+changeside+'px,0,0);-o-transform: translate3d('+changeside+'px,0,0);-ms-transform: translate3d('+changeside+'px,0,0);}');
		$('head style').append('.albumart_slidein_animation{transform: translate3d(0,0,0);-webkit-transform: translate3d(0,0,0);-moz-transform: translate3d(0,0,0);-o-transform: translate3d(0,0,0);-ms-transform: translate3d(0,0,0);}');
	}
}

function scrollToTop()
{
	var scroll_top = $(window).scrollTop();

	if(scroll_top != 1)
	{
		window.scrollTo(0,1);
	}
}

// Share

function shareUrl(url)
{
	if(isLocalFile(url))
	{
		showMessageDiv('Error', 'Not possible for local files.', '', '', '', '');
	}
	else
	{
		var facebook_append = '';

		if(!config_facebook_like_button || ua_android && ua_standalone)
		{
			var facebook_append = '';
		}
		else if(config_facebook_like_button)
		{
			var facebook_append = '<div id="message_share_facebook_like_button_div"></div>';

			setTimeout(function()
			{	
				$('div#message_share_facebook_like_button_div').html('<iframe src="http://www.facebook.com/plugins/like.php?href='+url+'&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font=verdana&amp;height=21&amp;locale=en_US" scrolling="no" frameborder="0" allowTransparency="true"></iframe>');
			}, 750);
		}

		if(ua_android && ua_standalone)
		{
			var buttons_append = '<div class="media_share_android_click_div"><img src="img/android-64.png?'+global_serial+'" title="Share on Android" alt="Image"><span class="hidden_value_span">'+decodeURIComponent(url)+'</span></div>';
		}
		else
		{
			var buttons_append = '<div class="open_window_click_div"><img src="img/facebook-48.png?'+global_serial+'" alt="Image"><span class="hidden_value_span">http://www.facebook.com/sharer.php?u='+url+'</span></div><div class="open_window_click_div"><img src="img/twitter-48.png?'+global_serial+'" alt="Image"><span class="hidden_value_span">https://twitter.com/share?url='+url+'</span></div><div class="open_window_click_div"><img src="img/googleplus-48.png?'+global_serial+'" title="Share on Google+" alt="Image"><span class="hidden_value_span">https://plus.google.com/share?url='+url+'</span></div>';
		}

		showMessageDiv('Share', '<div id="message_share_div"><div id="message_share_inner_div">'+buttons_append+'</div>'+facebook_append+'</div>', '', '', '', '');
	}
}

function shareToAndroid(string)
{
	if(ua_android && ua_standalone && window.Android)
	{
		Android.JSshare(string);
	}
}

// Messages

function checkForMessages()
{
	if(!ua_supported_browser)
	{
		var cookie_name = 'hide_unsupported_browser_warning_'+global_version;
		var cookie_days = 7;
		var cookie = getCookie(cookie_name);

		if(typeof cookie == 'undefined')
		{
			showMessageDiv('Warning', 'You\'re using an unsupported browser. If things don\'t work as they should, you know why.', cookie_name, cookie_days, 'http://code.google.com/p/spotcommander/#Testing', 'Help');
		}
	}

	var latest_version = getCookie('latest_version');

	if(config_check_for_updates && parseFloat(latest_version) > global_version)
	{
		var cookie_name = 'hide_update_available_warning_'+global_version;
		var cookie_days = 7;
		var cookie = getCookie(cookie_name);

		if(typeof cookie == 'undefined')
		{
			showMessageDiv('Update available', global_name+' '+latest_version+' has been released!', cookie_name, cookie_days, global_website, 'Website');
		}
	}

	if(ua_ios)
	{
		if(ua_standalone)
		{
			var cookie_name = 'hide_ios_back_gesture_tip_'+global_version;
			var cookie_days = 365;
			var cookie = getCookie(cookie_name);

			if(typeof cookie == 'undefined')
			{
				showMessageDiv('iOS tip', 'Since you are running fullscreen and your device has no back button, you can swipe in from the right to go back.', cookie_name, cookie_days, '', '');
			}
		}
		else
		{
			var cookie_name = 'hide_homescreen_tip_'+global_version;
			var cookie = getCookie(cookie_name);

			if(typeof cookie == 'undefined')
			{
				if(hasCharacters(ua, 'iPad'))
				{
					var cookie_days = 28;
					showMessageDiv('iPad tip', 'Add '+global_name+' to your home screen to get fullscreen like a native app.', cookie_name, cookie_days, 'http://code.google.com/p/spotcommander/wiki/AddToHomeScreen', 'How to');
				}
				else
				{
					var cookie_days = 1;
					showMessageDiv('iPhone/iPod warning', 'To function correctly, '+global_name+' should be added to your home screen.', cookie_name, cookie_days, 'http://code.google.com/p/spotcommander/wiki/AddToHomeScreen', 'How to');
				}
			}
		}

		if(hasCharacters(ua, 'CriOS'))
		{
			var cookie_name = 'hide_ios_chrome_warning_'+global_version;
			var cookie_days = 7;
			var cookie = getCookie(cookie_name);

			if(typeof cookie == 'undefined')
			{
				showMessageDiv('iOS warning', 'If you are not going to add '+global_name+' to your home screen (recommended), it is recommended to use Safari over Chrome.', cookie_name, cookie_days, 'http://code.google.com/p/spotcommander/#Testing', 'Why');
			}
		}
	}
	else if(ua_android)
	{
		if(!ua_standalone)
		{
			var cookie_name = 'hide_android_app_tip_'+global_version;
			var cookie_days = 1;
			var cookie = getCookie(cookie_name);

			if(typeof cookie == 'undefined')
			{
				showMessageDiv('Android app', 'You should install the Android app. It will give you fullscreen & multitasking like a native app, and makes it easy to add playlists from the Spotify app.', cookie_name, cookie_days, 'market://details?id=net.olejon.spotcommander', 'Download');
			}
		}

		if(hasCharacters(ua, 'Chrome'))
		{
			var cookie_name = 'hide_android_chrome_warning_'+global_version;
			var cookie_days = 7;
			var cookie = getCookie(cookie_name);

			if(typeof cookie == 'undefined')
			{
				showMessageDiv('Android warning', 'If you are not going to install the Android app, it is recommended to use the stock browser over Chrome.', cookie_name, cookie_days, 'http://code.google.com/p/spotcommander/#Testing', 'Why');
			}
		}
	}
}

function showMessageDiv(subject, message, cookie_name, cookie_days, url, url_text)
{
	if(!isDisplayed('div#black_cover_div') && !isDisplayed('div#message_div'))
	{
		$('div#black_cover_div').show();

		if(ua_supported_csstransitions)
		{
			setTimeout(function()
			{
				$('div#black_cover_div').addClass('black_cover_div_fadein_animation').on(events_transition_end, function()
				{
					fillMessageDiv(subject, message, cookie_name, cookie_days, url, url_text);
				});
			}, 25);
		}
		else
		{
			$('div#black_cover_div').animate({ opacity: '0.5' }, 250, 'easeOutQuad', function()
			{
				fillMessageDiv(subject, message, cookie_name, cookie_days, url, url_text);
			});
		}
	}
}

function fillMessageDiv(subject, message, cookie_name, cookie_days, url, url_text)
{
	$('div#message_div').html('<div id="message_title_div">'+subject+'</div><div id="message_message_div">'+message+'</div>');

	if(cookie_name != '' && cookie_days != '' && url != '' && url_text != '')
	{
		$('div#message_div').append('<div id="message_buttons_div"><div class="hide_message_click_div" onclick="void(0)">Close<span class="hidden_value_span">'+cookie_name+'|:|'+cookie_days+'</span></div><div class="open_window_click_div" onclick="void(0)">'+url_text+'<span class="hidden_value_span">'+url+'</span></div></div><input type="hidden" id="hide_message_input" value="">');
	}
	else if(cookie_name != '' && cookie_days != '')
	{
		$('div#message_div').append('<div id="message_buttons_div"><div class="hide_message_click_div" onclick="void(0)">Close<span class="hidden_value_span">'+cookie_name+'|:|'+cookie_days+'</span></div></div><input type="hidden" id="hide_message_input" value="">');
	}
	else
	{
		$('div#message_div').append('<div id="message_buttons_div"><div class="hide_message_click_div" onclick="void(0)">Close<span class="hidden_value_span">|:|</span></div></div><input type="hidden" id="hide_message_input" value="">');
	}

	$('div#message_div').show();

	if(ua_android && ua_standalone && window.Android)
	{
		Android.JSsetSetting('MESSAGE_DIV', 'visible');
	}

	var height = $('div#message_div').height();
	var margin = height / 2;

	$('div#message_div').css('margin-top', '-'+margin+'px');

	if(ua_supported_csstransitions && ua_supported_csstransforms3d)
	{
		$('div#message_div').addClass('message_scale');

		setTimeout(function()
		{
			$('div#message_div').addClass('show_message_animation');
		}, 25);
	}
	else
	{
		divFadeIn('div#message_div');
	}
}

function hideMessageDiv()
{
	if(isDisplayed('div#message_div'))
	{
		$('div#message_div').hide();
		$('div#black_cover_div').hide();

		if(ua_android && ua_standalone && window.Android)
		{
			Android.JSsetSetting('MESSAGE_DIV', 'hidden');
		}

		if(ua_supported_csstransitions && ua_supported_csstransforms3d)
		{
			$('div#message_div').removeClass('message_scale show_message_animation');
		}
		else
		{
			divHide('div#message_div');
		}

		if(ua_supported_csstransitions)
		{
			$('div#black_cover_div').removeClass('black_cover_div_fadein_animation');
		}
		else
		{
			$('div#black_cover_div').hide().css('opacity', '');
		}
	}
}

function hideMessage(cookie_name, cookie_days)
{
	if(cookie_name != '' && cookie_days != '')
	{	
		setCookie(cookie_name, '1', cookie_days);
	}

	hideMessageDiv();
	setTimeout(function() { checkForMessages(); }, 500);
}

// Updates

function checkForUpdates(type)
{
	var cookie_name = 'last_update_check';
	var cookie = getCookie(cookie_name);
	var time = new Date().getTime();

	if(type == 'manual')
	{
		pageLoading(1);

		page_xhr = $.get('main.php?check_for_updates', function(data)
		{
			if(data == 0)
			{
				setCookie('latest_version', '1', 0);
			}
			else
			{
				setCookie('latest_version', data, 365);
			}

			changePage('0', 'about', '', '');
		});

		setCookie(cookie_name, time, 365);
	}
	else if(type == 'auto' && config_check_for_updates)
	{
		if(typeof cookie == 'undefined' || time - cookie > 1000 * 3600 * 24)
		{
			$.get('main.php?check_for_updates', function(data)
			{
				if(data == 0)
				{
					setCookie('latest_version', '1', 0);
				}
				else
				{
					setCookie('latest_version', data, 365);
				}
			});

			setCookie(cookie_name, time, 365);
		}

		var latest_version = getCookie('latest_version');

		if(parseFloat(latest_version) > global_version)
		{
			$('img#about_img').attr('src', 'img/update-available-48.png?'+global_serial);
		}
	}
}

// Native apps

function onAppLoad()
{
	if(ua_android && ua_standalone && window.Android)
	{
		if(Android.JSgetSetting('ADD_PLAYLIST_URL') != '')
		{
			Android.JSshowToast('Playlist is being added', 2);

			var url = Android.JSgetSetting('ADD_PLAYLIST_URL');
			savePlaylist(url);

			Android.JSsetSetting('ADD_PLAYLIST_URL', '');
		}
	}
}

// Errors

function checkForErrors()
{
	$.get('main.php?check_for_errors', function(data)
	{
		if(data != 0)
		{
			window.location.replace('error.php?error_code='+data);
		}
	});
}

// Check stuff

function trueOrFalse(string)
{
	if(string == 'true')
	{
		return true;
	}
	else
	{
		return false;
	}
}

function isDisplayed(id)
{
	if($(id).is(':visible'))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function isVisible(id)
{
	if($(id).css('visibility') == 'visible')
	{
		return true;
	}
	else
	{
		return false;
	}
}

function hasCharacters(string, characters)
{
	var string = string.toLowerCase();
	var characters = characters.toLowerCase();
	var array = characters.split('|:|');

	for(x in array)
	{
		if(string.indexOf(array[x]) != -1)
		{
			var has_characters = true;
		}
	}

	if(typeof has_characters != 'undefined')
	{
		return true;
	}
	else
	{
		return false;
	}
}

function isLocalFile(uri)
{
	if(uri == 'local' || hasCharacters(uri, 'spotify:local:'))
	{
		return true;
	}
	else
	{
		return false;
	}
}

// Cookies

function setCookie(cookie_name, cookie_value, cookie_days)
{
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + cookie_days);
	var c_value=escape(cookie_value) + ((cookie_days==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=cookie_name + "=" + c_value;
}

function getCookie(cookie_name)
{
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++)
	{
		x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
		y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
		x=x.replace(/^\s+|\s+$/g,"");

		if (x==cookie_name)
		{
			return unescape(y);
		}
	}
}

function deleteAllCookies()
{
	pageLoading(1);

	var cookies = document.cookie.split(";");
	for (var i = 0; i < cookies.length; i++)
	{
		var cookie = cookies[i];
		var eqPos = cookie.indexOf("=");
		var cookie_name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
		document.cookie = cookie_name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
	}

	setTimeout(function() { window.location.replace('.'); }, 1000);
}

#!/bin/bash

# Copyright 2012 Ole Jon Bjørkum
# 
# This file is part of SpotCommander.
# 
# SpotCommander is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.

# SpotCommander is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with SpotCommander.  If not, see <http://www.gnu.org/licenses/>.

while inotifywait -e modify $SPOTCOMMANDER_PATH/sh/spotcommander-remote.txt; do

	CMD="$(cat "$SPOTCOMMANDER_PATH/sh/spotcommander-remote.txt")"

	if [ "$CMD" == "spotify-launch" ]; then

		spotify &

	elif [ "$CMD" == "spotify-quit" ]; then

		qdbus org.mpris.MediaPlayer2.spotify / org.freedesktop.MediaPlayer2.Quit

	elif [ "$CMD" == "play-pause" ]; then

		qdbus org.mpris.MediaPlayer2.spotify / org.freedesktop.MediaPlayer2.PlayPause

	elif [ "$CMD" == "previous" ]; then

		qdbus org.mpris.MediaPlayer2.spotify / org.freedesktop.MediaPlayer2.Previous

	elif [ "$CMD" == "next" ]; then

		qdbus org.mpris.MediaPlayer2.spotify / org.freedesktop.MediaPlayer2.Next

	elif [ "$CMD" == "up" ]; then

		xte 'key Up'

	elif [ "$CMD" == "down" ]; then

		xte 'key Down'

	elif [ "$CMD" == "tab" ]; then

		$SPOTCOMMANDER_PATH/sh/spotify-focus.sh

		sleep 0.5

		xte 'key Tab'

	elif [ "$CMD" == "return" ]; then

		xte 'key Return'

	elif [ "$CMD" == "volume" ]; then

		VOLUME="$(cat "$SPOTCOMMANDER_PATH/sh/volume.txt")"

		if [[ "$VOLUME" =~ ^[0-9]+$ ]] ; then

			if(("$VOLUME" > 65537)); then

				VOLUME=65537

			fi

			INDEX="$(pacmd list-sink-inputs | sed -e '/index:/b' -e '/application.name/b' -e d | sed -n '/application.name = "Spotify"/{g;1!p;};h' | sed 's/index: //;s/^[ \t]*//')"
			pacmd set-sink-input-volume "$INDEX" "$VOLUME"

		fi

	elif [ "$CMD" == "play-uri" ]; then

		URI="$(cat $SPOTCOMMANDER_PATH/sh/uri.txt)"
		qdbus org.mpris.MediaPlayer2.spotify / org.freedesktop.MediaPlayer2.OpenUri $URI

	elif [ "$CMD" == "metadata-get" ]; then

		qdbus org.mpris.MediaPlayer2.spotify / org.freedesktop.MediaPlayer2.GetMetadata > $SPOTCOMMANDER_PATH/sh/metadata-all.txt
		qdbus org.mpris.MediaPlayer2.spotify /org/mpris/MediaPlayer2 org.freedesktop.DBus.Properties.Get org.mpris.MediaPlayer2.Player PlaybackStatus > $SPOTCOMMANDER_PATH/sh/playbackstatus.txt
		cat $SPOTCOMMANDER_PATH/sh/metadata-all.txt|grep 'xesam:artist: '|sed 's/xesam:artist: //' > $SPOTCOMMANDER_PATH/sh/metadata-artist.txt
		cat $SPOTCOMMANDER_PATH/sh/metadata-all.txt|grep 'xesam:title: '|sed 's/xesam:title: //' > $SPOTCOMMANDER_PATH/sh/metadata-title.txt
		cat $SPOTCOMMANDER_PATH/sh/metadata-all.txt|grep 'xesam:album: '|sed 's/xesam:album: //' > $SPOTCOMMANDER_PATH/sh/metadata-album.txt
		cat $SPOTCOMMANDER_PATH/sh/metadata-all.txt|grep 'mpris:artUrl: '|sed 's/mpris:artUrl: //' > $SPOTCOMMANDER_PATH/sh/metadata-albumart.txt
		cat $SPOTCOMMANDER_PATH/sh/metadata-all.txt|grep 'xesam:url: '|sed 's/xesam:url: //' > $SPOTCOMMANDER_PATH/sh/metadata-uri.txt
		cat $SPOTCOMMANDER_PATH/sh/metadata-all.txt|grep 'mpris:length: '|sed 's/mpris:length: //' > $SPOTCOMMANDER_PATH/sh/metadata-length.txt
		cat $SPOTCOMMANDER_PATH/sh/metadata-all.txt|grep 'xesam:contentCreated: '|sed 's/xesam:contentCreated: //' > $SPOTCOMMANDER_PATH/sh/metadata-year.txt

	elif [ "$CMD" == "toggle-shuffle" ]; then

		$SPOTCOMMANDER_PATH/sh/spotify-focus.sh

		sleep 0.5

		xte 'keydown Control_L' 'key S' 'keyup Control_L'

	elif [ "$CMD" == "toggle-repeat" ]; then

		$SPOTCOMMANDER_PATH/sh/spotify-focus.sh

		sleep 0.5

		xte 'keydown Control_L' 'key R' 'keyup Control_L'

	elif [ "$CMD" == "focus" ]; then

		$SPOTCOMMANDER_PATH/sh/spotify-focus.sh

	fi

done

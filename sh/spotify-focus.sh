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

qdbus org.mpris.MediaPlayer2.spotify / org.freedesktop.MediaPlayer2.Raise

if wmctrl -l | grep "Spotify Premium - Linux Preview"; then

	wmctrl -a "Spotify Premium - Linux Preview"

elif wmctrl -l | grep "Spotify - Linux Preview"; then

	wmctrl -a "Spotify - Linux Preview"

fi

sleep 0.5


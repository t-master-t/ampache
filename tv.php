<?php
/*

 Copyright (c) 2001 - 2006 Ampache.org
 All rights reserved.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/

require_once('lib/init.php');


$dbh = dbh();
$web_path = conf('web_path');

/* Make sure they have access to this */
if (!conf('allow_democratic_playback') || $GLOBALS['user']->prefs['play_type'] != 'democratic') { 
	access_denied(); 
	exit;
}

/* Attempt to build the temp playlist */
$playlist	= new tmpPlaylist('-1'); 
$action 	= scrub_in($_REQUEST['action']);


switch ($action) { 
	case 'create_playlist':

	break;
	default: 

		require_once(conf('prefix') . '/templates/show_tv.inc.php');

	break;
} // end switch on action


?>

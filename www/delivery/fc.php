<?php

/*
+---------------------------------------------------------------------------+
| Max Media Manager v0.3                                                    |
| =================                                                         |
|                                                                           |
| Copyright (c) 2003-2006 m3 Media Services Limited                         |
| For contact details, see: http://www.m3.net/                              |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id: fc.php 5698 2006-10-12 16:16:22Z chris@m3.net $
*/

/**
 * Front controller for invocationTags plugins
 *
 * Thanks to this file you could create the invocationTag plugin and put all the files
 * only under plugins folder.
 *
 * Create the invocation tag plugin. Put delivery code into yourTagName.delivery.php file
 * in plugins/invocationTags/yourTagName folder
 * To run this file point invocation code to
 * http://serverName/delivery/fc.php?MMM_tagName=yourTagName&andAnyOtherCustomOptions=someValues
 *
 */

// Require the initialisation file
require_once '../../init-delivery.php';

/**
 * Invocation tag (plugin) name
 */
define('MAX_PLUGINS_AD_PLUGIN_NAME', 'MAX_type');

if(!isset($_GET[MAX_PLUGINS_AD_PLUGIN_NAME])) {
    include_once MAX_PATH . '/lib/Max.php';
    MAX::raiseError(MAX_PLUGINS_AD_PLUGIN_NAME . ' is not specified', MAX_ERROR_NODATA, PEAR_ERROR_DIE);
}

$tagName = $_GET[MAX_PLUGINS_AD_PLUGIN_NAME];
$tagFileName = MAX_PATH . '/plugins/invocationTags/'.$tagName.'/'.$tagName.'.delivery.php';

if(!file_exists($tagFileName)) {
    include_once MAX_PATH . '/lib/Max.php';
    MAX::raiseError('Invocation plugin delivery file "' . $tagFileName . '" doesn\'t exists', MAX_ERROR_NOFILE, PEAR_ERROR_DIE);
}

// include plugin specific delivery script
// (we are not using MAX_Plugin interface for it because it has to be as fast as possible)
include_once $tagFileName;


// stop benchmarking
MAX_benchmarkStop();

?>
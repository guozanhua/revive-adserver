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
$Id: atlas.plugin.php 6108 2006-11-24 11:34:58Z andrew@m3.net $
*/

/**
 * @package    MaxPlugin
 * @subpackage 3rdPartyServers
 * @author     Radek Maciaszek <radek@m3.net>
 *
 */

require_once MAX_PATH . '/plugins/3rdPartyServers/3rdPartyServers.php';
require_once MAX_PATH . '/lib/max/Plugin/Translation.php';

/**
 *
 * 3rdPartyServer plugin. Allow for generating different banner html cache
 *
 * @static
 */
class Plugins_3rdPartyServers_atlas_atlas extends Plugins_3rdPartyServers
{

    /**
     * Return the name of plugin
     *
     * @return string
     */
    function getName()
    {
        include_once MAX_PATH . '/lib/max/Plugin/Translation.php';
        MAX_Plugin_Translation::init($this->module, $this->package);

        return MAX_Plugin_Translation::translate('Rich Media - Atlas', $this->module, $this->package);
    }

    /**
     * Return plugin cache
     *
     * @return string
     */
    function getBannerCache($buffer, &$noScript)
    {
        $search  = array("/\[timestamp\]/i","/(http:.*?direct\/01)?click=(.*?)/i");
        $replace = array("{random}",     "$1click={clickurl}$2");

        $buffer = preg_replace ($search, $replace, $buffer);
        $noScript[0] = preg_replace($search[0], $replace[0], $noScript[0]);

        return $buffer;
    }

}

?>
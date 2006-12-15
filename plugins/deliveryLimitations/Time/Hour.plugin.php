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
$Id: Hour.plugin.php 6131 2006-11-29 11:57:30Z andrew@m3.net $
*/

require_once MAX_PATH . '/plugins/deliveryLimitations/Time/AbstractTimePlugin.php';
require_once MAX_PATH . '/lib/max/Plugin/Translation.php';

/**
 * A Time delivery limitation plugin, for blocking delivery of ads on the basis
 * of the hour of the day.
 *
 * Works with:
 * A comma separated list of numbers, in the range 0 - 23, representing the
 * hours of the day.
 *
 * Valid comparison operators:
 * =~, !~
 *
 * @package    MaxPlugin
 * @subpackage DeliveryLimitations
 * @author     Andrew Hill <andrew@m3.net>
 * @author     Chris Nutting <chris@m3.net>
 * @author     Andrzej Swedrzynski <andrzej.swedrzynski@m3.net>
 */
class Plugins_DeliveryLimitations_Time_Hour extends Plugins_DeliveryLimitations_AbstractTimePlugin
{

    /**
     * Calls the parent class constructor with values of 0 and 23.
     *
     * @return Plugins_DeliveryLimitations_Time_Hour
     */
    function Plugins_DeliveryLimitations_Time_Hour()
    {
        $this->Plugins_DeliveryLimitations_Time_Base(0, 23);
    }

    /**
     * Return name of plugin
     *
     * @return string
     */
    function getName()
    {
        return MAX_Plugin_Translation::translate('Hour of day', $this->module, $this->package);
    }

    /**
     * Return if this plugin is available in the current context
     *
     * @return boolean
     */
    function isAllowed($page = false)
    {
        return ($page != 'channel-acl.php');
    }

    /**
     * Outputs the HTML to display the data for this limitation
     *
     * @return void
     */
    function displayArrayData()
    {
        $tabindex =& $GLOBALS['tabindex'];
		echo "<table width='500' cellpadding='0' cellspacing='0' border='0'>";
		for ($i = 0; $i < 24; $i++)
		{
			if ($i % 4 == 0) echo "<tr>";
			echo "<td><input type='checkbox' name='acl[{$this->executionorder}][data][]' value='$i'".(in_array($i, $this->data) ? ' CHECKED' : '')." tabindex='".($tabindex++)."'>&nbsp;{$i}:00-{$i}:59&nbsp;&nbsp;</td>";
			if (($i + 1) % 4 == 0) echo "</tr>";
		}
		if (($i + 1) % 4 != 0) echo "</tr>";
		echo "</table>";
    }

    /**
     * A private method to return this delivery limitation plugin as a SQL limiation.
     *
     * @access private
     * @param string $comparison As for Plugins_DeliveryLimitations::_getSqlLimitation(),
     *                           but only '=~' and '!~' permitted.
     * @param string $data A comma separated list of hours of the day.
     * @return mixed As for Plugins_DeliveryLimitations::_getSqlLimitation().
     *
     * @TODO Needs to be changed to deal with databases other than MySQL.
     */
    function _getSqlLimitation($comparison, $data)
    {
        return $this->_getSqlLimitationForArray($comparison, $data, 'HOUR(date_time)');
    }

}

?>

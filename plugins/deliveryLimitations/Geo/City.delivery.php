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
$Id: City.delivery.php 6131 2006-11-29 11:57:30Z andrew@m3.net $
*/

/**
 * @package    MaxPlugin
 * @subpackage DeliveryLimitations
 * @author     Chris Nutting <chris@m3.net>
 * @author     Andrzej Swedrzynski <andrzej.swedrzynski@m3.net>
 */

require_once MAX_PATH . '/lib/max/Delivery/limitations.delivery.php';

/**
 * Check to see if this impression contains the valid city.
 *
 * @param string $limitation The city (or comma list of cities) limitation
 * @param string $op The operator (either '==' or '!=')
 * @param array $aParams An array of additional parameters to be checked
 * @return boolean Whether this impression's city passes this limitation's test.
 */
function MAX_checkGeo_City($limitation, $op, $aParams = array())
{
    if (empty($aParams)) {
        $aParams = $GLOBALS['_MAX']['CLIENT_GEO'];
    }
    if ($aParams && $aParams['city'] && $aParams['country_code']) {
        $aLimitation = MAX_limitationsGeoCityUnserialize($limitation);
        $sCities = MAX_limitationsGetSCities($aLimitation);
        return MAX_limitationsMatchStringValue(
                $aParams['country_code'], $aLimitation[0], '==')
            && MAX_limitationsMatchArrayValue(
                $aParams['city'], $sCities, '=~');
    } else {
        return false; // If client has no data about city, do not show the ad
    }
}

/* City delivery limitation plugin utility functions */

function MAX_limitationsGeoCitySerialize($aCityLimitation)
{
    $sCountry = MAX_limitationsGetCountry($aCityLimitation);
    $sCities = MAX_limitationsGetSCities($aCityLimitation);
    return $sCountry . '|' . $sCities;
}

function MAX_limitationsGeoCityUnserialize($sCityLimitation)
{
    return explode('|', $sCityLimitation);
}


function MAX_limitationsGetSCities($aData)
{
    return $aData[1];
}


function MAX_limitationsSetSCities(&$aData, $sCities)
{
    $aData[1] = $sCities;
}

?>

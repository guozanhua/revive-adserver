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
$Id: ZoneIdField.php 4568 2006-04-04 12:40:13Z roh@m3.net $
*/

require_once MAX_PATH . '/lib/max/Dal/Reporting.php';
require_once MAX_PATH . '/lib/max/Admin/UI/Field.php';

class Admin_UI_ZoneIdField extends Admin_UI_Field
{
    function display()
    {
        echo "
        <select name='{$this->_name}' tabindex='".($this->_tabIndex++)."'>";
        $this->displayZonesAsOptionList();
        echo "
        </select>";
    }

    function getZones()
    {
        global $list_filters;
    
        if (phpAds_isUser(phpAds_Admin)) {
            $aParams = array();
            $aPublishers = Admin_DA::getPublishers($aParams);
            // set publisher id if list is to be filtered by publisher
            if (isset($list_filters['publisher'])) {
                $aParams = array('publisher_id' => $list_filters['publisher']);
            } else { // else use all publishers
                $aParams = array('publisher_id' => implode(',',array_keys($aPublishers)));
            }
            if (isset($this->_filter)) {
                $aParams['zone_inventory_forecast_type'] = $this->getForecastType();
            }
            $aZones = Admin_DA::getZones($aParams);
        } elseif (phpAds_isUser(phpAds_Agency)) {
            $aParams = array('agency_id' => phpAds_getUserID());
            $aPublishers = Admin_DA::getPublishers($aParams);
            // set publisher id if list is to be filtered by publisher
            if (isset($list_filters['publisher'])) {
                $aParams = array('publisher_id' => $list_filters['publisher']);
            } else { // else use all of this agency's publishers
                $aParams = array('publisher_id' => implode(',',array_keys($aPublishers)));
            }
            if (isset($this->_filter)) {
                $aParams['zone_inventory_forecast_type'] = $this->getForecastType();
            }
            $aZones = Admin_DA::getZones($aParams);
        } elseif (phpAds_isUser(phpAds_Publisher)) {
            $aParams = array('publisher_id' => phpAds_getUserID());
            $aPublishers = Admin_DA::getPublishers($aParams);
            $aParams = array('publisher_id' => implode(',',array_keys($aPublishers)));
            if (isset($this->_filter)) {
                $aParams['zone_inventory_forecast_type'] = $this->getForecastType();
            }
            $aZones = Admin_DA::getZones($aParams);
        } else {
            $aPublishers = array();
            $aZones = array();
        }

        $aZoneArray = array();
        foreach ($aPublishers as $publisherId => $aPublisher) {
            foreach ($aZones as $zoneId => $aZone) {
                if ($aZone['publisher_id'] == $publisherId) {
                    $aZoneArray[$zoneId] = "[$publisherId]" . MAX_getPublisherName($aPublisher['name']) . " - [$zoneId]" . MAX_getZoneName($aZone['name']);
                }
            }
        }

        return $aZoneArray;
    }

    function getForecastType () 
    {
        switch ($this->_filter) {
        case FILTER_ZONE_INVENTORY_DOMAIN_PAGE_INDEXED :
            return 1;
            break;
        case FILTER_ZONE_INVENTORY_COUNTRY_INDEXED :
            return 2;
            break;
        case FILTER_ZONE_INVENTORY_SOURCE_INDEXED :
            return 4;
            break;
        case FILTER_ZONE_INVENTORY_CHANNEL_INDEXED :
            return 8;
            break;
        }
    }

    function displayZonesAsOptionList()
    {
        $aZones = $this->getZones();
        foreach ($aZones as $zoneId => $aZone) {
            $selected = $zoneId == $this->getValue() ? " selected='selected'" : '';
            echo "<option value='$zoneId'$selected>$aZone</option>";
        }
    }
}

?>

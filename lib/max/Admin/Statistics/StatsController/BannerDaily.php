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
$Id: BannerDaily.php 5724 2006-10-16 06:03:30Z arlen $
*/

require_once MAX_PATH . '/lib/max/Admin/Statistics/StatsDailyController.php';



class StatsBannerDailyController extends StatsDailyController
{
    function start()
    {
        // Get the preferences
        $pref = $GLOBALS['_MAX']['PREF'];

        // Get parameters
        if (phpAds_isUser(phpAds_Client)) {
            $advertiserId = phpAds_getUserId();
        } else {
            $advertiserId = (int)MAX_getValue('clientid', '');
        }
        $placementId  = (int)MAX_getValue('campaignid', '');
        $adId         = (int)MAX_getValue('bannerid', '');

        // Cross-entity
        $publisherId = (int)MAX_getValue('affiliateid', '');
        $zoneId      = (int)MAX_getValue('zoneid', '');

        // Security check
        phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);
        if (!MAX_checkAd($advertiserId, $placementId, $adId)) {
            phpAds_PageHeader('2');
            phpAds_Die ($GLOBALS['strAccessDenied'], $GLOBALS['strNotAdmin']);
        }

        if (!empty($zoneId)) {
            // Fetch banners
            $aZones = $this->getCampaignZones($placementId);

            // Cross-entity security check
            if (!isset($aZones[$zoneId])) {
                $this->noStatsAvailable = true;
            }
        } elseif (!empty($publisherId)) {
            // Fetch campaigns
            $aPublishers = $this->getCampaignPublishers($placementId);

            // Cross-entity security check
            if (!isset($aPublishers[$publisherId])) {
                $this->noStatsAvailable = true;
            }
        }

        // HTML Framework
        if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency)) {
            if (empty($publisherId) && empty($zoneId)) {
                $this->pageId = '2.1.2.2.1.1';
            } else {
                // Cross-entity
                $this->pageId = empty($zoneId) ? '2.1.2.2.3.1.1' : '2.1.2.2.3.2.1';
            }
            $this->pageSections = array($this->pageId);
        } elseif (phpAds_isUser(phpAds_Client)) {
            if (empty($publisherId) && empty($zoneId)) {
                $this->pageId = '1.2.2.1.1';
            } else {
                // Cross-entity
                $this->pageId = empty($zoneId) ? '1.2.2.4.1.1' : '1.2.2.4.2.1';
            }
            $this->pageSections = array($this->pageId);
        }

        // Add standard page parameters
        $this->pageParams = array('clientid' => $advertiserId, 'campaignid' => $placementId,
                                  'entity' => 'campaign', 'breakdown' => 'daily',
                                  'campaignid' => $placementId, 'bannerid' => $adId,
                                  'day' => MAX_getValue('day', ''),
                                  'period_preset' => MAX_getStoredValue('period_preset', 'today')
                                 );
        $this->pageParams['statsBreakdown'] = MAX_getStoredValue('statsBreakdown', 'day');


        // Cross-entity
        if (!empty($zoneId)) {
            $this->pageParams['affiliateid'] = $aZones[$zoneId]['publisher_id'];
            $this->pageParams['zoneid']      = $zoneId;
        } elseif (!empty($publisherId)) {
            $this->pageParams['affiliateid'] = $publisherId;
        }

        $this->loadParams();

        $this->addBreadcrumbs('banner', $adId);

        // Cross-entity
        if (!empty($zoneId)) {
            $this->addCrossBreadcrumbs('zone', $zoneId);
        } elseif (!empty($publisherId)) {
            $this->addCrossBreadcrumbs('publisher', $publisherId);
        }

        // Add shortcuts
        if (!phpAds_isUser(phpAds_Client)) {
            $this->addShortcut(
                $GLOBALS['strClientProperties'],
                'advertiser-edit.php?clientid='.$advertiserId,
                'images/icon-advertiser.gif'
            );
        }
        $this->addShortcut(
            $GLOBALS['strCampaignProperties'],
            'campaign-edit.php?clientid='.$advertiserId.'&campaignid='.$placementId,
            'images/icon-campaign.gif'
        );
        $this->addShortcut(
            $GLOBALS['strBannerProperties'],
            'banner-edit.php?clientid='.$advertiserId.'&campaignid='.$placementId.'&bannerid='.$adId,
            'images/icon-banner-stored.gif'
        );
        $this->addShortcut(
            $GLOBALS['strModifyBannerAcl'],
            'banner-acl.php?clientid='.$advertiserId.'&campaignid='.$placementId.'&bannerid='.$adId,
            'images/icon-acl.gif'
        );

        $aParams = array();
        $aParams['ad_id'] = $adId;

        // Cross-entity
        if (!empty($zoneId)) {
            $aParams['zone_id'] = $zoneId;
        } elseif (!empty($publisherId)) {
            $aParams['publisher_id'] = $publisherId;
        }

        $this->prepareHistory($aParams);
    }
}

?>

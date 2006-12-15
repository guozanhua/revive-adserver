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
$Id: MaintenanceStatisticsAdServer.mts.test.php 4614 2006-04-05 17:04:20Z andrew@m3.net $
*/

require_once MAX_PATH . '/lib/max/core/ServiceLocator.php';
require_once MAX_PATH . '/lib/Max.php';
require_once MAX_PATH . '/lib/max/DB.php';
require_once MAX_PATH . '/lib/max/Maintenance/Statistics/AdServer.php';
require_once 'Date.php';

/**
 * A class for performing integration testing the MAX_Maintenance_Statistics_AdServer class.
 *
 * @package    MaxMaintenance
 * @subpackage TestSuite
 * @author     Andrew Hill <andrew@m3.net>
 * @TODO Update to use a mocked DAL, instead of a real database.
 */
class Maintenance_TestOfMaintenanceStatisticsAdServer extends UnitTestCase
{

    /**
     * The constructor method.
     */
    function Maintenance_TestOfMaintenanceStatisticsAdServer()
    {
        $this->UnitTestCase();
    }

    /**
     * The main test method.
     */
    function testClass()
    {
        // Use a reference to $GLOBALS['_MAX']['CONF'] so that the configuration
        // options can be changed while the test is running
        $conf = &$GLOBALS['_MAX']['CONF'];
        $conf['table']['prefix'] = 'max_';
        $dbh = &MAX_DB::singleton();
        $tables = MAX_Table_Core::singleton($conf['database']['type'], true);
        // Create the required tables
        $tables->createTable('banners');
        $tables->createTable('campaigns');
        $tables->createTable('campaigns_trackers');
        $tables->createTable('clients');
        $tables->createTable('data_intermediate_ad');
        $tables->createTable('data_intermediate_ad_connection');
        $tables->createTable('data_intermediate_ad_variable_value');
        $tables->createTable('data_raw_ad_click');
        $tables->createTable('data_raw_ad_impression');
        $tables->createTable('data_raw_ad_request');
        $tables->createTable('data_raw_tracker_impression');
        $tables->createTable('data_raw_tracker_variable_value');
        $tables->createTable('data_summary_ad_hourly');
        $tables->createTable('data_summary_zone_impression_history');
        $tables->createTable('log_maintenance_statistics');
        $tables->createTable('trackers');
        $tables->createTable('userlog');
        $tables->createTable('variables');
        $tables->createTable('zones');
        // Get the data for the tests
        include_once MAX_PATH . '/lib/max/Maintenance/data/TestOfMaintenanceStatisticsAdServer.php';
        // Insert the test data
        $result = $dbh->query(ADSERVER_FULL_TEST_BANNERS);
        $result = $dbh->query(ADSERVER_FULL_TEST_CAMPAIGNS);
        $result = $dbh->query(ADSERVER_FULL_TEST_CAMPAIGNS_TRACKERS);
        $result = $dbh->query(ADSERVER_FULL_TEST_CLIENTS);
        $result = $dbh->query(ADSERVER_FULL_TEST_AD_IMPRESSIONS);
        $result = $dbh->query(ADSERVER_FULL_TEST_AD_REQUESTS);
        $result = $dbh->query(ADSERVER_FULL_TEST_TRACKER_IMPRESSIONS);
        $result = $dbh->query(ADSERVER_FULL_TEST_TRACKERS);
        $result = $dbh->query(ADSERVER_FULL_TEST_ZONES);
        // Set up the config as desired for testing
        $conf['maintenance']['operationInterval'] = 60;
        $conf['maintenance']['compactStats'] = false;
        $conf['modules']['Tracker'] = true;
        $conf['table']['split'] = false;
        // Set the "current" time
        $oDateNow = new Date('2004-11-28 12:00:00');
        $oServiceLocator = &ServiceLocator::instance();
        $oServiceLocator->register('now', $oDateNow);
        // Create and run the class
        $oMaintenanceStatistics = new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->updateStatistics();
        // Test the results
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                {$conf['table']['prefix']}{$conf['table']['data_intermediate_ad_connection']}";
        $row = $dbh->getRow($query);
        $this->assertEqual($row['number'], 1);
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                {$conf['table']['prefix']}{$conf['table']['data_intermediate_ad_variable_value']}";
        $row = $dbh->getRow($query);
        $this->assertEqual($row['number'], 0);
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                {$conf['table']['prefix']}{$conf['table']['data_intermediate_ad']}";
        $row = $dbh->getRow($query);
        $this->assertEqual($row['number'], 6);
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                {$conf['table']['prefix']}{$conf['table']['data_summary_ad_hourly']}";
        $row = $dbh->getRow($query);
        $this->assertEqual($row['number'], 6);
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                {$conf['table']['prefix']}{$conf['table']['data_summary_zone_impression_history']}";
        $row = $dbh->getRow($query);
        $this->assertEqual($row['number'], 2);
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                {$conf['table']['prefix']}{$conf['table']['data_raw_ad_click']}";
        $row = $dbh->getRow($query);
        $this->assertEqual($row['number'], 0);
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $row = $dbh->getRow($query);
        $this->assertEqual($row['number'], 30);
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                {$conf['table']['prefix']}{$conf['table']['data_raw_ad_request']}";
        $row = $dbh->getRow($query);
        $this->assertEqual($row['number'], 30);
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                {$conf['table']['prefix']}{$conf['table']['data_raw_tracker_impression']}";
        $row = $dbh->getRow($query);
        $this->assertEqual($row['number'], 1);
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                {$conf['table']['prefix']}{$conf['table']['data_raw_tracker_variable_value']}";
        $row = $dbh->getRow($query);
        $this->assertEqual($row['number'], 0);
        // Reset the testing environment
        TestEnv::restoreEnv();
    }

}

?>

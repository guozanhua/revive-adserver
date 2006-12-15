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
$Id: StatisticsCommonTaskSetUpdateRequirements.mtsdb.test.php 5631 2006-10-09 18:21:43Z andrew@m3.net $
*/

require_once MAX_PATH . '/lib/max/core/ServiceLocator.php';
require_once MAX_PATH . '/lib/max/Maintenance/Statistics/AdServer.php';
require_once MAX_PATH . '/lib/max/Maintenance/Statistics/Common/Task/SetUpdateRequirements.php';

/**
 * A class for testing the MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements class.
 *
 * @package    MaxMaintenance
 * @subpackage TestSuite
 * @author     Andrew Hill <andrew@m3.net>
 */
class Maintenance_TestOfMAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements extends UnitTestCase
{

    /**
     * The constructor method.
     */
    function Maintenance_TestOfMAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements()
    {
        $this->UnitTestCase();
    }

    /**
     * Test the creation of the class.
     */
    function testCreate()
    {
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $this->assertTrue(is_a($oSetUpdateRequirements, 'MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements'));
    }

    /**
     * A method to test the run() method.
     */
    function testRun()
    {
        // Use a reference to $GLOBALS['_MAX']['CONF'] so that the configuration
        // options can be changed while the test is running
        $conf = &$GLOBALS['_MAX']['CONF'];
        $dbh = &MAX_DB::singleton();
        $oServiceLocator = &ServiceLocator::instance();
        $tables = MAX_Table_Core::singleton();
        // Create the required tables
        $tables->createTable('data_raw_ad_impression');
        $tables->createTable('log_maintenance_statistics');
        // Create a Maintenance_Statistics_Common object with no data,
        // and an operation interval less than 60
        $conf['maintenance']['operationInterval'] = 30;
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Create a Maintenance_Statistics_Common object with no data,
        // and an operation interval greater than 60
        $conf['maintenance']['operationInterval'] = 120;
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        /*----------------------------------------------------------*/
        /* Run tests relating to a 30 minute operation interval     */
        /*----------------------------------------------------------*/
        $conf['maintenance']['operationInterval'] = 30;
        // Insert ad impression data 5 minutes ago, in current hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 18:05:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        // Insert ad impression data 20 minutes ago, in previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 17:50:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        // Insert ad impression data 20 minutes ago, in current hour
        // and current operation interval
        $now = new Date('2004-06-06 18:25:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 18:05:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        // Insert ad impression data 20 minutes ago, in current hour
        // and previous operation interval
        $now = new Date('2004-06-06 18:45:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 18:25:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        // Insert ad impression data 39 minutes ago, in previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 17:31:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        // Insert ad impression data 39 minutes ago, in current hour
        // and previous operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 18:01:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        // Insert ad impression data 61 minutes ago, in previous hour
        // and previous operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 17:39:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        /*----------------------------------------------------------*/
        /* Run tests relating to a 120 minute operation interval    */
        /*----------------------------------------------------------*/
        $conf['maintenance']['operationInterval'] = 120;
        // Insert ad impression data 5 minutes ago, in current hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 18:05:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        // Insert ad impression data 20 minutes ago, in previous hour
        // and current operation interval
        $now = new Date('2004-06-06 19:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 18:50:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        // Insert ad impression data 20 minutes ago, in previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 17:50:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        // Insert ad impression data 20 minutes ago, in current hour
        // and operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 18:20:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        // Insert ad impression data 61 minutes ago, in previous hour
        // and current operation interval
        $now = new Date('2004-06-06 19:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 18:39:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        // Insert ad impression data 61 minutes ago, in previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 17:39:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        // Insert ad impression data 121 minutes ago, in previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}
                (date_time)
            VALUES
                ('2004-06-06 16:39:00')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the ad impression
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['data_raw_ad_impression']}";
        $result = $dbh->query($query);
        /*----------------------------------------------------------*/
        /* Run tests relating to a 30 minute operation interval     */
        /*----------------------------------------------------------*/
        $conf['maintenance']['operationInterval'] = 30;
        // Insert OI stats run 5 minutes ago, up to current hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:05:00', 0, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert hourly stats run 5 minutes ago, up to current hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:05:00', 0, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 20 minutes ago, up to  previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 17:50:00', 0, '2004-06-06 17:29:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert hourly stats run 20 minutes ago, up to previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 17:50:00', 1, '2004-06-06 16:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 20 minutes ago, up to current hour
        // and current operation interval
        $now = new Date('2004-06-06 18:25:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:05:00', 0, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert hourly stats run 20 minutes ago, up to current hour
        // and current operation interval
        $now = new Date('2004-06-06 18:25:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:05:00', 1, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 20 minutes ago, up to current hour
        // and previous operation interval
        $now = new Date('2004-06-06 18:45:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:25:00', 0, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 20 minutes ago, up to current hour
        // and previous operation interval
        $now = new Date('2004-06-06 18:45:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:25:00', 1, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run data 39 minutes ago, up to previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 17:31:00', 0, '2004-06-06 17:29:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run data 39 minutes ago, up to previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 17:31:00', 1, '2004-06-06 16:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 39 minutes ago, up to current hour
        // and previous operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:01:00', 0, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert hourly stats run 39 minutes ago, up to current hour
        // and previous operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:01:00', 1, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 61 minutes ago, up to previous hour
        // and previous operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 17:39:00', 0, '2004-06-06 17:29:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 61 minutes ago, in previous hour
        // and previous operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 17:39:00', 1, '2004-06-06 16:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertTrue($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        /*----------------------------------------------------------*/
        /* Run tests relating to a 120 minute operation interval    */
        /*----------------------------------------------------------*/
        $conf['maintenance']['operationInterval'] = 120;
        // Insert OI stats run 5 minutes ago, up to current hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:05:00', 0, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert hourly stats run 5 minutes ago, up to current hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:05:00', 1, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 20 minutes ago, up to previous hour
        // and current operation interval
        $now = new Date('2004-06-06 19:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:50:00', 0, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 20 minutes ago, up to previous hour
        // and current operation interval
        $now = new Date('2004-06-06 19:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:50:00', 1, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 20 minutes ago, up to previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 17:50:00', 0, '2004-06-06 15:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert hourly stats run 20 minutes ago, up to previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 17:50:00', 1, '2004-06-06 16:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 20 minutes ago, up to current hour
        // and operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:20:00', 0, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert hourly stats run 20 minutes ago, up to current hour
        // and operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:20:00', 1, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 61 minutes ago, up to previous hour
        // and current operation interval
        $now = new Date('2004-06-06 19:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:39:00', 0, '17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert hourly stats run 61 minutes ago, up to previous hour
        // and current operation interval
        $now = new Date('2004-06-06 19:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 18:39:00', 1, '2004-06-06 17:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 61 minutes ago, up to previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 17:39:00', 0, '2004-06-06 15:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert hourly stats run 61 minutes ago, in previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 17:39:00', 1, '2004-06-06 16:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert OI stats run 121 minutes ago, up to previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 16:39:00', 0, '2004-06-06 15:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertTrue($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertFalse($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Insert hourly stats run 121 minutes ago, in previous hour
        // and operation interval
        $now = new Date('2004-06-06 18:40:00');
        $oServiceLocator->register('now', $now);
        $query = "INSERT INTO
            {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}
                (start_run, adserver_run_type, updated_to)
            VALUES
                ('2004-06-06 16:39:00', 1, '2004-06-06 15:59:59')";
        $result = $dbh->query($query);
        // Create and register a new MAX_Maintenance_Statistics_AdServer object
        $oMaintenanceStatistics = &new MAX_Maintenance_Statistics_AdServer();
        $oMaintenanceStatistics->module = 'AdServer';
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements
        // object, and set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Common_Task_SetUpdateRequirements();
        $oSetUpdateRequirements->run();
        $this->assertFalse($oSetUpdateRequirements->oController->updateUsingOI);
        $this->assertFalse($oSetUpdateRequirements->oController->updateIntermediate);
        $this->assertTrue($oSetUpdateRequirements->oController->updateFinal);
        // Delete the run log information
        $query = "DELETE FROM {$conf['table']['prefix']}{$conf['table']['log_maintenance_statistics']}";
        $result = $dbh->query($query);
        // Reset the testing environment
        TestEnv::restoreEnv();
    }

}

?>

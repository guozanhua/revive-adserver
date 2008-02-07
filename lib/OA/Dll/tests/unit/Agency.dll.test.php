<?php

/*
+---------------------------------------------------------------------------+
| OpenX v${RELEASE_MAJOR_MINOR}                                                               |
| =======${RELEASE_MAJOR_MINOR_DOUBLE_UNDERLINE}                                                                |
|                                                                           |
| Copyright (c) 2003-2008 m3 Media Services Ltd                             |
| For contact details, see: http://www.openx.org/                           |
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
$Id:$
*/

require_once MAX_PATH . '/lib/OA/Dll/Agency.php';
require_once MAX_PATH . '/lib/OA/Dll/AgencyInfo.php';
require_once MAX_PATH . '/lib/OA/Dll/tests/util/DllUnitTestCase.php';

/**
 * A class for testing DLL Agency methods
 *
 * @package    OpenXDll
 * @subpackage TestSuite
 * @author     Andriy Petlyovanyy <apetlyovanyy@lohika.com>
 *
 */


class OA_Dll_AgencyTest extends DllUnitTestCase
{

    /**
     * Errors
     *
     */
    var $unknownIdError = 'Unknown agencyId Error';

    /**
     * The constructor method.
     */
    function OA_Dll_AgencyTest()
    {
        $this->UnitTestCase();
        Mock::generatePartial(
            'OA_Dll_Agency',
            'PartialMockOA_Dll_Agency',
            array('checkPermissions')
        );
    }

    function tearDown()
    {
        DataGenerator::cleanUp();
    }

    /**
     * A method to test Add, Modify and Delete.
     */
    function testAddModifyDelete()
    {
        $dllAgencyPartialMock = new PartialMockOA_Dll_Agency($this);

        $dllAgencyPartialMock->setReturnValue('checkPermissions', true);
        $dllAgencyPartialMock->expectCallCount('checkPermissions', 5);

        $oAgencyInfo = new OA_Dll_AgencyInfo();

        $oAgencyInfo->agencyName = 'testAgency';
        $oAgencyInfo->contactName = 'Mike';
        $oAgencyInfo->username = 'Mike';

        // Add
        $this->assertTrue($dllAgencyPartialMock->modify($oAgencyInfo),
                          $dllAgencyPartialMock->getLastError());

        // Modify
        $oAgencyInfo->agencyName = 'modified Agency';

        $this->assertTrue($dllAgencyPartialMock->modify($oAgencyInfo),
                          $dllAgencyPartialMock->getLastError());

        // Delete
        $this->assertTrue($dllAgencyPartialMock->delete($oAgencyInfo->agencyId),
            $dllAgencyPartialMock->getLastError());

        // Modify not existing id
        $this->assertTrue((!$dllAgencyPartialMock->modify($oAgencyInfo) &&
                          $dllAgencyPartialMock->getLastError() == $this->unknownIdError),
            $this->_getMethodShouldReturnError($this->unknownIdError));

        // Delete not existing id
        $this->assertTrue((!$dllAgencyPartialMock->delete($oAgencyInfo->agencyId) &&
                           $dllAgencyPartialMock->getLastError() == $this->unknownIdError),
            $this->_getMethodShouldReturnError($this->unknownIdError));

        $dllAgencyPartialMock->tally();
    }

    /**
     * A method to test get and getList method.
     */
    function testGetAndGetList()
    {
        $dllAgencyPartialMock = new PartialMockOA_Dll_Agency($this);

        $dllAgencyPartialMock->setReturnValue('checkPermissions', true);
        $dllAgencyPartialMock->expectCallCount('checkPermissions', 6);

        $oAgencyInfo1               = new OA_Dll_AgencyInfo();
        $oAgencyInfo1->agencyName   = 'test name 1';
        $oAgencyInfo1->contactName  = 'contact';
        $oAgencyInfo1->emailAddress = 'name@domain.com';
        $oAgencyInfo1->username     = 'username';
        $oAgencyInfo1->password     = 'password';

        $oAgencyInfo2               = new OA_Dll_AgencyInfo();
        $oAgencyInfo2->agencyName   = 'test name 2';
        // Add
        $this->assertTrue($dllAgencyPartialMock->modify($oAgencyInfo1),
                          $dllAgencyPartialMock->getLastError());

        $this->assertTrue($dllAgencyPartialMock->modify($oAgencyInfo2),
                          $dllAgencyPartialMock->getLastError());

        $oAgencyInfo1Get = null;
        $oAgencyInfo2Get = null;
        // Get
        $this->assertTrue($dllAgencyPartialMock->getAgency($oAgencyInfo1->agencyId, $oAgencyInfo1Get),
                          $dllAgencyPartialMock->getLastError());
        $this->assertTrue($dllAgencyPartialMock->getAgency($oAgencyInfo2->agencyId, $oAgencyInfo2Get),
                          $dllAgencyPartialMock->getLastError());

        // Check field value
        $this->assertFieldEqual($oAgencyInfo1, $oAgencyInfo1Get, 'agencyName');
        $this->assertFieldEqual($oAgencyInfo1, $oAgencyInfo1Get, 'contactName');
        $this->assertFieldEqual($oAgencyInfo1, $oAgencyInfo1Get, 'emailAddress');
        $this->assertNull($oAgencyInfo1Get->password,
                          'Field \'password\' must be null');
        $this->assertFieldEqual($oAgencyInfo2, $oAgencyInfo2Get, 'agencyName');

        // Get List
        $aAgencyList = array();
        $this->assertTrue($dllAgencyPartialMock->getAgencyList($aAgencyList),
                          $dllAgencyPartialMock->getLastError());
        $this->assertEqual(count($aAgencyList) == 2,
                           '2 records should be returned');
        $oAgencyInfo1Get = $aAgencyList[0];
        $oAgencyInfo2Get = $aAgencyList[1];
        if ($oAgencyInfo1->agencyId == $oAgencyInfo2Get->agencyId) {
            $oAgencyInfo1Get = $aAgencyList[1];
            $oAgencyInfo2Get = $aAgencyList[0];
        }
        // Check field value from list
        $this->assertFieldEqual($oAgencyInfo1, $oAgencyInfo1Get, 'agencyName');
        $this->assertFieldEqual($oAgencyInfo2, $oAgencyInfo2Get, 'agencyName');


        // Delete
        $this->assertTrue($dllAgencyPartialMock->delete($oAgencyInfo1->agencyId),
            $dllAgencyPartialMock->getLastError());

        // Get not existing id
        $this->assertTrue((!$dllAgencyPartialMock->getAgency($oAgencyInfo1->agencyId, $oAgencyInfo1Get) &&
                          $dllAgencyPartialMock->getLastError() == $this->unknownIdError),
            $this->_getMethodShouldReturnError($this->unknownIdError));

        $dllAgencyPartialMock->tally();
    }

    /**
     * Method to run all tests for agency statistics
     *
     * @access private
     *
     * @param string $methodName  Method name in Dll
     */
    function _testStatistics($methodName)
    {
        $dllAgencyPartialMock = new PartialMockOA_Dll_Agency($this);

        $dllAgencyPartialMock->setReturnValue('checkPermissions', true);
        $dllAgencyPartialMock->expectCallCount('checkPermissions', 5);

        $oAgencyInfo = new OA_Dll_AgencyInfo();

        $oAgencyInfo->agencyName = 'testAgency';

        // Add
        $this->assertTrue($dllAgencyPartialMock->modify($oAgencyInfo),
                          $dllAgencyPartialMock->getLastError());

        // Get no data
        $rsAgencyStatistics = null;
        $this->assertTrue($dllAgencyPartialMock->$methodName(
            $oAgencyInfo->agencyId, new Date('2001-12-01'), new Date('2007-09-19'),
            $rsAgencyStatistics), $dllAgencyPartialMock->getLastError());

        $this->assertTrue(isset($rsAgencyStatistics) &&
            ($rsAgencyStatistics->getRowCount() == 0), 'No records should be returned');

        // Test for wrong date order
        $rsAgencyStatistics = null;
        $this->assertTrue((!$dllAgencyPartialMock->$methodName(
                $oAgencyInfo->agencyId, new Date('2007-09-19'),  new Date('2001-12-01'),
                $rsAgencyStatistics) &&
            $dllAgencyPartialMock->getLastError() == $this->wrongDateError),
            $this->_getMethodShouldReturnError($this->wrongDateError));

        // Delete
        $this->assertTrue($dllAgencyPartialMock->delete($oAgencyInfo->agencyId),
            $dllAgencyPartialMock->getLastError());

        // Test statistics for not existing id
        $rsAgencyStatistics = null;
        $this->assertTrue((!$dllAgencyPartialMock->$methodName(
                $oAgencyInfo->agencyId, new Date('2001-12-01'),  new Date('2007-09-19'),
                $rsAgencyStatistics) &&
            $dllAgencyPartialMock->getLastError() == $this->unknownIdError),
            $this->_getMethodShouldReturnError($this->unknownIdError));

        $dllAgencyPartialMock->tally();
    }

    /**
     * A method to test getAgencyDailyStatistics.
     */
    function testDailyStatistics()
    {
        $this->_testStatistics('getAgencyDailyStatistics');
    }

    /**
     * A method to test getAgencyAdvertiserStatistics.
     */
    function testAdvertiserStatistics()
    {
        $this->_testStatistics('getAgencyAdvertiserStatistics');
    }

    /**
     * A method to test getAgencyCampaignStatistics.
     */
    function testCampaignStatistics()
    {
        $this->_testStatistics('getAgencyCampaignStatistics');
    }

    /**
     * A method to test getAgencyBannerStatistics.
     */
    function testBannerStatistics()
    {
        $this->_testStatistics('getAgencyBannerStatistics');
    }

    /**
     * A method to test getAgencyPublisherStatistics.
     */
    function testPublisherStatistics()
    {
        $this->_testStatistics('getAgencyPublisherStatistics');
    }

    /**
     * A method to test getAgencyZoneStatistics.
     */
    function testZoneStatistics()
    {
        $this->_testStatistics('getAgencyZoneStatistics');
    }


}

?>
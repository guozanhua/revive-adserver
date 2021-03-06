<?php

/*
+---------------------------------------------------------------------------+
| Revive Adserver                                                           |
| http://www.revive-adserver.com                                            |
|                                                                           |
| Copyright: See the COPYRIGHT.txt file.                                    |
| License: GPLv2 or later, see the LICENSE.txt file.                        |
+---------------------------------------------------------------------------+
*/

function MAX_checkDemoDeliveryLimitation_{GROUP}Component($limitation, $op, $aParams = array())
{
    if ($limitation == '') {
        return true;
    }
    if (empty($aParams)) {
        $aParams = $_SERVER;
    }
    $ip = $aParams['REMOTE_ADDR'];

    if (MAX_ipContainsStar($limitation)) {
        $ip = MAX_ipWithLastComponentReplacedByStar($ip);
    }

    if ($op == '==') {
        return $limitation == $ip;
    } else {
        return $limitation != $ip;
    }
}
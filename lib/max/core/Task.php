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
$Id: Task.php 4246 2006-02-15 15:05:51Z andrew@m3.net $
*/

/**
 * A parent class, defining an interface for Task objects, to be collected
 * and run using the MAX_Core_Task_Runner calss.
 *
 * @abstract
 * @package    Max
 * @subpackage Tasks
 * @author     Demian Turner <demian@m3.net>
 */
class MAX_Core_Task
{

    /**
     * A abstract method that needs to be implemented in child Task classes,
     * which will be called when the task needs to be performed.
     *
     * @abstract
     */
    function run()
    {
        return;
    }

}

?>

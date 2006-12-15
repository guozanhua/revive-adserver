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
$Id: Table.php 4614 2006-04-05 17:04:20Z andrew@m3.net $
*/

require_once('XML/RPC.php');

/**
 * A class to deal with the services provided by Openads Sync
 *
 * @package    Max
 * @subpackage OpenadsSync
 * @author     Matteo Beccati <matteo@beccati.com>
 */
class MAX_OpenadsSync
{
    var $conf;
    var $pref;
    var $dbh;
    
    var $_openadsServer = array(
        'host'   => 'sync.openads.org',
        'script' => '/xmlrpc.php',
        'port'   => 80
    );

    /**
     * PHP5-style constructor
     *
     * @param array $conf array, if null reads the global variable
     * @param array $pref array, if null reads the global variable
     */
    function __construct($conf = null, $pref = null)
    {
        $this->conf = is_null($conf) ? $GLOBALS['_MAX']['CONF'] : $conf;
        $this->pref = is_null($pref) ? $GLOBALS['_MAX']['PREF'] : $pref;
        
        $this->dbh = &MAX_DB::singleton();
    }

    /**
     * PHP4-style constructor
     *
     * @param array $conf array, if null reads the global variable
     * @param array $pref array, if null reads the global variable
     */
    function MAX_OpenadsSync($conf = null, $pref = null)
    {
        $this->__construct($conf, $pref);
    }

    /**
     * Return phpAdsNew style config version parsing MAX_VERSION_READABLE
     *
     * the stability tag is converted to an int using the following table:
     *
     * 'alpha'  => 1
     * 'beta'   => 2
     * 'rc'     => 3
     * 'stable' => 4
     *
     * i.e.
     * v0.3.29-alpha becomes:
     * 0  *  100 +
     * 3  *   10 +
     * 29 /  100 +
     * 1  / 1000 =
     * ---------
     *    30.291
     */
    function getConfigVersion()
    {
        $a = array(
            ''       => 0,
            'alpha'  => 1,
            'beta'   => 2,
            'rc'     => 3,
            'stable' => 4
        );
        
        $v = preg_split('/[.-]/', substr(MAX_VERSION_READABLE, 1));
        $v = array_pad($v, 4, '');
        
        return $v[0] * 100 + $v[1] * 10 + $v[2] / 100 + $a[$v[3]] / 1000;
    }

    /**
     * Connect to Openads Sync to check for updates
     *
     * @param float Only check for updates newer than this value
     * @param bool Send software details
     * @return array Two items:
     *               Item 0 is the XML-RPC error code (special meanings: 0 - no error, 800 - No updates)
     *               Item 1 is either the error message (item 1 != 0), or an array containing update info
     */
    function checkForUpdates($already_seen = 0, $send_sw_data = true)
    {
        global $XML_RPC_erruser;

        // Create client object
        $client = new XML_RPC_Client($this->_openadsServer['script'],
            $this->_openadsServer['host'], $this->_openadsServer['port']);
            
        $params = array(
            new XML_RPC_Value('MMM-0.3', 'string'),
            new XML_RPC_Value($this->getConfigVersion(), 'string'),
            new XML_RPC_Value($already_seen, 'string'),
            new XML_RPC_Value('', 'string'),
            new XML_RPC_Value($this->pref['instance_id'], 'string')
        );
        
        if ($send_sw_data) {
            // Prepare software data
            $params[] = XML_RPC_Encode(array(
                'os_type'                    => php_uname('s'),
                'os_version'                => php_uname('r'),
                
                'webserver_type'            => isset($_SERVER['SERVER_SOFTWARE']) ? preg_replace('#^(.*?)/.*$#', '$1', $_SERVER['SERVER_SOFTWARE']) : '',
                'webserver_version'            => isset($_SERVER['SERVER_SOFTWARE']) ? preg_replace('#^.*?/(.*?)(?: .*)?$#', '$1', $_SERVER['SERVER_SOFTWARE']) : '',
    
                'db_type'                    => $GLOBALS['phpAds_dbmsname'],
                'db_version'                => $this->dbh->getOne("SELECT VERSION()"),
                
                'php_version'                => phpversion(),
                'php_sapi'                    => ucfirst(php_sapi_name()),
                'php_extensions'            => get_loaded_extensions(),
                'php_register_globals'        => (bool)ini_get('register_globals'),
                'php_magic_quotes_gpc'        => (bool)ini_get('magic_quotes_gpc'),
                'php_safe_mode'                => (bool)ini_get('safe_mode'),
                'php_open_basedir'            => (bool)strlen(ini_get('open_basedir')),
                'php_upload_tmp_readable'    => (bool)is_readable(ini_get('upload_tmp_dir').DIRECTORY_SEPARATOR),
            ));
        }
        
        // Create XML-RPC request message
        $msg = new XML_RPC_Message("Openads.Sync", $params);
    
        // Send XML-RPC request message
        if($response = $client->send($msg, 10)) {
            // XML-RPC server found, now checking for errors
            if (!$response->faultCode()) {
                $ret = array(0, XML_RPC_Decode($response->value()));
                
                // Save to cache only when additional data was sent
                if ($send_sw_data) {
                    $this->dbh->query("
                        UPDATE
                            {$this->conf['table']['prefix']}{$this->conf['table']['preference']}
                        SET
                            updates_cache = '".addslashes(serialize($ret[1]))."',
                            updates_timestamp = ".time()."
                        WHERE
                            agencyid = 0;
                    ");
                }
            } else {
                $ret = array($response->faultCode(), $response->faultString());
            }
            
            return $ret;
        }
        
        return array(-1, 'No response from the server');
    }
}

?>
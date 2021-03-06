<?php
/**
 * Data class to record responses to polls
 *
 * PHP version 5
 *
 * @category PollPlugin
 * @package  StatusNet
 * @author   Brion Vibber <brion@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://status.net/
 *
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2011, StatusNet, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.     See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('STATUSNET')) {
    exit(1);
}

/**
 * For storing the poll options and such
 *
 * @category PollPlugin
 * @package  StatusNet
 * @author   Brion Vibber <brion@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://status.net/
 *
 * @see      DB_DataObject
 */
class Sharing_city extends Managed_DataObject
{
    public $__table = 'sharing_city'; // table name
    public $id;          // char(36) primary key not null -> UUID
    public $name;         // varchar(191)   not 255 because utf8mb4 takes more space
    public $lat;                             // decimal(10,7)
    public $lon;                             // decimal(10,7)
    public $created;     // datetime

    /**
     * The One True Thingy that must be defined and declared.
     */
    public static function schemaDef()
    {
        return array(
            'description' => 'Record of sharing cities',
            'fields' => array(
                'id' => array('type' => 'int', 'not null' => true),
                'name' => array('type' => 'varchar', 'length' => 200, 'not null' => true, 'description' => 'City name'),
                'lat' => array('type' => 'numeric', 'precision' => 10, 'scale' => 7, 'description' => 'latitude'),
                'lon' => array('type' => 'numeric', 'precision' => 10, 'scale' => 7, 'description' => 'longitude'),
                'created' => array('type' => 'datetime', 'not null' => true),               
            ),
            'primary key' => array('id'),
        );
    }

    static function getNameById($id)
    {
        $city = self::getKV('id', $id);
        
        if($city) {
            return $city->name;
        } else {
            return _m('Objeto o servicio sin ciudad');
        }
    }

}

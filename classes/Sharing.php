<?php
/**
 * Data class to mark notices as bookmarks
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

class Sharing extends Managed_DataObject
{
    public $__table = 'sharing'; // table name
    public $id;          // char(36) primary key not null -> UUID
    public $uri;         // varchar(191)   not 255 because utf8mb4 takes more space
    public $profile_id;  // int -> profile.id
    public $displayName;    // text
    public $summary;    // text
    public $created;     // datetime
    public $updated;     // datetime

    /**
     * The One True Thingy that must be defined and declared.
     */
    public static function schemaDef()
    {
        return array(
            'description' => 'Per-notice sharing data for Sharings plugin',
            'fields' => array(
                'id' => array('type' => 'char', 'length' => 36, 'not null' => true, 'description' => 'UUID'),
                'uri' => array('type' => 'varchar', 'length' => 191, 'not null' => true),
                'profile_id' => array('type' => 'int'),
                'displayName' => array('type' => 'text'),
                'summary' => array('type' => 'text'),
                'created' => array('type' => 'datetime', 'not null' => true),
                'updated' => array('type' => 'datetime', 'not null' => true),
            ),
            'primary key' => array('id'),
            'unique keys' => array(
                'sharing_uri_key' => array('uri'),
            ),
        );
    }

    /**
     * Get a bookmark based on a notice
     *
     * @param Notice $notice Notice to check for
     *
     * @return Poll found poll or null
     */
    static function getByNotice($notice)
    {
        return self::getKV('uri', $notice->uri);
    }

    function getOptions()
    {
        return explode("\n", $this->options);
    }

    /**
     * Is this a valid selection index?
     *
     * @param numeric $selection (1-based)
     * @return boolean
     */
    function isValidSelection($selection)
    {
        if ($selection != intval($selection)) {
            return false;
        }
        if ($selection < 1 || $selection > count($this->getOptions())) {
            return false;
        }
        return true;
    }

    function getNotice()
    {
        return Notice::getKV('uri', $this->uri);
    }

    function getUrl()
    {
        return $this->getNotice()->getUrl();
    }

    /**
     * Get the response of a particular user to this poll, if any.
     *
     * @param Profile $profile
     * @return Poll_response object or null
     */
    function getResponse(Profile $profile)
    {
    	$pr = Sharing_response::pkeyGet(array('sharing_id' => $this->id,
    									   'profile_id' => $profile->id));
    	return $pr;
    }

    function countResponses()
    {
        $pr = new Poll_response();
        $pr->poll_id = $this->id;
        $pr->groupBy('selection');
        $pr->selectAdd('count(profile_id) as votes');
        $pr->find();

        $raw = array();
        while ($pr->fetch()) {
            // Votes list 1-based
            // Array stores 0-based
            $raw[$pr->selection - 1] = $pr->votes;
        }

        $counts = array();
        foreach (array_keys($this->getOptions()) as $key) {
            if (isset($raw[$key])) {
                $counts[$key] = $raw[$key];
            } else {
                $counts[$key] = 0;
            }
        }
        return $counts;
    }

    /**
     * Save a new poll notice
     *
     * @param Profile $profile
     * @param string  $question
     * @param array   $opts (poll responses)
     *
     * @return Notice saved notice
     */
    static function saveNew($profile, $displayName, $summary, $options=null)
    {

        $s = new Sharing();

        $s->id          = UUID::gen();
        $s->profile_id  = $profile->id;
        $s->displayName    = $displayName;
        $s->summary     = $summary;

        if (array_key_exists('created', $options)) {
            $s->created = $options['created'];
        } else {
            $s->created = common_sql_now();
        }

        if (array_key_exists('uri', $options)) {
            $s->uri = $options['uri'];
        } else {
            $s->uri = common_local_url('showsharings',
                                        array('id' => $s->id));
        }

        common_log(LOG_DEBUG, "Saving sharings: $s->id $s->uri");

        $s->insert();

        // TRANS: Notice content creating a poll.
        // TRANS: %1$s is the poll question, %2$s is a link to the poll.
        $content  = sprintf(_m('Objeto/Servicio: %1$s %2$s'),
                            $displayName,
                            $s->uri);
        $link = '<a href="' . htmlspecialchars($s->uri) . '">' . htmlspecialchars($displayName) . '</a>';
        // TRANS: Rendered version of the notice content creating a poll.
        // TRANS: %s is a link to the poll with the question as link description.
        $rendered = sprintf(_m('Objeto/Servicio: %s'), $link);

        $tags    = array('sharings');
        $replies = array();

        $options = array_merge(array('urls' => array(),
                                     'rendered' => $rendered,
                                     'tags' => $tags,
                                     'replies' => $replies,
                                     'object_type' => SharingsPlugin::SHARINGS_OBJECT),
                               $options);

        if (!array_key_exists('uri', $options)) {
            $options['uri'] = $s->uri;
        }

        $saved = Notice::saveNew($profile->id,
                                 $content,
                                 array_key_exists('source', $options) ?
                                 $options['source'] : 'web',
                                 $options);

        return $saved;
    }
}

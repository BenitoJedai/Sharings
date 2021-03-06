<?php
/**
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2011, StatusNet, Inc.
 *
 * Form for adding a new poll
 *
 * PHP version 5
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  PollPlugin
 * @package   StatusNet
 * @author    Brion Vibber <brion@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */

if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/**
 * Form to add a new poll thingy
 *
 * @category  PollPlugin
 * @package   StatusNet
 * @author    Brion Vibber <brion@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */
class SharingsResultForm extends Form
{
    protected $sharing;

    /**
     * Construct a new poll form
     *
     * @param Poll $poll
     * @param HTMLOutputter $out         output channel
     *
     * @return void
     */
    function __construct(Sharing $sharing, HTMLOutputter $out)
    {
        parent::__construct($out);
        $this->sharing = $sharing;
    }

    /**
     * ID of the form
     *
     * @return int ID of the form
     */
    function id()
    {
        return 'pollresult-form';
    }

    /**
     * class of the form
     *
     * @return string class of the form
     */
    function formClass()
    {
        return 'form_settings ajax';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */
    function action()
    {
        return common_local_url('respondsharings', array('id' => $this->sharing->id));
    }

    /**
     * Data elements of the form
     *
     * @return void
     */
    function formData()
    {
        $sharing = $this->sharing;
        $out = $this->out;
        $counts = $sharing->countResponses();

        $out->element('h3', 'sharing-title', _m('Un nuevo usuario ha indicado que le interesa:'));
        $out->element('p', 'sharings-displayName', $sharing->displayName);
        $out->element('p', 'sharings-summary', $sharing->summary);

        $out->element('p', 'sharings-summary', sprintf(_m('Este objeto / servicio le interesa a %d usuarios'), $counts));

    }

    /**
     * Action elements
     *
     * @return void
     */
    function formActions()
    {
    }
}

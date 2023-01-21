<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * webservice definitions.
 *
 * @package    block_learningcoach
 * @copyright  2022 Traindy/3E Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'block_learningcoach_get_status' => array(
        'classname'   => 'block_learningcoach\external',
        'methodname'  => 'get_status',
        'description' => 'Return version and status as JSON.',
        'type'        => 'read',
        'ajax'        => true,
    ),
    'block_learningcoach_update_profile' => array(
        'classname'   => 'block_learningcoach\external',
        'methodname'  => 'update_profile',
        'description' => 'Update Learning Coach profiles.',
        'type'        => 'write',
        'ajax'        => true,
    ),
    'block_learningcoach_update_privacy' => array(
        'classname'   => 'block_learningcoach\external',
        'methodname'  => 'update_privacy',
        'description' => 'Update Learning Coach privacy status.',
        'type'        => 'write',
        'ajax'        => true,
    ),
    'block_learningcoach_get_plugin_infos' => array(
        'classname'   => 'block_learningcoach\external',
        'methodname'  => 'get_plugin_infos',
        'description' => 'Get plugin infos and Moodle version.',
        'type'        => 'read',
        'ajax'        => true,
    ),


);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'LearningCoach Traindy webservice' => array(
        'functions' => array (
            'block_learningcoach_get_status',
            'block_learningcoach_update_profile',
            'block_learningcoach_update_privacy',
            'block_learningcoach_get_plugin_infos',
        ),
        'shortname' => 'LCTraindyws',
        'restrictedusers' => 1,
        'enabled' => 1,
    )
);

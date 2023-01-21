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
 * Block caps.
 *
 * @package    block_learningcoach
 * @copyright  2022 Traindy / 3E-Innovation
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    // Groups.
    array(
        'eventname' => '\core\event\group_deleted',
        'callback' => '\block_learningcoach\events_observers::group_deleted'
    ),
    array(
        'eventname' => '\core\event\group_member_added',
        'callback' => '\block_learningcoach\events_observers::group_member_added',
    ),
    array(
        'eventname' => '\core\event\group_member_removed',
        'callback' => '\block_learningcoach\events_observers::group_member_removed',
    ),

    // LC Cohort.
    array(
        'eventname' => '\core\event\cohort_member_added',
        'callback' => '\block_learningcoach\events_observers::lc_cohort_user_add',
    ),
    array(
        'eventname' => '\core\event\cohort_member_removed',
        'callback' => '\block_learningcoach\events_observers::lc_cohort_user_remove',
    ),
    array(
        'eventname' => '\core\event\core\event\cohort_deleted',
        'callback' => '\block_learningcoach\events_observers::lc_cohort_delete',
    ),

    array(
        'eventname' => '\core\event\user_created',
        'callback' => '\block_learningcoach\events_observers::user_created',
    ),
    array(
        'eventname' => '\core\event\user_deleted',
        'callback' => '\block_learningcoach\events_observers::user_deleted',
    ),

];

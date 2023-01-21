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
 * Block learningcoach is defined here.
 *
 * @package     block_learningcoach
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_learningcoach;

defined('MOODLE_INTERNAL') || die();


/**
 * Event observer.
 */
class events_observers {

    /**
     * Add User to LearningCoach system
     * Tested ok by LM 2022-09-06
     *
     * @param \core\event\cohort_member_added $event
     * @return array if register_user is called via get_cohort_users, else return false
     */
    public static function lc_cohort_user_add(\core\event\cohort_member_added $event) {
        GLOBAL $DB;
        $mylc = new Lc();

        $eventdata = $event->get_data();
        $cohortlc = $DB->get_record($mylc->tablecohort, array('idnumber' => $mylc->cohortidnumber));

        if (!$cohortlc) {
            return false;
        } else {
            if ($cohortlc->id == $eventdata['objectid']) {
                // Call get_cohort_users tha call register.
                return $mylc->get_cohort_users();
            } else {
                return false;
            }

        }
    }

    /**
     * Remove user and data associated of LearningCoach cohort
     * Tested ok by LM 2022-09-06
     *
     * @param \core\event\cohort_member_removed $event
     * @return bool true if lc user data deleted else false
     */
    public static function lc_cohort_user_remove(\core\event\cohort_member_removed $event) {
        GLOBAL $DB;
        $mylc = new Lc();

        $eventdata = $event->get_data();
        $cohortlc = $DB->get_record($mylc->tablecohort, array('idnumber' => $mylc->cohortidnumber));

        if (!$cohortlc) {
            return false;
        } else {
            if ($cohortlc->id == $eventdata['objectid']) {
                // removes the user and the data associated.
                return $mylc->user_delete($eventdata['relateduserid']);
            } else {
                return false;
            }
        }
    }

    /**
     * If 'Learning Coach cohort deletion' event is observed, then delete_users_and_scoresand_stats
     * Tested Ok By LM 2023/01/15
     * @param \core\event\cohort_deleted $event
     * @return array with boolean values if data deleted else false
     */
    public static function lc_cohort_delete(\core\event\cohort_deleted $event) {
        GLOBAL $DB;
        $eventdata = $event->get_data();

        $mylc = new Lc();
        $cohortlc = $DB->get_record($mylc->tablecohort, array('idnumber' => $mylc->cohortidnumber));
        // The cohort is already deleted by Moodle when this function is called
        // So if lc cohort does not exist anymore then delete data
        if (!$cohortlc) {
            // return [true,true,true] if everything is ok
            return $mylc->delete_users_and_scoresand_stats();
        } else {
            return false;
        }
    }

    /**
     * If 'member added in a group' event is observed, then updates the group profile
     *
     * @param \core\event\group_member_added $event
     * @return void
     */
    public static function group_member_added(\core\event\group_member_added $event) {
        GLOBAL $DB;
        $mylc = new Lc();
        $eventdata = $event->get_data();
        $groupid = $eventdata['objectid'];
        $userid = $eventdata['relateduserid'];
        // check if user is in LC cohort
        $cohortlc = $DB->get_record($mylc->tablecohort, array('idnumber' => $mylc->cohortidnumber));
        if (cohort_is_member ($cohortlc->id, $userid)) {
            return $mylc->profile_group($eventdata['objectid'], $eventdata['courseid'], true);
        } else {
            return false;
        }

    }

    /**
     * If 'member removed of a group' event is observed, then updates the group profile
     *
     * @param \core\event\group_member_removed $event
     * @return void
     */
    public static function group_member_removed(\core\event\group_member_removed $event) {
        GLOBAL $DB;
        $mylc = new Lc();
        $eventdata = $event->get_data();
        $groupid = $eventdata['objectid'];
        $userid = $eventdata['relateduserid'];
        // check if user is in LC cohort
        $cohortlc = $DB->get_record($mylc->tablecohort, array('idnumber' => $mylc->cohortidnumber));
        if (cohort_is_member ($cohortlc->id, $userid)) {
            return $mylc->profile_group($eventdata['objectid'], $eventdata['courseid'], true);
        } else {
            return false;
        }
    }

    /**
     * Delete Learning Coach statistics from DD when group is deleted -
     *
     * @param \core\event\group_deleted $event
     * @return bool true if data found and deleted else false
     */
    public static function group_deleted(\core\event\group_deleted $event) {
        // No check to do, if no group found request in group_delete_stats return false if no data to delete
        GLOBAL $DB;
        $mylc = new Lc();
        $eventdata = $event->get_data();
        $groupid = $eventdata['objectid'];
        $result = $DB->get_records($mylc->tablestats, array('moodle_group_id' => $groupid));
        if (count($result) > 0) {
            return $mylc->group_delete_stats($groupid);
        } else {
            return false;
        }
    }

    /**
     * Automatic enrolment to LearningCoach
     *
     * @param \core\event\user_created $event
     * @return void
     */
    public static function user_created(\core\event\user_created $event) {
        $mylc = new Lc();
        // Enrol the user if automatic enrolment setting is on.
        if ($mylc->lcenrolmentpolicy == "automatic") {
            $eventdata = $event->get_data();
            // Username : $eventdata['other']["username"];
            return $mylc->enrol_in_lc_cohort($eventdata['objectid']);
        } else {
            return false;
        }
    }

    /**
     * Delete LC user -- Tested ok by LM 2022-09-06
     *
     * @param \core\event\user_deleted $event
     * @return bool
     */
    public static function user_deleted(\core\event\user_deleted $event) {
        GLOBAL $DB;
        $eventdata = $event->get_data();
        $mylc = new Lc();

        $lcuser = $DB->get_record($mylc->tablelcusers, ['fk_moodle_user_id' => $eventdata['objectid']]);
        if (!$lcuser) {
            return false;
        } else {
            // Username : $eventdata['other']["username"];
            return $mylc->user_delete($eventdata['objectid']);
        }

    }

}

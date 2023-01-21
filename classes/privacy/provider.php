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
 * Privacy Subsystem implementation for block_learningcoach.
 *
 * @package    block_learningcoach
 * @copyright  2022 Traindy/3E Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_learningcoach\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\context;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;

/**
 * Privacy Subsystem for block_tag_flickr implementing metadata and plugin provider.
 *
 * @copyright  2018 Zig Tan <zig@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin stores user data.
    \core_privacy\local\metadata\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider,

    // This plugin may provide access to and deletion of user data.
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns the fields which contain personal data.
     *
     * LearningCoach stores data only in the DB. No files are used. No user preferences are stored.
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {

        // Describe data stored in block_learningcoach_users table.
        $collection->add_database_table('block_learningcoach_users', [
            'registered' => 'privacy:metadata:block_learningcoach_users:registered',
            'data_acces' => 'privacy:metadata:block_learningcoach_users:data_acces',
            'time_added' => 'privacy:metadata:block_learningcoach_users:time_added',
            'time_register' => 'privacy:metadata:block_learningcoach_users:time_register',
            'time_update' => 'privacy:metadata:block_learningcoach_users:time_update',
            'time_completion' => 'privacy:metadata:block_learningcoach_users:time_completion',
            'fk_moodle_user_id' => 'privacy:metadata:block_learningcoach_users:fk_moodle_user_id',
            'fk_lc_user_id' => 'privacy:metadata:block_learningcoach_users:fk_lc_user_id',
        ], 'privacy:metadata:block_learningcoach_users');

        // Describe data stored in block_learningcoach_cons_sco table.
        $collection->add_database_table('block_learningcoach_cons_sco', [
            'construct_score' => 'privacy:metadata:block_learningcoach_cons_sco:construct_score',
            'profile_version' => 'privacy:metadata:block_learningcoach_cons_sco:profile_version',
            'fk_id_construct' => 'privacy:metadata:block_learningcoach_cons_sco:fk_id_construct',
            'fk_moodle_user_id' => 'privacy:metadata:block_learningcoach_cons_sco:fk_moodle_user_id',
        ], 'privacy:metadata:block_learningcoach_cons_sco');

        // Indicates that data are stored in LearningCoach App.
        $collection->add_external_location_link('lc_user_infos', [
            'email' => 'privacy:metadata:lc_user_infos:email',
            'firstname' => 'privacy:metadata:lc_user_infos:fistname',
            'lastname' => 'privacy:metadata:lc_user_infos:lastname',
            'lang' => 'privacy:metadata:lc_user_infos:lang',
        ], 'privacy:metadata:lc_user_infos');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        // Data of the user is stored in the context of the system.
        // Data is not stored in a context of a course or block.
        $contextlist = new \core_privacy\local\request\contextlist();
        // Return system context.
         $contextlist->add_system_context();

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {

        global $DB;
        $context = \context_system::instance();
        // If not in a system context return.
        if (!$contextlist->count() || !in_array($context->id, $contextlist->get_contextids())) {
            return;
        }

        $context = \context_system::instance();

        $user = $contextlist->get_user();
        // Get the data.
        $sql = "SELECT cs.id, cs.construct_score,
                       c.tag AS construct_tag, ct.name AS construct_name,
                       ct.description AS construct_description,
                       dt.name AS dimension_name
                FROM {block_learningcoach_cons_sco} cs
                JOIN {block_learningcoach_const} c ON c.id = cs.fk_id_construct
                JOIN {block_learningcoach_cons_tra} ct ON ct.fk_id_construct = c.id AND ct.lang='fr'
                JOIN {block_learningcoach_dim} d ON d.id = c.fk_id_dimension
                JOIN {block_learningcoach_dim_tra} dt ON dt.fk_id_dimension = d.id AND dt.lang='fr'
                WHERE cs.fk_moodle_user_id = ?
                ORDER BY dimension_name, construct_name
            ";
        $results = $DB->get_recordset_sql($sql, [$user->id]);
        foreach ($results as $key => $result) {
            $learningcoachdata[] = (object) [
                'dimension' => $result->dimension_name,
                'construct' => $result->construct_name,
                'tag' => $result->construct_tag,
                'description' => $result->construct_description,
                'score' => $result->construct_score,
            ];
        }

        if (!empty($learningcoachdata)) {
            $data = (object) [
                'feeds' => $learningcoachdata,
            ];
            \core_privacy\local\request\writer::with_context($context)->export_data([
                get_string('pluginname', 'block_learningcoach')], $data);
        } else {
            return;
        }
        $results->close();
        // \core_privacy\local\request\writer::with_context($context)->export_data([], $exportdata);

        // commenté par Ludo 07/10/2022
        /*
        $userid = $contextlist->get_user()->id;

        $sql = "SELECT c.id, c.construct_score,
                    cd.tag AS construct_tag, cd.name AS construct_name, cd.description AS construct_description,
                    d.name AS dimension_name
                FROM {block_learningcoach_cons_sco} c
                JOIN {block_learningcoach_const} cd ON cd.id = c.fk_id_construct
                JOIN {block_learningcoach_dim} d ON d.id = cd.fk_id_dimension
                WHERE c.fk_moodle_user_id = $userid
                ORDER BY dimension_name, construct_name
            ";

        $results = static::get_records_sql($sql);

        $learningcoachdata = [];

        foreach ($results as $key => $result) {

            $learningcoachdata[] = (object) [
                'dimension' => $result->dimension_name,
                'construct' => $result->construct_name,
                'tag' => $result->construct_tag,
                'description' => $result->construct_description,
                'score' => $result->construct_score,
            ];
        }

        if (!empty($learningcoachdata)) {
            $data = (object) [
                'feeds' => $learningcoachdata,
            ];
            \core_privacy\local\request\writer::with_context($contextlist->current())->export_data([
                    get_string('pluginname', 'block_learningcoach')], $data);
        }*/
    }

    /**
     * Delete all use data which matches the specified deletion_criteria.
     *
     * @param   context $context A user context.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        // Can only be defined in system context.
        if ($context->id == \context_system::instance()->id) {
            $DB->delete_records('block_learningcoach_users');
            $DB->delete_records('block_learningcoach_cons_sco');
        }

        /*
        if ($context instanceof \context_user) {
            static::delete_data($context->instanceid);
        }*/
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {

        global $DB;
        $context = \context_system::instance();
        if (!$contextlist->count() || !in_array($context->id, $contextlist->get_contextids())) {
            return;
        }
        $DB->delete_records('block_learningcoach_cons_sco', ['fk_moodle_user_id' => $contextlist->get_user()->id]);
        $DB->delete_records('block_learningcoach_users', ['fk_moodle_user_id' => $contextlist->get_user()->id]);
        /*
        static::delete_data($contextlist->get_user()->id);
        */
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof \context_system) {
            return;
        }
        $sql = "SELECT fk_moodle_user_id
                FROM {block_learningcoach_users}
                WHERE fk_moodle_user_id = ?
            ";
        /* example
                $params = [
            'userid' => $context->instanceid,
            'datatype' => 'checkbox'
        ];*/
        $params = [$context->instanceid];
        $userlist->add_from_sql('fk_moodle_user_id', $sql, $params);
        // It is possible to cal several slq queries and use the add_from_sql method several times.
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        if (!count($userlist)) {
            return;
        }

        $userids = $userlist->get_userids();
        $context = $userlist->get_context();

        if ($context instanceof \context_system) {
            list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
            list($usersql, $userparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);

            $DB->delete_records_list('block_learningcoach_cons_sco', 'fk_moodle_user_id', $userids);
            $DB->delete_records_list('block_learningcoach_users', 'fk_moodle_user_id', $userids);
            /* $DB->set_field_select('tag', 'userid', 0, "userid {$usersql}", $userparams);
             $DB->delete_records('block_learningcoach_cons_sco', ['fk_moodle_user_id' => $contextlist->get_user()->id]);
             $DB->delete_records('block_learningcoach_users', ['fk_moodle_user_id' => $contextlist->get_user()->id]);*/
        }
        /* exemple





                    $contextid = $userlist->get_context()->id;

                    $userids = $userlist->get_userids();

                    $coursecontext = CONTEXT_COURSE;
                    list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
                    $sql = "SELECT lcr.id

                              FROM {context} ctx

                              JOIN {event} e ON

                                   (e.eventtype = 'course' AND ctx.contextlevel = $coursecontext AND e.courseid = ctx.instanceid)

                              JOIN {liveclass_reservations} lcr ON e.id = lcr.eventid

                             WHERE ctx.id = $contextid AND lcr.userid $insql";



                    $records = $DB->get_records_sql($sql, $inparams);

                    $reservationids = array_map(function($record) {

                        return $record->id;

                    }, $records);
                    $DB->delete_records_list('liveclass_reservations', 'id', $reservationids);

                */

        /*        $context = $userlist->get_context();

                if ($context instanceof \context_user) {
                    static::delete_data($context->instanceid);
                }*/
    }

}

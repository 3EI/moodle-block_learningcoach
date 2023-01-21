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
global $CFG;



require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use external_warnings;
use context_course;
use context_system;

/**
 * This is the external API for this component.
 *
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /** @var string Error message missing user */
    private static $errmsgmissinguser = 'User not found in Moodle';

    /** @var string Error code missing user */
    private static $errcodemissinguser = 'missing_user';

    /**
     * Get parameters
     *
     * @return external_function_parameters
     */
    public static function get_status_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get Status API
     *
     * @return array An array with a 'data' JSON string and a 'warnings' string
     */
    public static function get_status() {
        $json = ['version' => '0.1', 'message' => 'Up and running'];
        $warnings = "";
        return array('data' => json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), 'warnings' => $warnings);
    }

    /**
     * get_status return
     *
     * @return external_description
     */
    public static function get_status_returns() {
        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'JSON-formatted version and message'),
                'warnings' => new external_value(PARAM_TEXT, 'Warning message'),
            )
        );
    }



    /**
     * Old Update profile parameters
     *
     * @return external_function_parameters
     */
    public static function old_update_profile_parameters() {
        return new external_function_parameters(
            array(
                'profiles' => new external_value(PARAM_RAW, 'JSON-formatted profiles data', VALUE_REQUIRED),
                'multiple' => new external_value(PARAM_BOOL, VALUE_OPTIONAL),
        ));
    }
    /**
     * Update profile parameters
     *
     * @return external_function_parameters
     */
    public static function update_profile_parameters() {
        return new external_function_parameters(
            array(
                'data' => new external_value(PARAM_RAW, 'JSON-formatted profile data', VALUE_REQUIRED),
        ));

        /*return new external_single_structure(
            'profile', array(
                new external_param('user_id', PARAM_INT, 'user id'),
                new external_param('meta', PARAM_RAW, 'meta infos'),
                new external_param('scales', PARAM_RAW, 'scales infos'),
        )); */
        /* return new external_single_structure('profile'); */
    }

    /**
     * Update user profile
     *
     * @param json $data with (int)user_id and (json)scales
     * @return array with 'data' key - json with (string)errorcode and (string)message
     */
    public static function update_profile($data) {
        global $DB, $CFG;

        $profilesarr = $data;

        $placeholders = [];
        // For values ​​to insert into `block_learningcoach_cons_sco`.
        $insertvalues = [];

        $profilejson = json_decode($data, true);

        // Check that the given user is actually available in Moodle
        $lcuserid = $profilejson['user_id'];
        $tablcuser = $DB->get_record('block_learningcoach_users', ['fk_lc_user_id' => $lcuserid]);
        if (!$tablcuser) {
            return ['data' => json_encode(['errorcode' => self::$errcodemissinguser, 'message' => self::$errmsgmissinguser.$lcuserid ])];
        }

        // Get construct ids.
        $tabconstructdescription = $DB->get_records('block_learningcoach_const');

        $idsconstruct = [];
        foreach ($tabconstructdescription as $construct) {
            $idsconstruct[$construct->tag] = $construct->id;
        }

        $moodleusers = [];
        if (count($profilejson['scales']) == 0) {
            return ['data' => json_encode(['errorcode' => 'jsonproblem', 'message' => 'No scales found'])];
        }

        // Build the insert values and placeholders for SQL query INSERT DUPLICATE KEY UPDATE.
        foreach ($profilejson['scales'] as $scale => $constructs) {
            foreach ($constructs as $constructtag => $score) {
                array_push($insertvalues, $score, $profilejson['meta']['version'], intval($idsconstruct[$constructtag]),  $tablcuser->fk_moodle_user_id);
                array_push($placeholders, '(' . self::gen_row_place_holders(4) . ')');
            }
        }
        $places = implode(',', $placeholders);
        if (count($insertvalues) > 0) {

            // Check MySQL version.
            $dbhost = $CFG->dbhost;
            $dbuser = $CFG->dbuser;
            $dbpass = $CFG->dbpass;

            $mysqli = new \mysqli ($dbhost, $dbuser, $dbpass);

            // Depending on MySQL version, use the appropriate syntax for the query :

            if (stripos($mysqli->server_info, "mariadb") == true || (stripos($mysqli->server_info, "mariadb") == false && $mysqli->server_version < 80019)) {
                // This one works with mariadb and myslqi < 8.0.19
                $sql = "INSERT INTO {block_learningcoach_cons_sco}
                            (construct_score, profile_version, fk_id_construct, fk_moodle_user_id)
                            VALUES $places
                            ON DUPLICATE KEY UPDATE construct_score = VALUES(construct_score)
                            ";
                // MySQL 8.0.19 and up
                // This one does not works with mariadb
            } else {
                // MySQL 8.0.19.
                $sql = "INSERT INTO {block_learningcoach_cons_sco}
                            (construct_score, profile_version, fk_id_construct, fk_moodle_user_id)
                            VALUES $places AS n
                            ON DUPLICATE KEY UPDATE construct_score =  n.construct_score
                            ";
            }
            $status = $DB->execute($sql, $insertvalues);

            /* try {
                $status = $DB->update_record('block_learningcoach_cons_sco', $insertvalues, $bulk=false);
            } catch (Exception $e) {
                $status = $DB->insert_records('block_learningcoach_cons_sco', $insertvalues);
            } */

            if (!$status) {
                // Return error
                return ['data' => json_encode(['errorcode' => 'databaseinsertion', 'message' => 'Problem inserting data in database'])];
            }
        }
        // Return success
        return ['data' => json_encode(['errorcode' => 'ok', 'message' => 'Profile updated'])];
    }

    /**
     * update_profile return
     *
     * @return external_description
     */
    public static function update_profile_returns() {
        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'JSON-formatted version and message')
            )
        );
    }

    /**
     * Generates placeholders for multiple rows insertion
     *
     * @param integer $count
     * @return void
     */
    private static function gen_row_place_holders($count=0) {
        $result = array();
        if ($count > 0) {
            for ($x = 0; $x < $count; $x++) {
                $result[] = '?';
            }
        }
        return implode(',', $result);
    }

    /**
     * Webservice GDPR (called from Learning coach when user updates his data privacy status)
     * If $sharedata is false, delete the data from the database for the user
     *
     * @param json $data with (int)user_id and (bool)share_data
     * @return array with 'data' key - json with code and message
     */
    public static function update_privacy($data) {
        global $DB;

        $profilejson = json_decode($data, true);

        // Check that the json parameters are valid.
        if (!array_key_exists('user_id', $profilejson) || !array_key_exists('share_data', $profilejson)) {
            // Return error.
            return ['data' => json_encode(['errorcode' => 'invalidparaminjson', 'message' => 'user_id or share_data missing in JSON'])];
        }
        $lcuserid = $profilejson['user_id'];
        $sharedata = $profilejson['share_data'];
        $mylc = new Lc();
        $lcuser = $DB->get_record('block_learningcoach_users', ['fk_lc_user_id' => $lcuserid]);
        // Check that the given user is actually available in Moodle.
        if (!$lcuser) {
            return ['data' => json_encode(['errorcode' => self::$errcodemissinguser, 'message' => self::$errmsgmissinguser.$lcuserid ])];
        } else {
            $lcuser->time_updated = time();
            $lcuser->data_acces = (int)$sharedata;
            if (!$sharedata) {
                // Update time and data_acces fields in block_learningcoach_users.
                $result = $mylc->update_data_acces($lcuser);
                // Delete all scores for this user, but not the user entry in block_learningcoach_users.
                $datadeleted = $mylc->user_delete_scores($lcuser->fk_moodle_user_id);
                if ($datadeleted) {
                    // Return success.
                    return ['data' => json_encode(['errorcode' => 'ok', 'message' => 'Privacy updated, data deleted'])];
                } else {
                    // Return error.
                    return ['data' => json_encode(['errorcode' => 'errordatadeletion', 'message' => 'Problem deleting data in database called from update_privacy'])];
                }
            } else {
                // Update time and data_acces fields in block_learningcoach_users.
                $result = $mylc->update_data_acces($lcuser);
                // Return statement that profile has been updated.
                return ['data' => json_encode(['errorcode' => 'nodeletion', 'message' => 'Update_privacy called with sharedata = true, user privacy updated'])];
            }
        }
    }


    /**
     * Parameters for update_privacy
     *
     * @return external_function_parameters
     */
    public static function update_privacy_parameters() {
        return new external_function_parameters(
            array(
                'data' => new external_value(PARAM_RAW, 'JSON-formatted profile data', VALUE_REQUIRED),
            ));
    }

    /**
     * update_privacy return
     *
     * @return external_description
     */
    public static function update_privacy_returns() {
        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'JSON-formatted version and message')
            )
        );
    }

    /**
     * Webservice GDPR (called from Learning coach when user updates his data privacy status)
     * If $sharedata is false, delete the data from the database for the user
     *
     * @return array with 'data' key - json with code and message
     */
    public static function get_plugin_infos() {
        global $DB, $CFG;

        $infos['pluginversion'] = get_config('block_learningcoach')->version;
        $infos['pluginrelease'] = get_config('block_learningcoach')->release;
        $infos['moodleversion'] = get_config('')->version;
        $infos['moodlerelease'] = get_config('')->release;
        return ['data' => json_encode(['errorcode' => 'info', 'message' => $infos])];
    }

    /**
     * Parameters for get_plugin_infos
     *
     * @return external_function_parameters
     */
    public static function get_plugin_infos_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * get_plugin_infos return
     *
     * @return external_description
     */
    public static function get_plugin_infos_returns() {
        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'JSON-formatted version and message')
            )
        );
    }



    /**
     * Webservice to get the logs (for the Learning Coach)
     *
     * @return array with 'data' key - json with code and message
     */
    public static function get_logs() {
        global $DB, $CFG;

        $infos['pluginversion'] = get_config('block_learningcoach')->version;
        $infos['pluginrelease'] = get_config('block_learningcoach')->release;
        $infos['moodleversion'] = get_config('')->version;
        $infos['moodlerelease'] = get_config('')->release;
        return ['data' => json_encode(['errorcode' => 'info', 'message' => $infos])];
    }

    /**
     * Parameters for get_logs
     *
     * @return external_function_parameters
     */
    public static function get_logs_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * get_logs return
     *
     * @return external_description
     */
    public static function get_logs_infos_returns() {
        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'JSON-formatted version and message')
            )
        );
    }
}

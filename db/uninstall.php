<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Code that is executed before the tables and data are dropped during the plugin uninstallation.
 *
 * @package     block_learningcoach
 * @category    upgrade
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Custom uninstallation procedure.
 */
function xmldb_block_learningcoach_uninstall() {

    // Webservice functions are automatically deleted when the plugin is uninstalled (as there are defined in services.php).
    // The webservice is also automatically deleted when the plugin is uninstalled (as it is defined in services.php).

    global $DB;

    // removes LearningCoach cohort
    $DB->delete_records('cohort', ['idnumber' => 'LearningCoachTraindy']);
    // removes the user for the webservice
    $DB->delete_records('user', ['username' => 'noreply@traindy.io']);
    // removes the role for the webservice
    $DB->delete_records('role', ['shortname' => 'ws_traindy']);

    return true;
}

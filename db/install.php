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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     block_learningcoach
 * @category    upgrade
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Custom code to be run on installing the plugin.
 */
use block_learningcoach\Lc;

/**
 * Block learningcoach install class.
 *
 * @package     block_learningcoach
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_block_learningcoach_install() {
    global $DB;

    $mylc = new Lc();

    $createcohorte = $mylc->create_lc_cohort();
    $createwsuser = $mylc->create_lc_ws_user();
    $wsrole = $mylc->create_ws_role();

    if ($createcohorte === false || $createwsuser === false || $wsrole === false ) {
        throw new moodle_exception('errorinstall', 'block_learningcoach');
    }

    return true;
}

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
 * navbar's groups ans learners page
 *
 * @package     block_learningcoach
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$courseid = $_GET['cid'];


$top = array();

$url = new \moodle_url('/blocks/learningcoach/viewlearners.php', ['cid' => $courseid]);
$top[] = new \tabobject('learners', $url, get_string('learners', 'block_learningcoach'));

$url = new \moodle_url('/blocks/learningcoach/viewgroups.php', ['cid' => $courseid]);
$top[] = new tabobject('groups', $url, get_string('groups', 'block_learningcoach'));


print_tabs([$top], $currenttab);

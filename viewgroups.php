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
 * View of groups page
 *
 * @package     block_learningcoach
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_login();

use block_learningcoach\Lc;

global $USER, $DB, $PAGE, $OUTPUT;

$courseid = optional_param('cid', SITEID, PARAM_INT);

if (! $course = $DB->get_record("course", array('id' => $courseid)) ) {
    throw new moodle_exception('errorcourse', 'block_learningcoach');
}

// Force user login in course (SITE or Course).
if ($course->id == SITEID) {
    require_login();
    $context = \context_system::instance();
} else {
    require_login($course->id);
    $context = \context_course::instance($course->id);
}

$mylc = new Lc();

$groups = $mylc->get_groups($courseid);

$param = ['id' => $USER->id];

$PAGE->set_context($context);

$PAGE->set_pagelayout('base');

$PAGE->set_url('/blocks/learningcoach/viewgroups.php', $param);
$PAGE->set_title(get_string('titlegroups', 'block_learningcoach'));
$PAGE->set_heading('Learning Coach');
$PAGE->navbar->add(get_string('pluginname', 'block_learningcoach'));

// Use for lcnavbar current tab.
$currenttab = 'groups';

echo $OUTPUT->header();

if (!has_capability('block/learningcoach:viewteacherblock', $context)) {
    echo '<p class="path-block-learningcoach error"><b>'.get_string('warningaccess', 'block_learningcoach').'</b></p>';
    return;
};


require('lcnavbar.php');


echo '<h1>'.get_string('titlegroupscourse', 'block_learningcoach').'</h1>';
if (empty($groups)) {
    echo '<p>'.get_string('nogroup', 'block_learningcoach').'</p>';
} else {
    echo '<ul>';
    foreach ($groups as $key => $value) {
        echo '<li><a href ='.new \moodle_url(
            '/blocks/learningcoach/viewgrouplearners.php',
            ['gid' => $value->id, 'cid' => $value->courseid]
        ).'>'. $value->name .'</a></li>';
    }
    echo '</ul>';
}

echo $OUTPUT->footer();

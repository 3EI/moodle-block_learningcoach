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
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once( '../../config.php' );
require_login();

use block_learningcoach\Lc;

global $USER, $OUTPUT, $DB, $COURSE, $PAGE;

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

$learners = $mylc->get_learners($courseid);

$context = context_course::instance($COURSE->id);

$param = ['id' => $courseid];

$PAGE->set_context($context);
$PAGE->set_pagelayout('base');

$PAGE->set_url('/blocks/learningcoach/viewlearners.php', $param);
$PAGE->set_title(get_string('titlelearners', 'block_learningcoach'));
$PAGE->set_heading('Learning Coach');
$PAGE->navbar->add(get_string('pluginname', 'block_learningcoach'));

// Use for lcnavbar current tab.
$currenttab = 'learners';

echo $OUTPUT->header();

if (!has_capability('block/learningcoach:viewteacherblock', $context)) {
    echo "<p class='path-block-learningcoach error'><b>".get_string('warningaccess', 'block_learningcoach')."</b></p>";
    return;
};


require('lcnavbar.php');

echo '<h1>'.get_string('learners', 'block_learningcoach').'</h1>';
echo count($learners).' '.get_string('learners', 'block_learningcoach');
echo '<table class="table">';
    echo '<thead>';
        echo '<tr>';
            echo '<th scope="col">'.get_string('tablelastname', 'block_learningcoach').'</th>';
            echo '<th scope="col">'.get_string('tablefirstname', 'block_learningcoach').'</th>';
            echo '<th scope="col">'.get_string('tableprofile', 'block_learningcoach').'</th>';
        echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
foreach ($learners as $key => $value) {
    echo '<tr>';
        echo '<td>'.$value->user_lastname.'</td>';
        echo '<td>'.$value->user_firstname.'</td>';
    if ( $value->data_acces == 1) {
        echo '<td><a href ='.new \moodle_url('/blocks/learningcoach/viewprofile.php',
        ['id' => $value->fk_moodle_user_id]).'>'.get_string('seeprofile', 'block_learningcoach').'</a></td>';
    } else {
        echo '<td>'.get_string('sharedata', 'block_learningcoach').'</td>';
    }
    echo '</tr>';
}
    echo '</tbody>';
echo '</table>';

echo $OUTPUT->footer();

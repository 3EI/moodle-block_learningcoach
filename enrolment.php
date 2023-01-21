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
 * Enrolment page -- Tested ok by LM 2022-09-06
 *
 * @package    block_learningcoach
 * @copyright  2022 Traindy / 3E-Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_login();

use block_learningcoach\Lc;
global $DB, $USER, $COURSE, $DB, $PAGE, $CFG, $OUTPUT;


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


$param = ['cid' => $_GET['cid']];

// Set up the page.
$PAGE->set_context($context);
$PAGE->set_pagelayout('base');
$PAGE->set_url('/blocks/learningcoach/enrolment.php', $param);
$PAGE->set_title(get_string('enrolment', 'block_learningcoach'));
$PAGE->set_heading('Learning Coach');

// Doing render.
echo $OUTPUT->header();

// Display form.
if ((isset($_GET['u']) && ($_GET['u'] != null))) {

    // If user dipslaying the page and user id is the same user.
    if ($_GET['u'] == $USER->id) {
        $mylc = new Lc();
        if ($mylc->lcenrolmentpolicy == "learner") {
            // Enrol the user:
            $enrolresult = $mylc->enrol_in_lc_cohort($USER->id);
            // Enrolment is ok:
            if ($enrolresult['code'] == 1) {
                echo "<p>" . get_string('enrolment_success', 'block_learningcoach') . "</p>";
                $url = new \moodle_url('/');
                $label = get_string('back_home', 'block_learningcoach');
                echo html_writer::link($url, $label);
            } else {
                echo $enrolresult['data'];
            }
        } else {
            echo "<p>" . get_string('enrolment_settings', 'block_learningcoach') . "</p>";
        }
    } else {
        echo get_string('enrolment_self', 'block_learningcoach');
    }
} else {
    // If form have been validated:
    echo get_string('enrolment_validate', 'block_learningcoach');
    $paramstosend = array('validate' => '1', 'cid' => $param['cid'], 'u' => $USER->id);
    $url = new \moodle_url('/blocks/learningcoach/enrolment.php', $paramstosend);
    $button = $OUTPUT->single_button($url, get_string('validate', 'block_learningcoach'), 'get', array('class' => ''));
    $output = \html_writer::div($button, 'mod_quiz-next-nav');
    echo $output;
}
echo $OUTPUT->footer();

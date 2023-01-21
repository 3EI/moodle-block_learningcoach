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

global $USER, $DB, $COURSE, $SESSION , $PAGE, $OUTPUT, $CFG;


use block_learningcoach\Lc;

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

$context = context_course::instance($COURSE->id);

$userid = $_GET['id'];

 // False if not found.
$lcuser = $DB->get_record_select("block_learningcoach_users", 'fk_moodle_user_id = ?', [$userid]);



if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
} else {
    $lang = $USER->lang;
}

$param = ['id' => $userid];
$userlearner = $DB->get_record_select('user', 'id = ?', [$userid]);



$PAGE->set_context($context);
$PAGE->set_pagelayout('base');
$PAGE->set_url('/blocks/learningcoach/viewprofile.php', $param);

$PAGE->navbar->add(get_string('pluginname', 'block_learningcoach'));

if ($userlearner === false) {
    echo $OUTPUT->header();
    echo get_string('no_user', 'block_learningcoach');
} else {
    if ($userid == $USER->id) {
        $PAGE->set_title(get_string('titleprofile', 'block_learningcoach'));
    } else {
        $PAGE->set_title(get_string('titleprofileof', 'block_learningcoach') .
            ' ' . $userlearner->firstname . ' ' . $userlearner->lastname);
    }

    $PAGE->set_heading('Learning Coach');

    echo $OUTPUT->header();


    // Checks if user is registered to LC or not.
    if (!$lcuser) {
        throw new moodle_exception('erroruserlcnotfound', 'block_learningcoach');
    } else {

        // Page content.
        if ($userid == $USER->id) {
            echo '<h1>' . get_string('titleprofile', 'block_learningcoach') . '</h1>';
        } else {
            echo '<div class="mb-5">';
            echo '<a href="javascript:history.back()">< ' . get_string('backlist', 'block_learningcoach') . '</a>';
            echo '</div>';
            echo '<h1>' . get_string('titleprofileof', 'block_learningcoach') . '' . $userlearner->firstname .
                ' ' . $userlearner->lastname . '</h1>';
        }
        // If user profile has not been get yet.
        if ($lcuser->time_updated == 0 || is_null($lcuser->time_updated)) {
            $mylc = new Lc();
            $mylc->update_profile_user($userid);
            echo  get_string('update2', 'block_learningcoach');
        } else {
            // Display profile data.
            echo get_string('update', 'block_learningcoach'). ' ' . date('d/m/Y H:i', $lcuser->time_updated) . '<br/><br/>';

            if (!$lcuser->data_acces) {
                echo "<br>" . get_string('sharedata', 'block_learningcoach');
            } else {

                $mylc = new Lc();

                $results = $mylc->get_scores_infos($userid, $lang);

                if (count($results) == 0) {
                    $mylc->update_profile_user($userid, true);
                    $results = $mylc->get_scores_infos($userid, $lang);
                }
                $tabscore = [];
                $tabdimension = [];
                $tabinfoconstruct = [];
                foreach ($results as $key => $result) {
                    $dimensionname = $result->dimension_name;
                    $constructname = $result->construct_name;
                    $constructtag = $result->construct_tag;
                    $constructdescription = $result->construct_description;
                    $score = $result->construct_score;

                    // To get graph's labels (= dimensions name).
                    array_push($tabdimension, $dimensionname);

                    // To be able to average the constructs for the graph.
                    $tabscore[$dimensionname][$constructtag] = $score;

                    $tabinfoconstruct[$dimensionname][$constructtag] =
                        ['construct_name' => $constructname, 'construct_description' => $constructdescription, 'score' => $score];
                }

                // Reindexing the dimension name array, with unique values.
                $array1 = array();
                $array2 = array_unique($tabdimension);
                $dimensionsnames = array_merge($array1, $array2);

                // Average of scores/dimensions.
                $scoreaverage = [];

                foreach ($tabscore as $key) {
                    $result = round(array_sum($key) / count($key));
                    array_push($scoreaverage, $result);
                }


                if (empty($results)) {
                    echo '<div class="mt-3">';
                    if ($userid == $USER->id) {
                        echo '<p>' . get_string('uncompleteprofile', 'block_learningcoach') . '</p>';
                    } else {
                        echo '<p>' . get_string('uncompleteprofileotheruser', 'block_learningcoach') . '</p>';
                    }
                    echo '</div>';
                } else {
                    echo '<section style="width: 60%; margin: auto;">';

                    $datas = new core\chart_series('Moyenne / dimensions', $scoreaverage);

                    $chart = new core\chart_bar();
                    $yaxis = $chart->get_yaxis(0, true);
                    $yaxis->set_max(100);
                    $chart->add_series($datas);
                    $chart->set_labels($dimensionsnames);
                    $chart->set_legend_options(['display' => false]);
                    $CFG->chart_colorset = ['#36a2eb'];
                    echo $OUTPUT->render($chart);

                    echo '</section>';

                    echo '<h2 class="mb-5">' . get_string('titlescoredetail', 'block_learningcoach') . '</h2>';

                    echo '<ul class="list-group list-group-flush mt-3">';
                    foreach ($tabinfoconstruct as $key => $construct) {
                        echo '<li class="list-group-item"><span class="path-block-learningcoach dimension">' . ($key) . '</span>';
                        echo '<ul>';
                        foreach ($construct as $constructinfo) {
                            echo '<li class="path-block-learningcoach construct"><span class="path-block-learningcoach construct">'
                                . ($constructinfo['construct_name']) .
                                ' (score : ' . $constructinfo['score'] . '/100)</span>';
                            echo '<br>' . ($constructinfo['construct_description']) . '</li>';
                        }
                        echo '</ul>';
                        echo '</li>';
                    }
                    echo '</ul>';
                }
            }
        }
    }

}

echo $OUTPUT->footer();

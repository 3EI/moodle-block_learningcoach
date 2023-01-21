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

global $USER, $DB, $PAGE, $COURSE , $OUTPUT;

$PAGE->requires->js(new moodle_url('/blocks/learningcoach/javascript/plotly-2.12.1.min.js'), true);


$groupid = $_GET['gid'];

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
} else {
    $lang = $USER->lang;
}

$courseid = optional_param('cid', SITEID, PARAM_INT);

if (! $course = $DB->get_record("course", array('id' => $courseid)) ) {
    throw new moodle_exception('errorcourse', 'block_learningcoach');
}

// Force user login in course (SITE or Course).
if ($courseid == SITEID) {
    require_login();
    $context = \context_system::instance();
} else {
    require_login($courseid);
    $context = \context_course::instance($courseid);
}

$mylc = new Lc();

$group = $DB->get_record("groups", array('id' => $groupid));
// Get the learners belonging to a group.
$grouplearners = $mylc->get_group_learners($groupid, $courseid);

$groupstatdatas = $mylc->profile_group($groupid, $courseid);
$profilegroup = json_decode($groupstatdatas->datas, true);

$context = context_course::instance($COURSE->id);

$param = ['gid' => $groupid, 'cid' => $courseid];

$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');

$PAGE->set_url('/blocks/learningcoach/viewgrouplearners.php', $param);
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
?>
<div class="mb-5">
    <?php echo '<a href="javascript:history.back()">< '.get_string('backlist', 'block_learningcoach').'</a>';?>
</div>

<?php
echo '<h2 class="mb-5">'.get_string('titlestatdetail', 'block_learningcoach').'</h2>';

echo '<blockquote><p class="mb-0 path-block-learningcoach">'.get_string('constructdesc', 'block_learningcoach').'</p></blockquote>';

echo get_string('update', 'block_learningcoach') .' '.date('d/m/Y H:i', $groupstatdatas->time_updated).'<br/><br/>';
if ($profilegroup['code'] == 200) {
    $datas = json_decode($profilegroup['content']);

    // Create arrays for graph values.
    $tabdimensions = [];

    foreach ($datas as $key => $value) {
        $dimensions = $value->scales;
        $cpt = 0;

        foreach ($dimensions as $dimension => $valuedimension) {
            $x = [];
            $min = [];
            $q1 = [];
            $median = [];
            $q3 = [];
            $max = [];
            $datanull = true;

            $dim = $DB->get_record_select('block_learningcoach_dim', 'ref = ?', [$dimension]);
            $dimname = $DB->get_record_select('block_learningcoach_dim_tra', 'fk_id_dimension = ? AND lang = ?', [$dim->id, $lang]);

            echo '<h2>'. $dimname->name .'</h2>';

            foreach ($valuedimension as $construct => $valueconstruct) {
                if (!is_null($valueconstruct)) {
                    $datanull = false;

                    $dbconstruct = $DB->get_record("block_learningcoach_const", array('tag' => $construct));
                    $constructname = $DB->get_record_select('block_learningcoach_cons_tra', 'fk_id_construct = ? AND lang = ?', [$dbconstruct->id, $lang]);
                    array_push($x, $constructname->name);
                    array_push($min, $valueconstruct->min);
                    array_push($q1, $valueconstruct->q1);
                    array_push($median, $valueconstruct->median);
                    array_push($q3, $valueconstruct->q3);
                    array_push($max, $valueconstruct->max);

                } else {
                        $construct = new \stdClass();
                        $construct->n = "ND";
                        $construct->min = "ND";
                        $construct->q1 = "ND";
                        $construct->mean = "ND";
                        $construct->median = "ND";
                        $construct->q3 = "ND";
                        $construct->max = "ND";
                        $construct->sd = "ND";
                }

            }

            $tabdimensions[$cpt]['x'] = $x;
            $tabdimensions[$cpt]['min'] = $min;
            $tabdimensions[$cpt]['q1'] = $q1;
            $tabdimensions[$cpt]['median'] = $median;
            $tabdimensions[$cpt]['q3'] = $q3;
            $tabdimensions[$cpt]['max'] = $max;


            if (!$datanull) {
                echo '<div id="graph'.$cpt.'"></div>';
            } else {
                echo '<p><b>'.get_string('warning3', 'block_learningcoach').'</b></p>';
            }
            $cpt++;
        }
    }
    echo '<hr>';

    echo '<h1>'.get_string('titlegroupmembers', 'block_learningcoach').' : '.$group->name.'</h1>';

    echo '<table class="table mt-5">';
        echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">'.get_string('tablelastname', 'block_learningcoach').'</th>';
            echo '<th scope="col">'.get_string('tablefirstname', 'block_learningcoach').'</th>';
            echo '<th scope="col">'.get_string('tableprofile', 'block_learningcoach').'</th>';
            echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
    foreach ($grouplearners as $key => $value) {
        echo '<tr>';
            echo '<td>'.$value->user_lastname.'</td>';
            echo '<td>'.$value->user_firstname.'</td>';
        if ( $value->data_acces == 1) {
            echo '<td><a href ='.new \moodle_url('/blocks/learningcoach/viewprofile.php',
            ['id' => $value->fk_moodle_user_id, 'cid' => $courseid]).'>'.get_string('seeprofile', 'block_learningcoach').'</a></td>';
        } else {
            echo '<td>'.get_string('sharedata', 'block_learningcoach').'</td>';
        }
        echo '</tr>';
    }
        echo '</tbody>';
    echo '</table>';

} else if ($profilegroup['code'] == 422) {
    echo '<p><b>'.get_string('warning1', 'block_learningcoach').'</b></p>';
    echo '<p>'.get_string('warning2', 'block_learningcoach').'</p>';

} else {
    echo '<p><b>'.get_string('warning4', 'block_learningcoach').'</b></p>';
}

?>


<?php
echo $OUTPUT->footer();
?>

<script>

var tabdimensions = <?php echo json_encode($tabdimensions); ?>;
console.log(tabdimensions)

// Browse the dimension table to retrieve construct values
for (let y of Object.keys(tabdimensions)) {

    var idgraph = 'graph' + y;
    var graph = document.getElementById(idgraph);

    if ( graph != null){

        let plotvalues = tabdimensions[y];

        var x = plotvalues['x'];
        var q1 = plotvalues['q1'];
        var median = plotvalues['median'];
        var q3 = plotvalues['q3'];
        var min = plotvalues['min'];
        var max = plotvalues['max'];

        Plotly.newPlot(graph,[{
            "type": "box",
            "name": "Test",
            "x": x,
            "offsetgroup": "2",
            "q1": q1,
            "median": median,
            "q3": q3,
            "lowerfence": min,
            "upperfence": max
            }], {
        boxmode: 'group',
        legend: {
            x: 0,
            y: 1, yanchor: 'bottom'
            },
        yaxis: {
            range: [0, 100],
            zeroline: true,
            zerolinecolor: '#A6A6A6',
            },

        xaxis: {
            zeroline: true,
            zerolinecolor: '#A6A6A6',
        }
        });
    }
};


</script>

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

namespace block_learningcoach\output;

use renderable;
use renderer_base;
use templatable;

use block_learningcoach\Lc;

/**
 * Learner Profile renderable class - Block output class for user with both learner and teacher roles.
 * @package    block_learningcoach
 * @copyright  2022 Traindy/3E Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mixtbloc_view implements renderable, templatable {
    /**
     * @var object An object containing the configuration information for the current instance of this block.
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param object $config An object containing the configuration information for the current instance of this block.
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $USER, $OUTPUT, $DB, $COURSE;

        $mylc = new Lc();

        $param = ['id' => $USER->id];
        $urlprofile = new \moodle_url('/blocks/learningcoach/viewprofile.php', $param);

        if (isset($_GET['lang'])) {
            $lang = $_GET['lang'];
        } else {
            $lang = $USER->lang;
        }

        $sql = "SELECT cs.id, cs.construct_score,
                ct.name AS construct_name
                FROM {block_learningcoach_cons_sco} cs
                JOIN {block_learningcoach_const} c ON c.id = cs.fk_id_construct
                JOIN {block_learningcoach_cons_tra} ct ON ct.fk_id_construct = c.id
                WHERE cs.fk_moodle_user_id = $USER->id AND ct.lang = :lang
                ORDER BY cs.construct_score DESC
                LIMIT 3
            ";

        $score = $DB->get_records_sql($sql, ['lang' => $lang]);

        $lcuser = $DB->get_record('block_learningcoach_users', ['fk_moodle_user_id' => $USER->id]);

        // ! Mis ici actuellement pour éviter erreur php - en attente d'avancement.
        // For condition in Mustache template.
        $dataaccess = false;
        if ($lcuser) {
            $dataaccess = (bool) $lcuser->data_acces;
        }

        $context = \context_course::instance($COURSE->id);
        $urlgroups = new \moodle_url('/blocks/learningcoach/viewgroups.php', array('cid' => $COURSE->id));
        $urllearners = new \moodle_url('/blocks/learningcoach/viewlearners.php', array('cid' => $COURSE->id));

        if (!empty ($score)) {
            $forces = [];
            foreach ($score as $key => $value) {
                array_push($forces, $value->construct_name);
            }

            // For condition in Mustache template.
            $profile = true;

            $datas = array(
                'profile' => $profile,
                'Force1' => $forces[0],
                'Force2' => $forces[1],
                'Force3' => $forces[2],
                'url_profile' => $urlprofile,
                'data_access' => $dataaccess,
                'lc_user' => $lcuser,
                'url_groups' => $urlgroups,
                'url_learners' => $urllearners,
            );

        } else {

            // For condition in Mustache template.
            $profile = false;

            $datas = array(
                'profile' => $profile,
                'url_profile' => $urlprofile,
                'data_access' => $dataaccess,
                'lc_user' => $lcuser,
                'url_groups' => $urlgroups,
                'url_learners' => $urllearners,
            );

        }

        return $datas;
    }

}

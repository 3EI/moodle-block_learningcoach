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
 * Block learningcoach main file.
 *
 * @package     block_learningcoach
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_learningcoach\Lc;

/**
 * Block learningcoach class.
 *
 * @package     block_learningcoach
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_learningcoach extends block_base {
    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_learningcoach');
    }


    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {

        if ($this->content !== null) {
            return $this->content;
        }

        global $USER, $DB, $COURSE;
        $context = $this->page->context;

        // Homepage.
        if ($COURSE->id == SITEID) {

            $context = \context_system::instance();
        } else {

            $context = \context_course::instance($COURSE->id);
        }

        $viewteacherblock = false;
        $viewlearnerblock = false;

        if (has_capability('block/learningcoach:viewteacherblock', $context)) {
            $viewteacherblock = true;
        }

        if (has_capability('block/learningcoach:viewlearnerblock', $context)) {
            $viewlearnerblock = true;
        }

        // Get the user role.
        $sql = "SELECT ra.roleid, ra.contextid, r.shortname, ra.userid
                FROM {role_assignments} ra
                LEFT JOIN {role} r ON r.id = ra.roleid
                WHERE userid = $USER->id
                GROUP BY ra.roleid
                #AND ra.contextid=$context->id
                ";
        $mylc = new Lc();

        $role = $DB->get_records_sql($sql);

        $isstudent = false;
        if (array_search('student', array_column($role, 'shortname')) !== false) {
            $isstudent = true;
        }

        // If on dashboard.
        if ($this->page->pagetype == 'my-index') {
            // If student.
            if ($isstudent) {
                $lcuser = $DB->get_record('block_learningcoach_users', ['fk_moodle_user_id' => $USER->id]);
                // If user is registered in block_learningcoach_users.
                if ($lcuser !== false) {
                    $renderable = new \block_learningcoach\output\learner_profile($this->config);
                    $this->title = get_string('learnerprofile', 'block_learningcoach');
                } else {
                    // If user not yet enrolled in Learning Coach App.
                    // If setting on enrol is set learner => display message with enrol button.
                    if ($mylc->lcenrolmentpolicy == 'learner') {
                        $renderable = new \block_learningcoach\output\learner_register($this->config);
                        $this->title = get_string('learnerprofile', 'block_learningcoach');
                    }
                }

            }
        } else {
            // On course view.
            // If student.
            if ($isstudent) {
                $lcuser = $DB->get_record('block_learningcoach_users', ['fk_moodle_user_id' => $USER->id]);
                if ($lcuser !== false) {
                    $renderable = new \block_learningcoach\output\learner_profile($this->config);
                    $this->title = get_string('learnerprofile', 'block_learningcoach');
                    // If student and teacher.
                    if ($viewteacherblock) {
                        $renderable = new \block_learningcoach\output\mixtbloc_view($this->config);
                    }
                } else {
                    // If user not yet enrolled in Learning Coach App.
                    // If setting on enrol is set learner => display message with enrol button.
                    if ($mylc->lcenrolmentpolicy == 'learner') {
                        $renderable = new \block_learningcoach\output\learner_register($this->config);
                        $this->title = get_string('learnerprofile', 'block_learningcoach');
                    }

                    // If student and teacher.
                    if ($viewteacherblock) {
                        $renderable = new \block_learningcoach\output\mixtbloc_view($this->config);
                        $this->title = get_string('blocksubtitle', 'block_learningcoach');
                    }
                }
            } else {
                // If teatcher.
                if ($viewteacherblock) {
                    $renderable = new \block_learningcoach\output\teacher_courses($this->config);
                    $this->title = get_string('formercourses', 'block_learningcoach');
                }
            }

        }

        /*if (array_search('student', array_column($role, 'shortname')) !== false) {
            $renderable = new \block_learningcoach\output\learner_profile($this->config);
            $this->title = get_string('learnerprofile', 'block_learningcoach');

            if (array_search('editingteacher', array_column($role, 'shortname')) !== false) {
                $renderable = new \block_learningcoach\output\mixtbloc_view($this->config);
            }
        } else {
            var_dump($this->page->pagetype);
            if ($this->page->pagetype == 'course-view-topics') {
                $renderable = new \block_learningcoach\output\teacher_courses($this->config);
                $this->title = get_string('formercourses', 'block_learningcoach');
            }
             } else {
                $renderable = new \block_learningcoach\output\teacher_courses($this->config);
                $this->title = get_string('formercourses', 'block_learningcoach');
            }
        }*/

        if (isset($renderable)) {
            $renderer = $this->page->get_renderer('block_learningcoach');

            $this->content = new stdClass();
            $this->content->text = $renderer->render($renderable);
            $this->content->footer = '';

            return $this->content;
        } else {
            return;
        }

    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_learningcoach');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return boolean Array of pages and permissions.
     */
    public function applicable_formats() {
        return array(
            'all' => true,
            'site' => false,
            'site-index' => false,
            'course-view' => true,
            'my' => true
        );
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return true;
    }

    /**
     * Description
     *
     * @return bollean
     */
    public function instance_allow_config() {
        return true;
    }


}

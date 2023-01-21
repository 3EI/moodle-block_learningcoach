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
 * block LearningCoach renderer
 *
 * @package    block_learningcoach
 * @copyright  2022 Traindy/3E Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_learningcoach\output;


use plugin_renderer_base;
use renderable;

/**
 * Block LearningCoach renderer class.
 *
 * @package    block_learningcoach
 * @copyright  2022 Traindy/3E Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Defer to template
     * @param renderable $page
     * @return string HTML string
     */
    public function render_learner_register(renderable $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('block_learningcoach/learner_register', $data);
    }

    /**
     * Defer to template
     * @param renderable $page
     * @return string HTML string
     */
    public function render_learner_profile(renderable $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('block_learningcoach/learner_profile', $data);
    }

    /**
     * Defer to template
     * @param renderable $page
     * @return string HTML string
     */
    public function render_former_dashboard(renderable $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('block_learningcoach/teacher_dashboard', $data);
    }

    /**
     * Defer to template
     * @param renderable $page
     * @return string HTML string
     */
    public function render_former_courses(renderable $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('block_learningcoach/teacher_courses', $data);
    }
}

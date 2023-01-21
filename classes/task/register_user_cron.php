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
 * Legacy Cron Quiz Reports Task
 *
 * @package    block_learningcoach
 * @copyright  2022 Traindy/3E Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_learningcoach\task;

use block_learningcoach\Lc;

/**
 * Legacy Cron Learing Coach Task
 *
 * @package    block_learningcoach
 * @copyright  2022 Traindy/3E Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class register_user_cron extends \core\task\scheduled_task {

    /**
     * Get the name of the task for use in the interface.
     *
     * @return string
     */
    public function get_name() {
        return get_string('registerusercron', 'block_learningcoach');
    }

    /**
     * Execute register user block_learningcoach cron tasks.
     */
    public function execute() {
        $mylc = new Lc();
        $mylc->log_error_clean_older_recs();
        return $mylc->get_cohort_users();
    }
}

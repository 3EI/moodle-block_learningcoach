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

namespace block_learningcoach\tests;

use block_learningcoach\Lc;

use ReflectionClass;
use advanced_testcase;
use block_accessreview;
use context_course;


/**
 * PHPUnit block_learningcoach tests
 *
 * @package     block_learningcoach
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class task_register_user_cron extends advanced_testcase {

    /**
     * Running the Learning Coach cron task
     * Register users to LC when they are registered in the Learning Coach cohort
     */
    public function test_cron() {
        $this->resetAfterTest(true);

        $testcron = new \block_learningcoach\task\register_user_cron();
        $result = $testcron->execute();

        $this->assertJson('{"code": "200", "data" : "Aucun utilisateur à envoyer"}', $result['data']);
    }
}

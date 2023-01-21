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
 * @coversDefaultClass \block_learningcoach\External
 */
class external_test extends advanced_testcase {

    /**
     * Get Webservice Status - test
     *
     * @covers ::get_status
     */
    public function test_webservice_status() {

        $this->resetAfterTest(true);

        $testws = new \block_learningcoach\external();
        $result = $testws::get_status();
        $this->assertJsonStringEqualsJsonString('{"version": "0.1", "message": "Up and running"}', $result['data']);

    }

    /**
     * Update Webservice - test
     *
     * @covers ::update_profile
     */
    public function test_webservice_update() {

        $this->resetAfterTest(true);
        $mylc = new Lc();

        $user = $this->getDataGenerator()->create_user(array(
            'email' => 'user1@testlc.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));

        $oneprofile = [
                'user_id' => is_int($user->id),
                'meta'    => [
                    'version'           => 1,
                    'aptitude_taken_on' => '2020-01-01',
                    'attitude_taken_on' => '2020-01-01',
                    'autonomy_taken_on' => '2020-01-01',
                    'context_taken_on'  => '2020-01-01',
                ],
                'scales' => [

                    'aptitude' => [
                        'SEN' => 100,
                        'SDE' => 100,
                        'MEM' => 100,
                        'ATT' => 100,
                    ],

                    'attitude' => [
                        'OUV' => 100,
                        'PLA' => 100,
                        'VIS' => 100,
                        'ENG' => 100,
                        'CON' => 100,
                    ],
                    'autonomy' => [

                        'OBJ' => 100,
                        'TEM' => 100,
                        'EMO' => 100,
                        'INF' => 100,
                        'CRI' => 100,
                        'FEE' => 100,
                    ],

                    'context' => [
                        'SEC' => 100,
                        'OPP' => 100,
                        'NUM' => 100,
                        'RES' => 100,
                    ],
                ],
        ];

        $data = json_encode($oneprofile);

        $testws = new \block_learningcoach\external();
        $result = $testws::update_profile($data);

        $this->assertArrayHasKey('data', $result);
        $this->assertJsonStringEqualsJsonString('{"errorcode":"missing_user","message":"User not found in Moodle"}', $result['data']);
    }

    /**
     * Update Privacy - test
     *
     * @covers ::update_privacy
     */
    public function test_update_privacy() {
        $data = [
            'data' => json_encode([
                'user_id' => 20,
                'share_data' => false,
            ]),
        ];

        $testupdate = new \block_learningcoach\external();
        $result = $testupdate::update_privacy(json_encode($data));

        // $this->assertJsonStringEqualsJsonString('{"errorcode" => "nodeletion", "message" => "Update_privacy called with sharedata = true, nothing has been done"}', $result);

        $this->assertArrayHasKey('data', $result);
    }
}

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

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;
Use PHPUnit\DbUnit\Database\Connection;

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
class lc_test extends advanced_testcase {

    /**
     * Test Class LC Connexion à l'API
     * @return Json
     * @return Array
     */
    public function test_api_connexion() {
        $mylc = new Lc();

        $result = $mylc->callapi("/health", false);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('content', $result);

    }

    /**
     * Test Class LC API Connexion with key
     * @return Json
     */
    public function test_api_key() {
        $mylc = new Lc();

        $result = $mylc->callapi("/key", false);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('content', $result);
    }

    /**
     * Test Class LC get Learning Coach Constructs
     * @return Json
     */
    public function test_api_construct() {

        $this->resetAfterTest(true);
        $this->setAdminUser();

        $mylc = new Lc();

        $result = $mylc->fill_scales_constructs();

        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('data', $result);
    }

    /**
     * Test Class LC Enrol a user Moodle in Learning Coach
     * @return Json
     * @return Array
     */
    public function test_api_users() {
        $this->resetAfterTest(true);

        $mylc = new Lc();

        $user1 = $this->getDataGenerator()->create_user(array(
            'email' => 'user1@3e-innovation.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));

        $user2 = $this->getDataGenerator()->create_user(array(
            'email' => 'user2@3e-innovation.com',
            'firstname' => 'User2',
            'lastname' => 'User2',
        ));

        $userstoregister = [$user1, $user2];

        $result = $mylc->register_user($userstoregister);

        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('data', $result);

    }

    /**
     * Test Class LC Learning Coach cohort creation
     * @return Boolean
     */
    public function test_lc_create_cohort() {
        $this->resetAfterTest(true);

        $mylc = new Lc();

        $cohortlc1 = $mylc->create_lc_cohort();
        // Cohort Learning Coach already exists so return false
        $cohortlc2 = $mylc->create_lc_cohort();

        if ($cohortlc1 === false) {
            $this->assertFalse($cohortlc1);
        } else {
            $this->Int($cohortlc1);
        }
        $this->assertFalse($cohortlc2);
    }

    /**
     * Test Class LC Get users in the Learning Coach Cohort and register them in LC app
     * @return Json
     */
    public function test_get_lccohort_users() {
        $this->resetAfterTest(true);

        $mylc = new Lc();

        $cohort = $this->getDataGenerator()->create_cohort(array('name' => 'LearningCoach'));

        $user1 = $this->getDataGenerator()->create_user(array(
            'email' => 'user1@3e-innovation.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));
        $result = $mylc->get_cohort_users();

        $this->assertJson(200, $result['code']);
    }


    /**
     * Test Class LC create  user Webservice Learning Coach
     * @return Boolean
     */
    public function test_lc_create_ws_user() {

        $this->resetAfterTest(true);

        $mylc = new Lc();
        $result = $mylc->create_lc_ws_user();
        $result2 = $mylc->create_lc_ws_user();

        if ($result === false) {
            $this->assertFalse($result);
        } else {
            $this->assertIsInt($result);
        }

        $this->assertFalse($result2);
    }

    /**
     * Test Class LC : lc webservice role attribution to user Traindy Webservice
     * @return Boolean
     */
    public function test_lc_webservice_role() {
        $this->resetAfterTest(true);

        $mylc = new Lc();
        $result = $mylc->create_ws_role();

        $this->assertTrue($result);
    }

    /**
     * Test Class LC Creation & get token webservice Learning Coach
     * @return String
     */
    public function test_get_ws_token() {

        $this->resetAfterTest(true);

        $mylc = new Lc();

        $result = $mylc->get_ws_token();

        $this->assertIsString($result);
    }

    /**
     * Test Class LC indsert api's datas in Databse for profile Learning Coach group update
     * @return StdClass
     */
    public function test_lc_profile_group() {
        global $DB;

        $this->resetAfterTest(true);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        $course = $this->getDataGenerator()->create_course();
        $group = $this->getDataGenerator()->create_group(array('courseid' => $course->id));

        $groupuser1 = $this->getDataGenerator()->create_group_member(array('userid' => $user1->id, 'groupid' => $group->id));
        $groupuser2 = $this->getDataGenerator()->create_group_member(array('userid' => $user2->id, 'groupid' => $group->id));
        $groupuser3 = $this->getDataGenerator()->create_group_member(array('userid' => $user3->id, 'groupid' => $group->id));

        $insertvalues = [];
        array_push($insertvalues, '{"code":200,"content":"{\"data\":{\"meta\":{\"version\":1,\"group_size\":3},
        \"scales\":{
        \"aptitude\":{
        \"SEN\":{\"n\":3,\"min\":16,\"q1\":40,\"mean\":53,\"median\":64,\"q3\":71,\"max\":78,\"sd\":32.5},
        \"SDE\":{\"n\":3,\"min\":22,\"q1\":25,\"mean\":34,\"median\":28,\"q3\":40,\"max\":51,\"sd\":15.3},
        \"MEM\":{\"n\":3,\"min\":17,\"q1\":20,\"mean\":39,\"median\":22,\"q3\":50,\"max\":78,\"sd\":33.9},
        \"ATT\":{\"n\":3,\"min\":16,\"q1\":21,\"mean\":36,\"median\":25,\"q3\":47,\"max\":68,\"sd\":27.8}},
        \"attitude\":{
        \"OUV\":{\"n\":3,\"min\":17,\"q1\":27,\"mean\":49,\"median\":37,\"q3\":65,\"max\":93,\"sd\":39.4},
        \"PLA\":{\"n\":3,\"min\":55,\"q1\":69,\"mean\":79,\"median\":83,\"q3\":91,\"max\":98,\"sd\":21.8},
        \"VIS\":{\"n\":3,\"min\":45,\"q1\":51,\"mean\":63,\"median\":56,\"q3\":72,\"max\":87,\"sd\":21.8},
        \"ENG\":{\"n\":3,\"min\":38,\"q1\":53,\"mean\":58,\"median\":67,\"q3\":68,\"max\":69,\"sd\":17.3},
        \"CON\":{\"n\":3,\"min\":29,\"q1\":30,\"mean\":44,\"median\":30,\"q3\":52,\"max\":73,\"sd\":25.1}},
        \"autonomy\":{
        \"OBJ\":{\"n\":3,\"min\":21,\"q1\":27,\"mean\":51,\"median\":33,\"q3\":66,\"max\":99,\"sd\":42},
        \"TEM\":{\"n\":3,\"min\":16,\"q1\":48,\"mean\":62,\"median\":79,\"q3\":85,\"max\":91,\"sd\":40.3},
        \"EMO\":{\"n\":3,\"min\":14,\"q1\":31,\"mean\":52,\"median\":48,\"q3\":71,\"max\":93,\"sd\":39.6},
        \"INF\":{\"n\":3,\"min\":30,\"q1\":53,\"mean\":67,\"median\":75,\"q3\":86,\"max\":97,\"sd\":34.2},
        \"CRI\":{\"n\":3,\"min\":20,\"q1\":22,\"mean\":26,\"median\":24,\"q3\":30,\"max\":35,\"sd\":7.8},
        \"FEE\":{\"n\":3,\"min\":34,\"q1\":50,\"mean\":64,\"median\":65,\"q3\":79,\"max\":92,\"sd\":29}},
        \"context\":{
        \"SEC\":{\"n\":3,\"min\":67,\"q1\":69,\"mean\":70,\"median\":71,\"q3\":72,\"max\":73,\"sd\":3.1},
        \"OPP\":{\"n\":3,\"min\":13,\"q1\":54,\"mean\":69,\"median\":95,\"q3\":98,\"max\":100,\"sd\":48.9},
        \"NUM\":{\"n\":3,\"min\":30,\"q1\":31,\"mean\":48,\"median\":31,\"q3\":58,\"max\":84,\"sd\":30.9},
        \"RES\":{\"n\":3,\"min\":14,\"q1\":46,\"mean\":59,\"median\":77,\"q3\":82,\"max\":86,\"sd\":39.2}}},
        \"users\":{\"weak\":[18,16],\"strong\":[18,17,16]}}}"}', 1673171614, $group->id);
        $sql = "INSERT INTO {block_learningcoach_stats}
         (datas, time_updated, moodle_group_id)
         VALUES (?,?,?)";
        $status = $DB->execute($sql, $insertvalues);

        $mylc = new Lc();

        $result = $mylc->profile_group($group->id, $course->id, false);

        $this->assertObjectHasAttribute('datas', $result);
        $this->assertObjectHasAttribute('time_updated', $result);
        // $this->assertObjectHasAttribute('moodle_group_id', $result);
    }

    /**
     * LC Cohort enrolment - test
     * @return Array
     */
    public function test_enrol_in_lc_cohort() {
        $this->resetAfterTest(true);
        $mylc = new Lc();

        $user1 = $this->getDataGenerator()->create_user();

        $cohortlc1 = $mylc->create_lc_cohort();

        $result = $mylc->enrol_in_lc_cohort($user1->id);

        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('data', $result);
    }

    /**
     * Test Class LC Update LC profile
     * @return Array
     */
    public function test_update_profile_user() {

        $this->resetAfterTest(true);

        $mylc = new Lc();

        $moodleuser = $this->getDataGenerator()->create_user();

        $lcuser = $this->getDataGenerator()->create_user(array('fk_moodle_user_id' => $moodleuser->id));

        $result = $mylc->update_profile_user($moodleuser->id, $forceupdate = false);

        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('message', $result);
    }

    /**
     * Test Class LC Delete user's score - test
     * @return Boolean
     */
    public function test_user_delete_scores() {
        $this->resetAfterTest(true);
        $mylc = new Lc();

        $moodleuser = $this->getDataGenerator()->create_user();

        $lcuser = $this->getDataGenerator()->create_user(array('fk_moodle_user_id' => $moodleuser->id));

        $result = $mylc->user_delete_scores($moodleuser->id);

        $this->assertTrue($result);

    }

    /**
     * Test Class LC Delete LC user
     * @return Boolean
     */
    public function test_user_delete() {
        $this->resetAfterTest(true);
        $mylc = new Lc();

        $moodleuser = $this->getDataGenerator()->create_user();

        $lcuser = $this->getDataGenerator()->create_user(array('fk_moodle_user_id' => $moodleuser->id));

        $result = $mylc->user_delete($moodleuser->id);
        $this->assertTrue($result);

    }

    /**
     * Test Class LC Delete user, stats and score
     * @return Array
     */
    public function test_delete_users_and_scoresand_stats() {
        $this->resetAfterTest(true);
        $mylc = new Lc();

        $user = $this->getDataGenerator()->create_user();

        $result = $mylc->delete_users_and_scoresand_stats($user);

        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertArrayHasKey(2, $result);
    }


    /**
     * Test connexion to DB - test
     * @return PHPUnit\DbUnit\Database\Connection
     */
    public function get_connection() {
        $database = 'moodlephpu';
        $user = 'root';
        $password = 'password';
        $dns = 'mysql:dbname=moodlephpu;host=localhost';
        $pdo = new \PDO($dns, $user, $password);
        return $this->createDefaultDBConnection($pdo, $database);
    }

    /**
     * Test Class LC update date acces - test
     * @return Boolean
     */
    public function test_update_data_acces() {
        $this->resetAfterTest(true);

        $user1 = $this->getDataGenerator()->create_user(array(
            'email' => 'john.doe@3e-innovation.com',
            'username' => 'john.doe@3e-innovation.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));

        $moodleusers['john.doe@3e-innovation.com'] = $user1->id;

        $data = '{
            "data": {
                "john.doe@3e-innovation.com": 3564
                }
            }';

        $mylc = new Lc();

        $result = $mylc->insert_lc_users($data, $moodleusers);

        $lcuser = $mylc->get_lc_user($user1->id);
        $lcuser->data_acces = 0;
        $result = $mylc->update_data_acces($lcuser);

        $this->assertTrue($result);
    }

    /**
     * LearningCoach webservice cabapility
     * @return Boolean
     */
    public function test_assign_capability_ws() {
        $this->resetAfterTest(true);

        $mylc = new Lc();

        $result = $mylc->assign_capability_ws();

        $this->assertTrue($result);
    }

    /**
     * LearningCoach Webservice add user to webservice - test
     * @return Void
     */
    public function test_add_user_to_ws() {
        $this->resetAfterTest(true);

        /* $moodleuser1 = $this->getDataGenerator()->create_user(array(
            'username' => 'noreply@traindy.io',
        )); */

        $mylc = new Lc();

        $result = $mylc->add_user_to_ws();

        $this->assertNull($result);
    }

    /**
     * Test Class LC Get % LC user profile completed
     * @return Integer
     */
    public function test_get_percent() {
        $this->resetAfterTest(true);

        $mylc = new Lc();

        $course1 = $this->getDataGenerator()->create_course();

        $courseid = (int)$course1->id;

        $result = $mylc->get_percent($courseid);

        $this->assertIsInt($result);
    }

    /**
     * Test Class LC Log Error()
     * @return Integer
     */
    public function test_log_error() {

        $this->resetAfterTest(true);

        $mylc = new Lc();

        $result = $mylc->log_error("error", 'error description test');

        $this->assertIsInt($result);
    }

    /**
     * Test Class LC insert_lc_user
     * @return Array
     */
    public function test_insert_lc_user() {

        $this->resetAfterTest(true);

        $user1 = $this->getDataGenerator()->create_user(array(
            'email' => 'john.doe@example.com',
            'username' => 'john.doe@example.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));

        $user2 = $this->getDataGenerator()->create_user(array(
            'email' => 'jane.doe@example.com',
            'username' => 'jane.doe@example.com',
            'firstname' => 'User2',
            'lastname' => 'User2',
        ));

        $moodleusers['john.doe@example.com'] = $user1->id;
        $moodleusers['jane.doe@example.com'] = $user2->id;

        $data = '{
            "data": {
                "john.doe@example.com": 3564,
                "jane.doe@example.com": 3565,
                "not-able-to-import-mail@example.com": null
            }
        }';

        $mylc = new Lc();

        $result = $mylc->insert_lc_users($data, $moodleusers);

        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertJsonStringEqualsJsonString(201, $result['code']);
        $this->assertEquals("OK", $result['data']);

    }

    /**
     * Test Class LC -> get_groups
     * @return Stdclass
     */
    public function test_get_groups() {
        $this->resetAfterTest(true);

        $course1 = $this->getDataGenerator()->create_course();
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));

        $mylc = new Lc();
        $groups = $mylc->get_groups($course1->id);
        $this->assertInstanceOf('\stdClass', $groups[$group1->id]);
    }

    /**
     * Test Class LC - > get_groups_learners
     * @return Stdclass
     */
    public function test_get_group_learners() {
        $this->resetAfterTest(true);

        $user1 = $this->getDataGenerator()->create_user(array(
            'email' => 'john.doe@3e-innovation.com',
            'username' => 'john.doe@3e-innovation.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));

        $moodleusers['john.doe@3e-innovation.com'] = $user1->id;

        $data = '{
            "data": {
                "john.doe@3e-innovation.com": 3564
                }
            }';

        $mylc = new Lc();

        $result = $mylc->insert_lc_users($data, $moodleusers);

        $course1 = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id);
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));
        $this->getDataGenerator()->create_group_member(array('userid' => $user1->id, 'groupid' => $group1->id));

        $grouplearners = $mylc->get_group_learners($group1->id, $course1->id);

        $this->assertInstanceOf('\stdClass', $grouplearners[$user1->id]);
    }

    /**
     * Test Class LC -> group_delete_stats
     * @return Boolean
     */
    public function test_group_delete_stats() {
        $this->resetAfterTest(true);

        $course1 = $this->getDataGenerator()->create_course();
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));

        $mylc = new Lc();
        $result = $mylc->group_delete_stats($group1->id);

        $this->assertTrue($result);
    }

    /**
     * Test Class LC log_error_clean_older_recs
     * @return Boolean
     */
    public function test_log_error_clean_older_recs() {
        global $DB;
        $this->resetAfterTest(true);

        $tablename = 'block_learningcoach_log_err';

        // Test logic: add 25 entries in the log table ;
        // Call log_error_clean_older_recs function;
        // Check that the table contains only 11 entries ;
        $mylc = new Lc();
        for ($i = 1; $i <= 25; $i++) {
            $error = $mylc->log_error("error", 'error description test');
        }
        $count = $DB->count_records('block_learningcoach_log_err');
        $this->assertEquals(25, $count);
        $mylc = new Lc();
        $sql = $mylc->log_error_clean_older_recs();

        $count2 = $DB->count_records('block_learningcoach_log_err');
        $this->assertEquals(11, $count2);
    }
}

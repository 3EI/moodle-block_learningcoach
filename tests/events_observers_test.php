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

namespace block_learningcoach;

use block_learningcoach\events_observers;
use block_learningcoach\Lc;
use ReflectionClass;
use advanced_testcase;
use block_accessreview;
use context_course;

defined('MOODLE_INTERNAL') || die();

GLOBAL $CFG;
require_once($CFG->dirroot.'/blocks/learningcoach//db/events.php');
require_once($CFG->dirroot.'/blocks/learningcoach/classes/events_observers.php');


/**
 * PHPUnit block_learningcoach tests
 *
 * @package     block_learningcoach
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \block_learningcoach\events_observers
 */
class events_observers_test extends advanced_testcase {

    /**
     * Test the cohort user added event observer.
     * Ensure that register_user is called when a member is added to LC cohort
     * and that nothing happens when another cohort is concerned.
     * Tested OK by LM 2023/01/15
     * @covers ::lc_cohort_user_add
     */
    public function test_lc_cohort_user_add() {
        GLOBAL $DB;
        $mylc = new Lc();
        $this->resetAfterTest(true);

        // Generates data for test.
        // Creates a cohort and lc cohort.
        $cohort1 = $this->getDataGenerator()->create_cohort(array(
            'idnumber' => 'test',
        ));
        $mylc->create_lc_cohort();
        $cohortlc = $DB->get_record($mylc->tablecohort, array('idnumber' => $mylc->cohortidnumber));
        // creates users
        $user1 = $this->getDataGenerator()->create_user(array(
            'email' => 'user1@testlc.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));
        $user2 = $this->getDataGenerator()->create_user(array(
            'email' => 'user2@testlc.com',
            'firstname' => 'User2',
            'lastname' => 'User2',
        ));

        // Add user1 to cohort1 and capture event.
        $sink = $this->redirectEvents();
        cohort_add_member($cohort1->id, $user1->id);
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);
        $this->assertInstanceOf('\core\event\cohort_member_added', $event);
        // ensure that nothing happened
        $result = events_observers::lc_cohort_user_add($event);
        $this->assertFalse($result);

        // Add user2 to cohortlc and capture event.
        $sink = $this->redirectEvents();
        cohort_add_member($cohortlc->id, $user2->id);
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);
        $this->assertInstanceOf('\core\event\cohort_member_added', $event);
        // ensure that everything is ok (Array structure is returned)
        $result = events_observers::lc_cohort_user_add($event);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('data', $result);
    }

    /**
     * Test the cohort user removed event observer
     * Ensure that LC user data is delted if removed from LC cohort and that nothing happens if another cohort is concerned
     * and that nothing happens when another cohort is concerned.
     * Tested OK by LM 2023/01/15.
     * @covers ::lc_cohort_user_remove
     */
    // test_lc_cohort_user_remove
    public function test_lc_cohort_user_remove_observer() {
        GLOBAL $DB;
        $mylc = new Lc();

        $this->resetAfterTest(true);

        // Create the test data
        // Creates users
        $user1 = $this->getDataGenerator()->create_user(array(
            'email' => 'user1@testlc.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));
        $user2 = $this->getDataGenerator()->create_user(array(
            'email' => 'user2@testlc.com',
            'firstname' => 'User2',
            'lastname' => 'User2',
        ));

        // Create cohort 1 and cohortlc
        $cohort1 = $this->getDataGenerator()->create_cohort(array(
            'idnumber' => 'test2',
        ));
        $mylc->create_lc_cohort();
        $cohortlc = $DB->get_record($mylc->tablecohort, array('idnumber' => $mylc->cohortidnumber));

        // Add user1 to cohort1 and user2 to cohortlc
        cohort_add_member($cohort1->id, $user1->id);
        cohort_add_member($cohortlc->id, $user2->id);

        // Delete user1 from cohort1 and capture event.
        $sink = $this->redirectEvents();
        cohort_remove_member($cohort1->id, $user1->id);
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);
        $this->assertInstanceOf('\core\event\cohort_member_removed', $event);
        // Ensure that nothing happens
        $result = events_observers::lc_cohort_user_remove($event);
        $this->assertFalse($result);

        $sink = $this->redirectEvents();
        cohort_remove_member($cohortlc->id, $user2->id);
        $events = $sink->get_events();
        $sink->close();
        $event2 = reset($events);
        $this->assertInstanceOf('\core\event\cohort_member_removed', $event2);
        // Ensure that lc user data is deleted
        $result = events_observers::lc_cohort_user_remove($event2);
        $this->assertTrue($result);

    }

    /**
     * Test the cohort deleted event observer.
     * Ensure that LC's data (scores and stats) is deleted when the LC cohort id deleted
     * and that nothing is deleted when another cohort is deleted.
     * Tested OK by LM 2023/01/15
     * @covers ::lc_cohort_delete
     */
    public function test_lc_cohort_delete() {
        GLOBAL $DB;
        $mylc = new Lc();

        $this->resetAfterTest(true);

        // Generates data for test.
        // Creates a cohort and lc cohort.
        $cohort = $this->getDataGenerator()->create_cohort(array(
            'idnumber' => 'test',
        ));
        $mylc->create_lc_cohort();
        $cohortlc = $DB->get_record($mylc->tablecohort, array('idnumber' => $mylc->cohortidnumber));

        // Delete cohort and capture event.
        $sink = $this->redirectEvents();
        cohort_delete_cohort($cohort);
        $this->assertFalse($DB->record_exists('cohort', array('id' => $cohort->id)));
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);
        $this->assertInstanceOf('\core\event\cohort_deleted', $event);
        $result = events_observers::lc_cohort_delete($event);
        // Ensures that nothing has been deleted
        $this->assertFalse($result);

        // Delete LC cohort and capture event.
        $sink2 = $this->redirectEvents();
        cohort_delete_cohort($cohortlc);
        $this->assertFalse($DB->record_exists('cohort', array('id' => $cohortlc->id)));
        $events2 = $sink->get_events();
        $sink2->close();
        $event2 = reset($events2); // $event2 = array_pop($events2);
        $this->assertInstanceOf('\core\event\cohort_deleted', $event2);
        $result = events_observers::lc_cohort_delete($event2);
        // Ensures that data has been deleted
        $this->assertNotFalse($result);
    }

    /**
     * Test the group member added event observer.
     * Ensure that LC group profile is updated after the new member has been added
     * Tested OK by LM 2023/01/16
     * @covers ::group_member_added
     */
    public function test_group_member_added() {
        GLOBAL $DB;
        $mylc = new Lc();

        $this->resetAfterTest(true);

        // Generates data for test.
        // Create course.
        $course1 = $this->getDataGenerator()->create_course();
        // Create group.
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));
        $this->assertTrue(groups_group_exists($group1->id));
        // Create users.
        $user1 = $this->getDataGenerator()->create_user(array(
            'email' => 'user1@testlc.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));
        $user2 = $this->getDataGenerator()->create_user(array(
            'email' => 'user2@testlc.com',
            'firstname' => 'User2',
            'lastname' => 'User2',
        ));
        // Enrol user1 and user2 in course1
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id);
        // Add user2 to LC cohort
        $mylc->create_lc_cohort();
        $cohortlc = $DB->get_record($mylc->tablecohort, array('idnumber' => $mylc->cohortidnumber));
        cohort_add_member($cohortlc->id, $user2->id);

        // Add user1 to group1 (user 1 not in LC cohort)
        // Trigger and capture the event
        $sink = $this->redirectEvents();
        $this->assertTrue(groups_add_member($group1->id, $user1->id));
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);
        // Validate event data
        $this->assertInstanceOf('\core\event\group_member_added', $event);
        // Add member in group
        // User1 is not in LC cohort so result should be false
        $this->assertFalse(events_observers::group_member_added($event));

        // Add user2 to group2 (user 2 is in LC cohort)
        // Trigger and capture the event
        $sink = $this->redirectEvents();
        $this->assertTrue(groups_add_member($group1->id, $user2->id));
        $events = $sink->get_events();
        $sink->close();
        $event2 = reset($events);
        // Validate event data
        $this->assertInstanceOf('\core\event\group_member_added', $event2);
        // Add member in group
        // User2 is in LC cohort so result is an json
        $result = events_observers::group_member_added($event2);
        $this->assertNotFalse($result);

    }
    /**
     * Test the group member removed event observer.
     * Ensure that LC group profile is updated after the member has been removed
     * Tested OK by LM 2023/01/16
     * @covers ::group_member_removed
     */
    public function test_group_member_removed() {
        GLOBAL $DB;
        $mylc = new Lc();

        $this->resetAfterTest(true);

        // Generates data for test.
        // Create courses
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        // Create groups
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));

        // Create users :
        $user1 = $this->getDataGenerator()->create_user(array(
            'email' => 'user1@testlc.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));
        $user2 = $this->getDataGenerator()->create_user(array(
            'email' => 'user2@testlc.com',
            'firstname' => 'User2',
            'lastname' => 'User2',
        ));
        $user3 = $this->getDataGenerator()->create_user(array(
            'email' => 'user3@testlc.com',
            'firstname' => 'User3',
            'lastname' => 'User3',
        ));
        // Enrol user1 and user 2 in course1
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id);
        // Enrol user3 and in course2
        $this->getDataGenerator()->enrol_user($user3->id, $course2->id);
        // Add user2 to LC cohort
        $mylc->create_lc_cohort();
        $cohortlc = $DB->get_record($mylc->tablecohort, array('idnumber' => $mylc->cohortidnumber));
        cohort_add_member($cohortlc->id, $user2->id);
        // Add user1 in group1 and user2 in group2
        $this->assertTrue(groups_add_member($group1->id, $user1->id));
        $this->assertTrue(groups_add_member($group2->id, $user2->id));

        // Remove user1 from group1 (user 1 not in LC cohort)
        // Trigger and capture the event
        $sink = $this->redirectEvents();
        $this->assertTrue(groups_remove_member($group1->id, $user1->id));
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);
        // Validate event data
        $this->assertInstanceOf('\core\event\group_member_removed', $event);
        // Remove member from group
        // user1 is not in LC cohort so result should be false
        $this->assertFalse(events_observers::group_member_removed($event));

        // Remove user2 from group2 (user 2 is in LC cohort)
        // Trigger and capture the event
        $sink = $this->redirectEvents();
        $this->assertTrue(groups_remove_member($group2->id, $user2->id));
        $events = $sink->get_events();
        $sink->close();
        $event2 = reset($events);
        // Validate event data
        $this->assertInstanceOf('\core\event\group_member_removed', $event2);
        // Remove member from group
        // User2 is in LC cohort so result is an json
        $result = events_observers::group_member_removed($event2);
        $this->assertNotFalse($result);

    }


    /**
     * Test the group member removed event observer.
     * Ensure that group data is deleted if group had members in LC cohort else ensure nothing happens
     * Tested OK by LM 2023/01/16
     * @covers ::group_deleted
     */
    public function test_group_deleted() {
        GLOBAL $DB;
        $mylc = new Lc();

        $this->resetAfterTest(true);

        // Generates data for test.
        // Create users
        $user1 = $this->getDataGenerator()->create_user(array(
            'email' => 'user1@testlc.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));
        $user2 = $this->getDataGenerator()->create_user(array(
            'email' => 'user2@testlc.com',
            'firstname' => 'User2',
            'lastname' => 'User2',
        ));
        // Create course
        $course1 = $this->getDataGenerator()->create_course();
        // Create groups
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));
        // Enrol user1 and user 2 in course1
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id);
        // Add user2 to LC cohort
        $mylc->create_lc_cohort();
        $cohortlc = $DB->get_record($mylc->tablecohort, array('idnumber' => $mylc->cohortidnumber));
        cohort_add_member($cohortlc->id, $user2->id);
        // Add user1 in group1 and user2 in group2
        $this->assertTrue(groups_add_member($group1->id, $user1->id));
        $this->assertTrue(groups_add_member($group2->id, $user2->id));

        // Delete group1 (user 1 not in LC cohort)
        // Trigger and capture the event
        $sink = $this->redirectEvents();
        groups_delete_group($group1->id);
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);
        // Group1 has no users with LC profile so result should be false
        $result = events_observers::group_deleted($event);
        $this->assertFalse($result);

        // Delete group2 (user 2 is in LC cohort)
        // Trigger and capture the event
        $sink = $this->redirectEvents();
        groups_delete_group($group2->id);
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);
        // Group2 has a user with LC profile so result should be true
        $result = events_observers::group_deleted($event);
        $this->assertTrue($result);

    }

    /**
     * Test the user deleted event observer.
     * Ensure that user si removed from LC user table and tha data is deleted when the user is deleted
     * and that nothing is deleted when a user has not an LC profile
     * Tested OK by LM 2023/01/16
     * @covers ::user_deleted
     */
    public function test_user_deleted() {
        global $DB;
        $mylc = new Lc();

        $this->resetAfterTest(true);

        // Generates data for test.
        // Create users
        $user1 = $this->getDataGenerator()->create_user(array(
            'email' => 'user1@testlc.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));
        $user2 = $this->getDataGenerator()->create_user(array(
            'email' => 'user2@testlc.com',
            'firstname' => 'User2',
            'lastname' => 'User2',
        ));

        // Add user2 to LC cohort
        $mylc->create_lc_cohort();
        $cohortlc = $DB->get_record($mylc->tablecohort, array('idnumber' => $mylc->cohortidnumber));
        cohort_add_member($cohortlc->id, $user2->id);

        // Insert user2 in lc_users table
        $moodleusers['john.doe@3e-innovation.com'] = $user2->id;
        $data = '{
            "data": {
                "john.doe@3e-innovation.com": 3564
                }
            }';
        $result = $mylc->insert_lc_users($data, $moodleusers);
        $this->assertEquals($result['code'], "201");
        // Insert scores
        $insertvalues = [];
        array_push($insertvalues, 10, 1, 1 , $user2->id);
        $sql = "INSERT INTO {block_learningcoach_cons_sco}
         (construct_score, profile_version, fk_id_construct, fk_moodle_user_id)
         VALUES (?,?,?,?)";
        $status = $DB->execute($sql, $insertvalues);

        $scores = $DB->get_record($mylc->tablescore, ['fk_moodle_user_id' => $user2->id]);
        $this->assertNotFalse($scores);

        // Delete user 1 that is not in LC cohort
        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $result = user_delete_user($user1);
        $this->assertTrue($result);
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);

        // Test that user is deleted in DB.
        $deluser = $DB->get_record('user', array('id' => $user1->id), '*', MUST_EXIST);
        // Check that user field deleted is set to 1
        $this->assertEquals(1, $deluser->deleted);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\user_deleted', $event);
        $this->assertSame($user1->id, $event->objectid);
        $this->assertSame('user_deleted', $event->get_legacy_eventname());
        $this->assertEventLegacyData($user1, $event);
        $eventdata = $event->get_data();
        $this->assertSame($eventdata['other']['username'], $user1->username);
        // User1 is not in LC cohort so result should be true
        $result = events_observers::user_deleted($event);
        $this->assertFalse($result);

        // Delete user 2 that is in LC cohort
        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $result = user_delete_user($user2);
        $this->assertTrue($result);
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);
        // User2 is in LC cohort so result should be true
        $result = events_observers::user_deleted($event);
        $this->assertTrue($result);
        // Test that user2 no longer exists in tablelcusers.
        $lcuser = $DB->get_record($mylc->tablelcusers, ['fk_moodle_user_id' => $user2->id]);
        $this->assertFalse($lcuser);
        // Test that scores of the user2 are deleted
        $scores = $DB->get_record($mylc->tablescore, ['fk_moodle_user_id' => $user2->id]);
        $this->assertFalse($scores);
    }


    /**
     * Test the user created event observer.
     * Ensure that user is enrolled in LC cohort if enrolment policy setting is to automatic
     * and that nothing happens if not set to automatic
     * Tested OK by LM 2023/01/xxx
     * @covers ::user_created
     */
    public function test_user_created() {
        global $DB;
        $mylc = new Lc();

        $this->resetAfterTest(true);

        // Generates data for test.

        // Set enrolment policy to something different from automatic
        set_config('enrolment', '', 'block_learningcoach');
        // Check that user is not enrolled in LC cohort
        // Create users
        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $user1 = $this->getDataGenerator()->create_user(array(
            'email' => 'user1@testlc.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);
        // Automatic enrolment is off so result should be xxx
        $result = events_observers::user_created($event);
        $this->assertFalse($result);

        // Set enrolment policy to automatic
        set_config('enrolment', 'automatic', 'block_learningcoach');
        // Check that user is enrolled in LC cohort
        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $user2 = $this->getDataGenerator()->create_user(array(
            'email' => 'user2@testlc.com',
            'firstname' => 'User2',
            'lastname' => 'User2',
        ));
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);
        // Automatic enrolment is off so result should be xxx
        $result = events_observers::user_created($event);
        $this->assertNotFalse($result);

    }
}

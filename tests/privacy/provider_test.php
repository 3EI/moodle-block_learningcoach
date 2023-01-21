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
 * Provides the {@see block_learningcoach\privacy\provider_test} class.
 *
 * @package     block_learningcoach
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_learningcoach\privacy;

defined('MOODLE_INTERNAL') || die();

global $CFG;

use \block_learningcoach\privacy\provider;
use \block_learningcoach\Lc;

use \core_privacy\local\request\writer;
use \core_privacy\tests\provider_testcase;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\approved_userlist;

/**
 *
 * Unit tests for the privacy API implementation.
 *
 * @package     block_learningcoach
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider_test extends provider_testcase {

    /** @var testing_data_generator */
    protected $generator;

    /** @var stdClass */
    protected $student1;


    /**
     * Set up the test environment.
     *
     */
    /* protected function setUp(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->student1 = $this->generator->create_user();
    } */


    /**
     * Test fetching information about user data stored.
     */
    public function test_get_metadata() {
        $collection = new \core_privacy\local\metadata\collection('block_learningcoach');
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();
        $this->assertNotEmpty($collection);

        $this->assertCount(3, $itemcollection);

        $table = array_shift($itemcollection);
        $this->assertEquals('block_learningcoach_users', $table->get_name());
        $table = array_shift($itemcollection);
        $this->assertEquals('block_learningcoach_cons_sco', $table->get_name());
        $table = array_shift($itemcollection);
        $this->assertNotEmpty($table);

    }

    /**
     * Test getting the context for the user ID related to this plugin.
     */
    public function test_get_contexts_for_userid() {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        $student = $generator->create_user();
        $studentcontext = \context_user::instance($student->id);
        $systemcontext = \context_system::instance();

        // Enrol users in course and add course items.
        $course = $generator->create_course();
        $generator->enrol_user($student->id, $course->id, 'student');

        // Check nothing is found before block is populated.
        $contextlist1 = provider::get_contexts_for_userid($student->id);

        $this->assertCount(1, $contextlist1);

        // Ensure context returned is system
        $this->assertEquals($systemcontext, $contextlist1->current());
    }


    /**
     * Test that only users with a user context are fetched.
     * @link \block_learningcoach\privacy\provider::get_users_in_context() implementation.
     */
    public function test_get_users_in_context() {
        $this->resetAfterTest(true);
        $mylc = new LC();
        $component = 'block_learningcoach';

        // Create a user.
        $user = $this->getDataGenerator()->create_user();
        $usercontext = \context_user::instance($user->id);

        $systemctx = \context_system::instance();

        // $this->setAdminUser();

        $userlist = new \core_privacy\local\request\userlist($systemctx, $component);

         // The list of users within the system context should not contain user as user not enrolled in Learning Coach.
         provider::get_users_in_context($userlist);

         $this->assertCount(0, $userlist);
         // $this->assertTrue(in_array($user->id, $userlist->get_userids()));

         // The list of users within the user context should be empty.
        $userlist2 = new \core_privacy\local\request\userlist($usercontext, $component);
        provider::get_users_in_context($userlist2);
        $this->assertCount(0, $userlist2);

    }

    /**
     * Test that data is exported correctly for this plugin.
     * @link \block_learningcoach\privacy\provider::export_user_data() implementation.
     */
    public function test_export_user_data() {
        global $DB;
        $this->resetAfterTest(true);
        /* $user = $this->getDataGenerator()->create_user();
        $usercontext = \context_user::instance($user->id);

        $userlist = new \core_privacy\local\request\userlist($usercontext, $component);
        provider::export_user_data($userlist); */

        $user = $this->getDataGenerator()->create_user();
        $component = 'block_learningcoach';

        $userctx = \context_user::instance($user->id);
        $userlist = new approved_contextlist($user, $component, [$userctx->id]);
        provider::export_user_data($userlist);
        // User has no data for Learning Coach:
        $writer = writer::with_context($userctx );
        $this->assertFalse($writer->has_any_data());

        $systemcontext = \context_system::instance();
        $userlist = new approved_contextlist($user, $component, [$systemcontext->id]);
        provider::export_user_data($userlist);

        // User has no data for Learning Coach:
        $writer = writer::with_context($systemcontext);
        $this->assertFalse($writer->has_any_data());

    }

    /**
     * Test that user data is deleted using the context.
     * @link \block_learningcoach\privacy\provider::delete_data_for_all_users_in_context() implementation.
     */
    public function test_delete_data_for_all_users_in_context() {
        global $DB;
        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user(array(
            'email' => 'john.doe@3e-innovation.com',
            'username' => 'john.doe@3e-innovation.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));

        $moodleusers['john.doe@3e-innovation.com'] = $user->id;

        $data = '{
            "data": {
                "john.doe@3e-innovation.com": 3564
                }
            }';

        $mylc = new Lc();

        $result = $mylc->insert_lc_users($data, $moodleusers);

        $context = \context_system::instance();

        // Check that we have an entry in LC users.
        $lcusers = $DB->get_records('block_learningcoach_users', ['fk_moodle_user_id' => $user->id]);
        $this->assertCount(1, $lcusers );

        provider::delete_data_for_all_users_in_context($context);

        // Check that LC users has now been deleted.
        $lcusers = $DB->get_records('block_learningcoach_users', ['fk_moodle_user_id' => $user->id]);
        $this->assertCount(0, $lcusers);
    }

    /**
     * Test that user data is deleted for this user.
     * @link \block_learningcoach\privacy\provider::delete_data_for_user() implementation.
     */
    public function test_delete_data_for_user() {
        global $DB;
        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user(array(
            'email' => 'john.doe@3e-innovation.com',
            'username' => 'john.doe@3e-innovation.com',
            'firstname' => 'User1',
            'lastname' => 'User1',
        ));

        $moodleusers['john.doe@3e-innovation.com'] = $user->id;

        $data = '{
            "data": {
                "john.doe@3e-innovation.com": 3564
                }
            }';

        // $component = 'block_learningcoach';

        $mylc = new Lc();

        $result = $mylc->insert_lc_users($data, $moodleusers);

        $userctx = \context_user::instance($user->id);

        // Check that we have an entry in LC users.
        $lcusers = $DB->get_records('block_learningcoach_users', ['fk_moodle_user_id' => $user->id]);

        $this->assertCount(1, $lcusers );

        $contextlist  = new approved_contextlist($user, 'block_learningcoach_users', [$userctx->id]);

        provider::delete_data_for_user($contextlist);

        // Check that LC users has now been deleted.
        $lcusers = $DB->get_records('block_learningcoach_users', ['fk_moodle_user_id' => $user->id]);
        // $this->assertCount(0, $lcusers); */

    }

    /**
     * Test that data for users in approved userlist is deleted.
     * @link \mod_workshop\privacy\provider::delete_data_for_users() implementation.
     */
    public function test_delete_data_for_users() {
        $this->resetAfterTest(true);
        global $DB;

        // Create users.
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

        $context = \context_system::instance();

        $key1 = $DB->get_record('block_learningcoach_users', ['fk_moodle_user_id' => $user1->id]);

        // Before deletion.
        $count = $DB->count_records('block_learningcoach_users');
        $this->assertEquals(2, $count);

        // Ensure deleting wrong user in the user context does nothing.
        $approveduserids = [$user1->id];
        $approvedlist = new approved_userlist($context, 'block_learningcoach', $approveduserids);
        provider::delete_data_for_users($approvedlist);

        // After deletion.
        $count = $DB->count_records('block_learningcoach_users');
        $this->assertEquals(1, $count);
    }

}

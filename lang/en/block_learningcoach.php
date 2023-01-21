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
 * Plugin strings are defined here.
 *
 * @package     block_learningcoach
 * @category    string
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_learningcoach\Lc;

global $CFG;

$moodlehost = $CFG->wwwroot;


$string['learningcoach:addinstance'] = 'Add a new Learning Coach block';
$string['learningcoach:myaddinstance'] = 'Add a new Learning Coach Dashboard block';
$string['learningcoach:viewteacherblock'] = 'Teacher Block';
$string['learningcoach:viewlearnerblock'] = 'Learner Block';
$string['pluginname'] = 'Learning Coach';
$string['profile'] = 'profile';
$string['learnerprofile'] = 'My learner profile';
$string['formercourses'] = 'Learner profiles completed';
$string['learners'] = 'Learners';
$string['groups'] = 'Groups';
$string['apihost'] = 'Learning Coach Host';
$string['apihostinfo'] = 'Retrieve this value from your integrations page at <a {$a}>https://app.learning.coach/en/integrations </a><br><i> Please make sure to use the https:// protocol in the url.</i>';
$string['servicekey'] = 'Learning Coach Token';
$string['servicekeyinfo'] = 'Retrieve this value from your integrations page at <a {$a}>https://app.learning.coach/en/integrations </a>';
$string['moodlehost'] = 'Moodle Host';
$string['moodlehostinfo'] = $moodlehost;
$string['moodletoken'] = 'Moodle Token';
$string['notificationstartcourse'] = 'Notifications';
$string['notificationstartcourseinfo'] = "By default, notifications are sent 5 days before the start of the training (license start date)";

$string['back_home'] = 'Back home';
$string['validate'] = "Validate";

$string['errorcourse'] = "No such course id";
$string['errorstats'] = "An error has occurred. Please contact the administrator";
$string['erroruserlcnotfound'] = "User not found in Learning Coach";
$string['errorinstall'] = "Install Error";

$string['blockalllearners'] = 'See all learners';
$string['blockallgroups'] = 'See all groups';
$string['blockprofile'] = "My learner profile in detail";
$string['blocknodata'] = 'You do not want to share your Learning Coach data';
$string['blockinfo'] = 'You can change this setting in the app';
$string['blockinscriptioninfo'] = 'You are not yet registered to Learning Coach';
$string['blockinscriptionbtn'] = 'Register';
$string['blocksubtitle'] = 'My  teacher role';
$string['blockscore'] = 'You have not yet completed your profile to evaluate your score';

$string['titlegroups'] = 'Learning Coach - Groups';
$string['titlegroupmembers'] = 'Group\'s members';
$string['titlegroupscourse'] = 'Course group(s)';
$string['titlelearners'] = 'Learning Coach - Learners';
$string['titleprofile'] = 'My learner profile';
$string['titleprofileof'] = 'Learner profile: ';
$string['titlescoredetail'] = 'Score details ';
$string['titlestatdetail'] = 'Group statistics';

$string['update'] = 'Updated:';
$string['update2'] = 'Update date: not defined<br/>Refresh to update datas';

$string['no_user'] = 'Non-existing user';

$string['sharedata'] = 'Do not wish to share their data';
$string['seeprofile'] = 'See profile';
$string['uncompleteprofile'] = 'You have not yet completed your learner profile. Your datas are therefore not available.';
$string['uncompleteprofileotheruser'] = 'This learner\'s profile is not completed yet. Datas are therefore not available.';
$string['uncompleteprofileinfo'] = 'Go to the Learning Coach app to complete it.';

$string['nogroup'] = 'No group in this course';

$string['tablelastname'] = 'Last name';
$string['tablefirstname'] = 'First name';
$string['tableprofile'] = 'Profile';

$string['warning1'] = 'The number of learners belonging to this group is not sufficient to obtain statistics.';
$string['warning2'] = '3 learners minimum are necessary to obtain an objective result.';
$string['warning3'] = 'No data can currently be displayed: the number of learners who have completed their profiles is not sufficient.';
$string['warning4'] = 'An error has occurred';
$string['warningaccess'] = 'You don\'t have the rights to access this page';

$string['backlist'] = 'Back to list';

$string['tag'] = 'Tag';
$string['taginfo'] = 'You can add the tags you want, each separated by a comma. Example: 2022, Skills, ...';

// Content hub.
$string['contenthub:settings:connexion'] = 'Connexion';
$string['contenthub:settings:enrolment'] = 'Registration';
$string['contenthub:settings:event'] = 'Event';

// For enrolment settings.
$string['enrolment'] = 'Enrolment';
$string['enrolment_help'] = 'This setting determines how Learning Coach sign-ups occur. The available options are:

* Manually: the administrator enrolls learners in the Learning Coach cohort.
* Automatic: all learners registered on Moodle are automatically enrolled in the Learning Coach cohort.
* By learner: the learner registers for the cohort via their Learning Coach block.';
$string['enrolment_automatic'] = 'Automatic';
$string['enrolment_manual'] = 'Manual';
$string['enrolment_learner'] = 'By learner';

$string['enrolment_success'] = 'Registration completed successfully.';
$string['enrolment_settings'] = 'Learning Coach settings don\'t allow enroll yourself';
$string['enrolment_self'] = 'You cannot register a different user than yourself.';
$string['enrolment_validate'] = 'Do you want to validate your subscription to Learning Coach?';

$string['licence'] = 'Automatic license renewal';
$string['licenceinfo'] = 'Please note that if you do not renew the license, the Learning Coach profile data will be deleted 1 month after the expiration date of the license';

// Construct description.
$string['constructdesc'] = '* max: maximum score in the population <br>
* q3: 75% of the population has a lower score <br>
* median: 50% of the population has a lower score <br>
* q1: 25% of the population a lower score <br>
* min: minimum score in the population';

// For cron.
$string['registerusercron'] = 'Processing users manually added to the LearningCoach cohort';

// Privacy provider.
$string['privacy:metadata:block_learningcoach_users'] = 'Informations about registered users of ';
$string['privacy:metadata:block_learningcoach_users:registered'] = 'User registered or not at Learning Coach';
$string['privacy:metadata:block_learningcoach_users:data_acces'] = 'User authorizes the sharing of his Learning Coach datas';
$string['privacy:metadata:block_learningcoach_users:time_added'] = 'Date the user was added to the block_learningcoach_users table';
$string['privacy:metadata:block_learningcoach_users:time_register'] = 'Date on which the user must be registered';
$string['privacy:metadata:block_learningcoach_users:time_update'] = 'Profile update date';
$string['privacy:metadata:block_learningcoach_users:time_completion'] = 'Date on which the user fully completed their Learning Coach profile';
$string['privacy:metadata:block_learningcoach_users:fk_moodle_user_id'] = 'User ID on the Moodle platform';
$string['privacy:metadata:block_learningcoach_users:fk_lc_user_id'] = 'User ID on the Learning Coach app';

$string['privacy:metadata:block_learningcoach_cons_sco'] = 'Saves the scores obtained on the Learning Coach application';
$string['privacy:metadata:block_learningcoach_cons_sco:construct_score'] = 'Score from 0 to 100';
$string['privacy:metadata:block_learningcoach_cons_sco:profile_version'] = 'User profile version';
$string['privacy:metadata:block_learningcoach_cons_sco:fk_id_construct'] = 'Construct ID';
$string['privacy:metadata:block_learningcoach_cons_sco:fk_moodle_user_id'] = 'User ID on the Moodle platform';

$string['privacy:metadata:lc_user_infos'] = 'Data sent to the Learning Coach app';
$string['privacy:metadata:lc_user_infos:email'] = 'Email sent from Moodle to allow the Learning Coach app to share user data on the Moodle platform.';
$string['privacy:metadata:lc_user_infos:fistname'] = 'First name sent from Moodle to allow the Learning Coach app to share user data on the Moodle platform.';
$string['privacy:metadata:lc_user_infos:lastname'] = 'Last name sent from Moodle to allow the Learning Coach app to share user data on the Moodle platform.';
$string['privacy:metadata:lc_user_infos:lang'] = 'Language sent to the Learning Coach app to allow better user experience.';

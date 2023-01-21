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
 * Plugin administration pages are defined here.
 *
 * @package     block_learningcoach
 * @category    admin
 * @copyright   2022 Traindy / 3E-Innovation.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use block_learningcoach\Lc;

defined('MOODLE_INTERNAL') || die();
$mylc = new Lc();
if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading(
        'block_learningcoach/content_hub_settings',
        get_string('contenthub:settings:connexion', 'block_learningcoach'),
        ''
    ));

    $item = new admin_setting_configtext('block_learningcoach/apihost',
        get_string('apihost', 'block_learningcoach'),
        get_string('apihostinfo', 'block_learningcoach', 'href="https://app.learning.coach/en/integrations" target="_blank"'),
        '', PARAM_URL, 80);

    $item->set_updatedcallback(function () {
        $mylc = new Lc();
        $mylc->assign_capability_ws();
        $mylc->add_user_to_ws();
    });
    $settings->add($item);

    /*$settings->add(new admin_setting_configtext('block_learningcoach/apihost', get_string('apihost', 'block_learningcoach'),
        get_string('apihostinfo', 'block_learningcoach',
        'href="https://app.learning.coach/en/integrations" target="_blank"'), '', PARAM_URL, 80));*/

    $settings->add(new admin_setting_configtext('block_learningcoach/servicekey', get_string('servicekey', 'block_learningcoach'),
        get_string('servicekeyinfo', 'block_learningcoach',
        'href="https://app.learning.coach/en/integrations" target="_blank"'), '', PARAM_RAW, 30));

    $settings->add(new admin_setting_description('block_learningcoach/moodlehost', get_string('moodlehost', 'block_learningcoach'),
        get_string('moodlehostinfo', 'block_learningcoach')));

    $settings->add(new admin_setting_description('block_learningcoach/moodletoken',
    get_string('moodletoken', 'block_learningcoach'), $mylc->get_ws_token()));

    $settings->add(new admin_setting_configtext('block_learningcoach/notificationstartcourse',
        get_string('notificationstartcourse', 'block_learningcoach'),
        get_string('notificationstartcourseinfo', 'block_learningcoach'), '5', PARAM_INT, 3));

    $settings->add(new admin_setting_heading(
        'block_learningcoach/content_hub_policy', get_string('contenthub:settings:enrolment', 'block_learningcoach'), ''));

    $options = array(
        'manual' => get_string('enrolment_manual', 'block_learningcoach'),
        'automatic' => get_string('enrolment_automatic', 'block_learningcoach'),
        'learner' => get_string('enrolment_learner', 'block_learningcoach')
    );
    $settings->add(new admin_setting_configselect('block_learningcoach/enrolment',
        get_string('enrolment', 'block_learningcoach'),
        get_string('enrolment_help', 'block_learningcoach'), 'manual', $options ));

    /* $settings->add(new admin_setting_configcheckbox('block_learningcoach/licence', get_string('licence', 'block_learningcoach'),
        get_string('licenceinfo', 'block_learningcoach'), '', 1)
    ); */

    $settings->add(new admin_setting_configtext('block_learningcoach/tag', get_string('tag', 'block_learningcoach'),
        get_string('taginfo', 'block_learningcoach'), '', PARAM_RAW, 30));

}




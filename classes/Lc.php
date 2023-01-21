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
 * Block learningcoach is defined here.
 *
 * @package     block_learningcoach
 * @copyright   2022 Traindy / 3E-Innovation
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_learningcoach;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot .'/webservice/lib.php');
require_once($CFG->dirroot .'/lib/externallib.php');
require_once($CFG->dirroot.'/cohort/lib.php');

/**
 * This is the main Learning Coach Class
 *
 * @package    block_learningcoach
 * @copyright  2022 Traindy/3E innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Lc {

    /** @var string Default Learning Coach API Key */
    private $lcapikey = "";

    /** @var string Default Learning Coach server url */
    private $lcurl = "";

    /** @var string Learning Coach enrolment policy */
    public $lcenrolmentpolicy = "";

    /** @var string Default Learning Coach service ID */
    private $serviceid = "3e-innovation-moodle-service-sph30m3sw8sm055of6hd";

    /** @var string Learning Coach Dimension table in DB */
    public $tabledimension = 'block_learningcoach_dim';

    /** @var string Learning Coach table in DB */
    public $tabledimensiontrans = 'block_learningcoach_dim_tra';

    /** @var string Learning Coach Construct table in DB */
    public $tableconstruct = 'block_learningcoach_const';

    /** @var string Learning Coach Construct Translation table in DB */
    public $tableconstructtrans = 'block_learningcoach_cons_tra';

    /** @var string Learning Coach Scores table in DB */
    public $tablescore = 'block_learningcoach_cons_sco';

    /** @var string Learning Coach Stats table in DB */
    public $tablestats = 'block_learningcoach_stats';

    /** @var string Cohort table in DB */
    public $tablecohort = 'cohort';

    /** @var string Learning Coach Users table in DB */
    public $tablelcusers = 'block_learningcoach_users';

    /** @var string Learning Coach Users table in DB */
    public $tablelcerror = 'block_learningcoach_log_err';

    /** @var string User table in DB */
    public $tableuser = 'user';

    /** @var string Role table in DB */
    public $tablerole = 'role';

    /** @var string External Services table in DB */
    public $tableexternalservices = 'external_services';

    /** @var string External Tokenstable in DB */
    public $tableexternaltokens = 'external_tokens';

    /** @var string Name of the cohort*/
    public $cohortname = 'LearningCoach';

    /** @var string Id of the cohort*/
    public $cohortidnumber = 'LearningCoachTraindy';

    /** @var string Shortname of the role for the webservice*/
    public $wsshortname = 'ws_traindy';

    /** @var string Name of the webservice (from services.php)*/
    public $externalserviceshortname = 'LCTraindyws';
    /**
     * Constructor for the class.
     */
    public function __construct() {
        $this->lcapikey = get_config('block_learningcoach', 'servicekey');
        $this->lcurl = get_config('block_learningcoach', 'apihost');
        $this->lcenrolmentpolicy  = get_config('block_learningcoach', 'enrolment');
    }

    /**
     * callapi call learningCoachAPI
     *
     * @param string $service : service route
     * @param boolean $post : boolean to call either POST or GET
     * @param array $params : optionnal array of parameters
     * @return array with code (CURLINFO_HTTP_CODE)  and content
     */
    public function callapi($service, $post, $params = array()) {
        $content = "";
        $url = $this->lcurl.$service;
        $httpreturncode  = "";
        try {
            $curl = curl_init();

            // Check if initialization had gone wrong.
            if ($curl === false) {
                throw new Exception('failed to initialize');
            }

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new Exception ($url." is not a valid URL");
                die;
            }
            if (substr($url, 0, 8) != "https://") {
                throw new Exception ($url." is not a valid URL. It mus begin with https://");
                die;
            }

            // If API is called with POST.
            if ($post) {
                // curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt( $curl, CURLOPT_POST, true);
                curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($params));
            } else {
                // API called with GET.
                $url = $url.'?'.http_build_query($params);

            }

            $options = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                // CURLOPT_SSL_VERIFYPEER => false,only for tests.
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $post ? 'POST' : 'GET',
                CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-Requested-With: XMLHttpRequest',
                'Authorization: Bearer '.$this->lcapikey,
                ),

            );
            curl_setopt_array($curl, $options);

            $content = curl_exec($curl);

            // Check the return value of curl_exec(), too.
            if ($content === false) {
                throw new Exception(curl_error($curl), curl_errno($curl));
            }
            // Check HTTP return code, too; might be something else than 200.
            $httpreturncode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        } catch (Exception $e) {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);
                $content = "Exception ".$e->getMessage();
                $httpreturncode = $e->getCode();

        } finally {
            // Close curl handle unless it failed to initialize.
            if (is_resource($curl)) {
                curl_close($curl);
            }
            return array("code" => $httpreturncode, "content" => $content);
        }

    }


    /**
     * Get the scales and construct from LearningCoach servers and insert/update the database.
     * WARNING can't be called on installation as the setting to call the API are requested
     *
     * @return array with code and content
     *
     *
     */
    public function fill_scales_constructs() {
        global $DB, $USER;

        if (isset($_GET['lang'])) {
            $lang = $_GET['lang'];
        } else {
            $lang = $USER->lang;
        }

        $profiledetail = $this->callapi("/profile/structure", true, array('version' => '1', 'language' => $lang));

        $tabprofiledetail = json_decode($profiledetail['content'], true);

        $dimensions = [];
        $dimensionstranslation = [];

        // ! Profile version beta version.
        // $profileversion = $tabprofiledetail['data']['meta']['assessment_version'];

        $profileversion = 1;
        $language = $tabprofiledetail['data']['meta']['language'];

        $atabinserteddim = [];
        $tabinserteddim = $DB->get_records($this->tabledimension);
        foreach ($tabinserteddim as $dim) {
            $atabinserteddim[$dim->ref] = $dim->id;
        }

        $atabinserteddimtrans = [];
        $tabinserteddimtrans = $DB->get_records($this->tabledimensiontrans);
        foreach ($tabinserteddimtrans as $dimtrans) {
            $atabinserteddimtrans[$dimtrans->fk_id_dimension][$dimtrans->lang] = $dimtrans->id;
        }

        // Loop on dimensions.
        foreach ($tabprofiledetail['data']['scales'] as $dimension => $value) {

            $dim = new \stdClass();
            $dim->ref = $dimension;

            array_push($dimensions, $dim);

            // Check if dimension already exists in DB.
            if (!isset($atabinserteddim[$dim->ref])) {
                $dimid = $DB->insert_record($this->tabledimension, $dim);
            } else {
                $dimid = $atabinserteddim[$dim->ref];
            }

            $dimtrans = new \stdClass();
            $dimtrans->name = $value['name'];
            $dimtrans->description = $value['definition'];
            $dimtrans->lang = $language;
            $dimtrans->fk_id_dimension = $dimid;

            array_push($dimensionstranslation, $dimtrans);

            // Check if dimension translation already exists in DB.
            if (!isset($atabinserteddimtrans[$dimid][$language])) {
                $DB->insert_record($this->tabledimensiontrans, $dimtrans);
            }

            $atabinsertedconstruct = [];
            $tabinsertedconstruct = $DB->get_records($this->tableconstruct);

            foreach ($tabinsertedconstruct as $const) {
                $atabinsertedconstruct[$const->tag] = $const->id;
            }

            $tabconstruits = [];
            $tabconstruitstrans = [];

            // Check if construct translation already exists in DB.
            $atabinsertedconstructtrans = [];
            $tabinsertedconstructtrans = $DB->get_records($this->tableconstructtrans);

            foreach ($tabinsertedconstructtrans as $constucttrans) {
                $atabinsertedconstructtrans[$constucttrans->fk_id_construct][$constucttrans->lang] = $constucttrans->id;
            }

            // Loop on constructs.
            foreach ($value['constructs'] as $construitcode => $construit) {
                $construitobj = new \stdClass();
                $construitobj->tag = $construitcode;
                $construitobj->profile_version = $profileversion;
                $construitobj->fk_id_dimension = $dimid;
                array_push($tabconstruits, $construitobj);

                if (!isset($atabinsertedconstruct[$construitobj->tag])) {
                    $constructid = $DB->insert_record($this->tableconstruct, $construitobj);
                } else {
                    $constructid = $atabinsertedconstruct[$construitobj->tag];
                }

                $construittrans = new \stdClass();
                $construittrans->name = $construit['name'];
                $construittrans->description = $construit['definition'];
                $construittrans->lang = $language;
                $construittrans->fk_id_construct = $constructid;

                array_push($tabconstruitstrans, $construittrans);

                if (!isset($atabinsertedconstructtrans[$constructid][$language])) {
                    $DB->insert_record($this->tableconstructtrans, $construittrans);
                }
            }

        }
        return ['code' => 200, 'data' => 'OK'];
    }

    /**
     * Create the LearningCoach cohort.
     *
     * @return $lccohortdb
     */
    public function create_lc_cohort() {
        global $DB;

        // Check if cohort LearningCoach allready exists.
        $tabinsertedcohort = $DB->get_records($this->tablecohort);

        $moodlecohorts = [];
        foreach ($tabinsertedcohort as $moodlecohort) {
            array_push($moodlecohorts, $moodlecohort->name);
        }

        // Construction of learningCoach cohort object.
        $objcohort = new \stdClass();
        $objcohort->contextid = 1;
        $objcohort->name = $this->cohortname;
        $objcohort->idnumber = $this->cohortidnumber;
        $objcohort->descriptionformat = 1;
        $objcohort->visible = 1;
        $objcohort->component = '';
        $objcohort->timecreated = time();
        $objcohort->timemodified = time();

        // Check if $objcohort->name exists in array $moodlecohorts.
        if (in_array($objcohort->name, $moodlecohorts)) {
            return false;
        } else {
            $lccohortdb = $DB->insert_record($this->tablecohort, $objcohort);
        }

        return $lccohortdb;
    }

    /**
     *
     *  Enrol a user in the LearningCoach cohort and register in LearningCoach app.
     *
     * @param int $userid
     * @return array with code and content
     *
     */
    public function enrol_in_lc_cohort($userid) {
        global $DB;
        $cohort = $DB->get_record($this->tablecohort, array('idnumber' => $this->cohortidnumber));
        if (!$cohort) {
            return ['code' => 0, 'data' => 'Error: cohort not found'];
        } else {
            \cohort_add_member($cohort->id, $userid);
            // User is then registred in LC app with event observer cohort member added.
            return ['code' => 1, 'data' => 'Enrolment ok'];
        }

    }

    /**
     * Get the users from LearningCoach cohort and register them in LearningCoach app.
     * @return Json
     */
    public function get_cohort_users() {
        GLOBAL $DB;
        $sql = "SELECT cm.userid, u.id, u.email, u.firstname, u.lastname, u.lang
                FROM {cohort_members} cm
                LEFT JOIN {cohort} c ON c.id = cm.cohortid
                LEFT JOIN {block_learningcoach_users} lcu ON lcu.fk_moodle_user_id = cm.userid
                LEFT JOIN {user} u ON u.id = cm.userid
                WHERE c.idnumber = 'LearningCoachTraindy'
                AND lcu.fk_moodle_user_id IS null
                LIMIT 100
            ";
        $userstoregister = $DB->get_records_sql($sql);
        return $this->register_user($userstoregister);
    }

    /**
     * Call LearningCoach API to register users in LearningCoach app
     *
     * @param array $userstoregister Array Moodle Users Object
     * @return array with code and content
     */
    public function register_user($userstoregister) {
        global $DB, $CFG;
        // $startlicence= new DateTime("now", core_date::get_user_timezone_object());
        $now = new \DateTime("now", \core_date::get_server_timezone_object());
        $startlicence = $now->format('Y-m-d');

        $tabuser = [];
        // Associative array email => id.
        $moodleusers = [];

        $tags = get_config('block_learningcoach', 'tag');
        if ($tags == '') {
            $tags = [];
        } else {
            $tags = explode(',', get_config('block_learningcoach', 'tag'));
        }

        foreach ($userstoregister as $user) {
            // Test if $user is an object, if not, this means that it is not a user.
            if (is_object($user)) {
                $oneuser = [
                    'email' => $user->email,
                    'first_name' => $user->firstname,
                    'last_name' => $user->lastname,
                    'language' => $user->lang,
                    'license' => [
                        'start' => $startlicence,
                        'renew' => '3'
                    ],
                    'tags' =>
                        $tags
                    ,
                    'allow_mobile' => true
                ];

                $moodleusers[$user->email] = $user->id;
                array_push($tabuser, $oneuser);
            }

        }

        $users['users'] = $tabuser;

        if (count($tabuser) > 0) {
            $usersregistered = $this->callapi("/users/register", true, $users);

            // Error creating user in LearningCoach app.
            if ($usersregistered["code"] != "201") {
                $this->log_error("error_lcapi", $usersregistered['code'].$usersregistered['content']);
                // Returns LC code error.
                return ['code' => $usersregistered['code'], 'data' => $usersregistered['content']];
            } else {
                // Everything ok, we can now register the user in the LC table.
                $data = $usersregistered["content"];

                return $this->insert_lc_users($data, $moodleusers);
            }
        } else {
            return ['code' => '200', 'data' => 'Aucun utilisateur à envoyer'];
        }

    }

    /**
     *
     * Insert users in database
     *
     * @param Jons $data json with users from learning coach
     * @param Array $moodleusers array with email => id
     * @return array with code and data ['code' => '', 'data' => '']
     */
    public function insert_lc_users($data, $moodleusers) {
        global $CFG, $DB;

        $tab = json_decode($data, true);

        $tablcusers = [];
        $placeholders = [];
        $insertvalues = [];

        foreach ($tab['data'] as $key => $value) {
            if (isset($moodleusers[$key])) {

                // $key is e-mail and $value is lc_id
                $oneuser = array(1, 1, time(), intval($moodleusers[$key]), $value);
                array_push($tablcusers, $oneuser);
                // Generate array with ?,?,?,?,?
                array_push($placeholders, '('.$this->gen_row_place_holders(5).')');
                // DB fields : registered  data_acces  time_added  fk_moodle_user_id  fk_lc_user_id.
                array_push($insertvalues, 1, 1, time(), intval($moodleusers[$key]), $value);

            }
        }
        // Make string from array to have someting like (?,?,?,?,?),(?,?,?,?,?).
        $places = implode(',', $placeholders);

        // Check MySQL version.
        $dbhost = $CFG->dbhost;
        $dbuser = $CFG->dbuser;
        $dbpass = $CFG->dbpass;

        $mysqli = new \mysqli ($dbhost, $dbuser, $dbpass);

        if (count($insertvalues) > 0) {
            // Depending on MySQL version, use the appropriate syntax for the query :
            // If mysql < 8.0.19 use VALUES.
            if (stripos($mysqli->server_info, "mariadb") == true ||
                (stripos($mysqli->server_info, "mariadb") == false && $mysqli->server_version < 80019)) {
                // If (stripos($mysqli->server_info, "mariadb") !== false || $mysqli->server_version < 80019) {
                // On online test server MariaDB 100244 goes there (tested on 2022-09-02).
                $sql = "INSERT INTO {block_learningcoach_users}
                                    (registered, data_acces, time_added, fk_moodle_user_id, fk_lc_user_id)
                                    VALUES $places
                                    ON DUPLICATE KEY UPDATE fk_lc_user_id = VALUES(fk_lc_user_id), data_acces = VALUES(data_acces)
                                    ";
            } else {
                // MySQL > 8.0.19.
                $sql = "INSERT INTO {block_learningcoach_users}
                                    (registered, data_acces, time_added, fk_moodle_user_id, fk_lc_user_id)
                                    VALUES $places AS n
                                    ON DUPLICATE KEY UPDATE fk_lc_user_id = n.fk_lc_user_id, data_acces=n.data_acces";
            }
            $status = $DB->execute($sql, $insertvalues);
            if (!$status) {
                $this->log_error("error_mdl", "Problem inserting data in database might be problem with MySQL ON DUPLICATE KEY UPDATE");
                return ['code' => '405', 'data' => 'Problem inserting data in database'];
            }
        }
        return ['code' => '201', 'data' => 'OK'];
    }


    /**
     * Generates placeholders for multiple rows insertion
     *
     * @param integer $count
     * @return String $result
     */
    private function gen_row_place_holders($count=0) {
        $result = array();
        if ($count > 0) {
            for ($x  = 0; $x < $count; $x++) {
                $result[] = '?';
            }
        }
        return implode(',', $result);
    }

    /**
     * Create the Learning Coach web service user
     *
     * @return Id of user created. If user already existe return false
     */
    public function create_lc_ws_user() {
        global $DB;

        // Create password for webservice user.
        $password = $this->generate_password();
        $hashpassword = password_hash($password, PASSWORD_DEFAULT);

        // Construction of traindy webservice user object.
        $objwsuser = new \stdClass();
        $objwsuser->username = 'noreply@traindy.io';
        $objwsuser->password = $hashpassword;
        $objwsuser->firstname = 'Traindy';
        $objwsuser->lastname = 'Webservice';
        $objwsuser->email = 'noreply@traindy.io';
        $objwsuser->mnethostid = 1;
        $objwsuser->confirmed = 1;
        $objwsuser->policyagreed = 1;

        // Check if $objwsuser->name exists in table moodle users.
        if (!$DB->get_record($this->tableuser, array('username' => $objwsuser->username))) {
            $wsuserdb = $DB->insert_record($this->tableuser, $objwsuser);
        } else {
            return $wsuserdb = false;
        }
        return $wsuserdb;
    }

    /**
     * Generate a random password
     *
     * @param integer $len
     * @return void
     */
    private function generate_password($len = 12) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789*!@";
        $password = substr(str_shuffle($chars), 0, $len);
        return $password;
    }

    /**
     * Assign the webservice/rest:use capability to the role for the webservice
     *
     * @return Boolean
     */
    public function assign_capability_ws() {
        global $DB;
        $role = $DB->get_record($this->tablerole, array('shortname' => $this->wsshortname));
        $systemcontext = \context_system::instance();
        $result = assign_capability('webservice/rest:use', CAP_ALLOW, $role->id, $systemcontext->id, true);
        return $result;
    }

    /**
     * Add the user to the the webservice
     *
     * @return int or null
     */
    public function add_user_to_ws() {
        global $DB;
        $userid = $DB->get_field($this->tableuser, 'id', ['username' => 'noreply@traindy.io']);
        $systemcontext = \context_system::instance();
        $webservice = new \webservice;
        $lcwebservice = $webservice->get_external_service_by_shortname($this->externalserviceshortname);
        // Add user to external service.
        $user = new \stdClass();
        $user->userid = $userid;
        $user->externalserviceid = $lcwebservice->id;

        $res = $webservice->get_ws_authorised_user($lcwebservice->id, $userid);
        if (!$res) {
            $externalserviceuser = $webservice->add_ws_authorised_user($user);
            // Create token.
            $token = external_generate_token(EXTERNAL_TOKEN_PERMANENT, $lcwebservice->id, $userid, $systemcontext->id);
        }
    }

    /**
     * Create Learning Coach web service role and add function to it
     *
     * @return Boolean true
     */
    public function create_ws_role() {
        global $DB;

        // Construction of traindy webservice role object.
        $objwsrole = new \stdClass();
        $objwsrole->name = 'WebService Traindy';
        $objwsrole->shortname = $this->wsshortname;
        $objwsrole->description = 'Role used for Learning Coach webservice';

        // Find free sortorder number.
        $res = $DB->get_field('role', 'id', array('shortname' => $objwsrole->shortname));

        // Check if $objwsrole->shortname exists in array $moodleroles.
        if (!$res) {
            $roleid = create_role($objwsrole->name, $objwsrole->shortname, $objwsrole->description);
        } else {
            $roleid = (int)$res;
        }

        // Role context level.
        $contextlevels = [CONTEXT_SYSTEM, CONTEXT_USER, CONTEXT_COURSE];
        $contexts = set_role_contextlevels($roleid, $contextlevels);

        // Role assignment.
        $userid = $DB->get_field($this->tableuser, 'id', ['username' => 'noreply@traindy.io']);
        $systemcontext = \context_system::instance();
        $assignementsystem = role_assign($roleid, $userid, $systemcontext->id, $component = '', $itemid = 0, $timemodified = '');

        // Capabilities.
        $courseview = assign_capability('moodle/course:view', CAP_ALLOW, $roleid, $systemcontext->id);
        $courseupdate = assign_capability('moodle/course:update', CAP_ALLOW, $roleid, $systemcontext->id);
        $courseviewhiddencourses = assign_capability('moodle/course:viewhiddencourses', CAP_ALLOW, $roleid, $systemcontext->id);
        // Can't assing 'webservice/rest :use' here because the capability is not yet defined during installation

        // Return true if everything is ok
        return true;
    }

     /**
      * return webservice token
      *
      * @return string token or message no token
      */
    public function get_ws_token() {
        GLOBAL $DB, $CFG;

        $externalservice = $DB->get_field($this->tableexternalservices, 'id', ['shortname' => $this->externalserviceshortname]);

        $wstoken = $DB->get_field($this->tableexternaltokens, 'token', ['externalserviceid' => $externalservice]);
        if (!$wstoken) {
            $wstoken = "no token";
        }
        return $wstoken;
    }

    /**
     * Retrieves scores from database for a user
     * @param int $userid
     * @param string $lang
     * @return array $results
     */
    public function get_scores_infos($userid, $lang) {
        GLOBAL $DB;

        $sql = "SELECT cs.id, cs.construct_score,
                    c.tag AS construct_tag,
                    ct.name AS construct_name, ct.description AS construct_description, ct.lang AS construct_lang,
                    dt.name AS dimension_name, dt.description AS dimension_description, dt.lang AS dimension_lang
                FROM {block_learningcoach_cons_sco} cs
                JOIN {block_learningcoach_const} c ON c.id = cs.fk_id_construct
                JOIN {block_learningcoach_cons_tra} ct ON ct.fk_id_construct = c.id
                JOIN {block_learningcoach_dim} d ON d.id = c.fk_id_dimension
                JOIN {block_learningcoach_dim_tra} dt ON dt.fk_id_dimension = d.id
                AND dt.lang = ct.lang
                WHERE cs.fk_moodle_user_id = :userid AND dt.lang = :lang
                ORDER BY dimension_name, construct_name
            ";

        $params = ['userid' => $userid, 'lang' => $lang];
        $results = $DB->get_records_sql($sql, $params);

        return $results;
    }

    /**
     * This function allow to retrieve all Learning Coach learners linked to a course
     *
     * @param int $courseid
     * @return array $results
     */
    public function get_learners($courseid) {

        GLOBAL $DB;

        $sql = "SELECT lcu.id, lcu.fk_lc_user_id, lcu.fk_moodle_user_id, lcu.data_acces,
                    u.id AS user_id, u.firstname AS user_firstname, u.lastname AS user_lastname
                FROM {block_learningcoach_users} lcu
                JOIN {user} u ON u.id = lcu.fk_moodle_user_id
                JOIN {role_assignments} ra ON ra.userid = lcu.fk_moodle_user_id
                JOIN {role} r ON ra.roleid = r.id
                JOIN {context} con ON ra.contextid = con.id
                JOIN {course} c ON c.id = con.instanceid AND con.contextlevel = 50
                WHERE r.shortname = 'student'
                AND c.id = :courseid
                ORDER BY u.lastname ASC
            ";
        $params = ['courseid' => $courseid];
        $results = $DB->get_records_sql($sql, $params);

        return $results;
    }

    /**
     * Get one LC User
     *
     * @param [id] $moodleuserid
     * @return sdtClass lcuser  or false
     */
    public function get_lc_user($moodleuserid) {
        GLOBAL $DB;
        return $DB->get_record_select("block_learningcoach_users", 'fk_moodle_user_id = ?', [$moodleuserid]);
    }

    /**
     * This function allow to retrieve all groups linked to a course
     *
     * @param int $courseid
     * @return array $results
     */
    public function get_groups($courseid) {

        GLOBAL $DB;

        $sql = "SELECT id, courseid, name
                FROM {groups}
                WHERE courseid = :courseid
            ";
        $params = ['courseid' => $courseid];
        $results = $DB->get_records_sql($sql, $params);

        return $results;
    }


    /**
     * Get the learners belonging to a group
     *
     * @param int $groupid
     * @param int $courseid
     * @return array $results
     */
    public function get_group_learners($groupid, $courseid) {

        GLOBAL $DB;

        $sql = "SELECT gm.userid,
                        u.id AS user_id, u.firstname AS user_firstname, u.lastname AS user_lastname,
                        lcu.id, lcu.fk_lc_user_id, lcu.fk_moodle_user_id, lcu.data_acces
                FROM {groups_members} gm
                JOIN {groups} g ON g.id = gm.groupid AND g.courseid = courseid
                JOIN {user} u ON u.id = gm.userid
                JOIN {block_learningcoach_users} lcu ON lcu.fk_moodle_user_id = u.id
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {role} r ON ra.roleid = r.id
                JOIN {context} con ON ra.contextid = con.id
                JOIN {course} c ON c.id = con.instanceid AND con.contextlevel = 50
                WHERE g.id = :groupid
                AND c.id = :courseid
                AND r.shortname = 'student'
                ORDER BY u.lastname ASC
            ";

        $params = ['groupid' => $groupid, 'courseid' => $courseid];
        $results = $DB->get_records_sql($sql, $params);

        return $results;
    }

    /**
     * Get MySQL info and version
     *
     * @return array with server info and version
     */
    private static function get_my_sql_infos() {
        global $CFG;
        $mysqli = new \mysqli ($CFG->dbhost, $CFG->dbuser, $CFG->dbpass);
        return ['server_info' => $mysqli->server_info, 'server_version' => $mysqli->server_version];
    }

    /**
     *
     * Get the ALPI (Adaptive Learning Profile Inventory) profile of a user from Learning Coach API if not already in DB
     *
     * @param int $moodleuserid Moodle user id
     * @param bool $forceupdate force update from Learning Coach API
     * @return array code and message
     * return codes :
     * 0 user not found
     * 1 profile updated
     * 2 and up : errors
     */
    public function update_profile_user($moodleuserid, $forceupdate=false) {
        GLOBAL $DB;
        $callapi = false;
        // Check that the given user is actually available in Moodle.
        $lcuser = $DB->get_record($this->tablelcusers, ['fk_moodle_user_id' => $moodleuserid]);
        if (!$lcuser) {
            return ['code' => '0', 'message' => 'User not found in Moodle Table LC Users'];
        }
        if ($forceupdate === false) {
            // Check if the profile is already in the database.
            if ($lcuser->time_updated == 0 || is_null($lcuser->time_updated)) {
                $callapi = true;
            } else {
                return ['code' => '2', 'message' => 'Profile already in database'];
            }
        } else {
            // Forceupdate is true.
            $callapi = true;
        }

        if ($callapi) {
            // Call the API to get the user profile.
            $profileuserfromapi = $this->callapi("/profile", true, array('user_id' => $lcuser->fk_lc_user_id));
            if ($profileuserfromapi['code'] == 200) {
                $json = json_decode($profileuserfromapi['content'], true);
                $profilejson = $json['data'];
                // Get construct ids.
                $tabconstructdescription = $DB->get_records('block_learningcoach_const');
                if (count($tabconstructdescription) > 0) {
                    $idsconstruct = [];
                    foreach ($tabconstructdescription as $construct) {
                        $idsconstruct[$construct->tag] = $construct->id;
                    }
                } else {
                    // Fill the database with the construct description.
                    $this->fill_scales_constructs();
                    return ['code' => '2', 'message' => 'Refresh teh page to get the profile'];
                }
                if (count($profilejson['scales']) == 0) {
                    return ['code' => '2', 'message' => 'No scales found in profile'];
                }
                // Build the insert values and placeholders for SQL query INSERT DUPLICATE KEY UPDATE.
                // Values to insert.
                $insertvalues = [];
                // Placeholders for the query.
                $placeholders = [];
                foreach ($profilejson['scales'] as $scale => $constructs) {
                    if (!is_null($constructs)) {
                        ;
                        foreach ($constructs as $constructtag => $score) {
                            array_push($insertvalues, $score, $profilejson['meta']['version'], intval($idsconstruct[$constructtag]), $lcuser->fk_moodle_user_id);
                            // gen array with ?,?,?,?
                            array_push($placeholders, '(' . self::gen_row_place_holders(4) . ')');
                        }
                    }
                }
                $places = implode(',', $placeholders);
                if (count($insertvalues) > 0) {
                    $mysqlinfo = self::get_my_sql_infos();
                    // Depending on MySQL version, use the appropriate syntax for the query.
                    if (stripos($mysqlinfo['server_info'], "mariadb") !== false || $mysqlinfo['server_version'] < 80019) {
                        $sql = "INSERT INTO {block_learningcoach_cons_sco}
                                (construct_score, profile_version, fk_id_construct, fk_moodle_user_id)
                                VALUES $places
                                ON DUPLICATE KEY UPDATE construct_score = VALUES(construct_score)
                                ";
                    } else {
                        // MySQL 8.0.19.
                        $sql = "INSERT INTO {block_learningcoach_cons_sco}
                                (construct_score, profile_version, fk_id_construct, fk_moodle_user_id)
                                VALUES $places AS n
                                ON DUPLICATE KEY UPDATE construct_score =  n.construct_score
                                ";
                    }
                    $status = $DB->execute($sql, $insertvalues);
                    $lcuser->time_updated = time();
                    // Time completion if profile is fully completed.
                    // Get all dimensions completed.
                    $sql = 'SELECT lccs.id AS score_id, lccs.fk_id_construct AS score_construct , lccs.fk_moodle_user_id,
                            lcc.id AS construct_id, lcc.fk_id_dimension AS construct_dimension_id,
                            lcd.id AS dimension_id
                            FROM {block_learningcoach_cons_sco} lccs
                            JOIN {block_learningcoach_const} lcc ON lcc.id = lccs.fk_id_construct
                            JOIN {block_learningcoach_dim} lcd ON lcd.id = lcc.fk_id_dimension
                            WHERE lccs.fk_moodle_user_id = :userid
                            GROUP BY lcd.id
                            ';
                    $params = ['userid' => $lcuser->fk_moodle_user_id];
                    $result1 = $DB->get_records_sql($sql, $params);
                    // Get all dimensions.
                    $sql = 'SELECT id
                    FROM {block_learningcoach_dim}
                    ';
                    $result2 = $DB->get_records_sql($sql);
                    if (count($result1) == count($result2)) {
                        // Profile completed
                        $lcuser->time_completion = time();
                    } else {
                        $lcuser->time_completion = null;
                    }
                    // Update Time updated & Time completion in DB.
                    $result = $DB->update_record($this->tablelcusers, $lcuser);
                    if (!$status) {
                        // Return error.
                        return ['code' => '2', 'message' => 'Problem inserting data in database'];
                    }
                }
                // Return success.
                return ['code' => '1', 'message' => 'Profile updated'];
                /* *********************** */
            } else {
                $jsonerror = json_decode($profileuserfromapi['content'], true);
                $error = $jsonerror['error'];
                switch ($error['code']) {
                    case 403:
                        // User do not share his data (Data Privacy)
                        // Update $lcuser into DB
                        $lcuser->data_acces = 0;
                        $result = $DB->update_record($this->tablelcusers, $lcuser);
                        // Delete scores
                        $result = $this->user_delete_scores($lcuser->fk_moodle_user_id);
                        break;
                    case 404:
                        // User not found
                        $result = $this->user_delete($lcuser->fk_moodle_user_id);
                        break;
                }
                // Return error
                return ['code' => $error['code'], 'message' => $error['title']];
            }
        }
    }


    /**
     * Get the Group profil from database first and if empty from Learning Coach API
     *
     * @param int $groupid group's id
     * @param int $courseid course's id  in which the group is located
     * @param bool $force force update from Learning Coach API
     *
     */
    public function profile_group($groupid, $courseid, $force = false) {

        GLOBAL $DB;

        $usersid = [];

        $groupmembers = [];

        $sql = "SELECT gm.userid, gm.timeadded AS group_member_added,
                        u.id AS user_id, u.firstname AS user_firstname, u.lastname AS user_lastname,
                        lcu.id, lcu.fk_lc_user_id, lcu.fk_moodle_user_id, lcu.data_acces
                FROM {groups_members} gm
                JOIN {groups} g ON g.id = gm.groupid AND g.courseid = courseid
                JOIN {user} u ON u.id = gm.userid
                JOIN {block_learningcoach_users} lcu ON lcu.fk_moodle_user_id = u.id
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {role} r ON ra.roleid = r.id
                JOIN {context} con ON ra.contextid = con.id
                JOIN {course} c ON c.id = con.instanceid AND con.contextlevel = 50
                WHERE g.id = :groupid
                AND c.id = :courseid
                AND r.shortname = 'student'
            ";

        $params = ['groupid' => $groupid, 'courseid' => $courseid];
        $results = $DB->get_records_sql($sql, $params);

        foreach ($results as $result => $value) {
            array_push($usersid, $value->fk_lc_user_id);
            array_push($groupmembers, intval($value->group_member_added));
        }

        // ! See to play with the date a user was added to the group for updating a group (lign nb 839)
        // Rsort($groupmembers);

        $statdb = $DB->get_record($this->tablestats, ['moodle_group_id' => $groupid]);

        // $date =(time() - 3600);

        $statsdata = new \stdClass();
        // If $statdb is empty, call Learning Coach API to get the group's profile.
        if (empty($statdb)) {
            $profilegroupapi = $this->callapi("/profile/group", true, array('users_ids' => $usersid));

            $statsdata->datas = json_encode($profilegroupapi);
            $statsdata->time_updated = time();
            $statsdata->moodle_group_id = $groupid;

            // Send $profilegroupapi into DB
            $groupstatdatas = $DB->insert_record($this->tablestats, $statsdata);

        } else if ($force || $statdb->time_updated < (time() - 3600)) {
            $profilegroupapi = $this->callapi("/profile/group", true, array('users_ids' => $usersid));

            $statsdata->id = $statdb->id;
            $statsdata->datas = json_encode($profilegroupapi);
            $statsdata->time_updated = time();

            // Update $profilegroupapi into DB
            $groupstatdatas = $DB->update_record($this->tablestats, $statsdata);

        } else {
            $statsdata->id = $statdb->id;
            $statsdata->datas = $statdb->datas;
            $statsdata->time_updated = $statdb->time_updated;
            $statsdata->moodle_group_id = $statdb->moodle_group_id;
        }

        return $statsdata;
        // return $groupstatdatas;
    }

    /**
     * Delete LC stats in DB if Moodle group is deleted (called by group_deleted event when a group is deleted for Moodle)
     *
     * @param int $groupid : group's id
     * @return array $results
     */
    public function group_delete_stats($groupid) {
        GLOBAL $DB;
        $datadeleted = $DB->delete_records($this->tablestats, ['moodle_group_id' => $groupid]);
        return $datadeleted;
    }

    /**
     *
     * Delete LC scores in DB if a user is deleted called if user doesn't want to share his data (Data Privacy) anymore
     * and when deleting a user
     *
     * @param int $moodleuserid : Moodle user id
     * @return array $results
     */
    public function user_delete_scores($moodleuserid) {
        GLOBAL $DB;
        $datadeleted = $DB->delete_records($this->tablescore, ['fk_moodle_user_id' => $moodleuserid]);
        return $datadeleted;
    }


    /**
     *
     * Delete LC user and data ssociated in DB if a user is deleted (called by user_deleted event when a user is deleted in Moodle)     *
     *
     * @param int $moodleuserid : Moodle user id
     * @return array $results
     */
    public function user_delete($moodleuserid) {
        GLOBAL $DB;
        $this->user_delete_scores($moodleuserid);
        $datadeleted = $DB->delete_records($this->tablelcusers, ['fk_moodle_user_id' => $moodleuserid]);
        return $datadeleted;

    }


    /**
     *
     * Delete all LC users and scores and group stats in DB (called by cohort_deleted event)
     *
     * @return array $results with booleans
     */
    public function delete_users_and_scoresand_stats() {
        GLOBAL $DB;
        $statsdeleted = $DB->delete_records($this->tablescore);
        $lcusersdeleted = $DB->delete_records($this->tablelcusers);
        $lcstatsdelted = $DB->delete_records($this->tablestats);
        return [$statsdeleted, $lcusersdeleted, $lcstatsdelted];
    }


    /**
     *
     *  Update data privacy for a user
     *
     * @param stdClass $lcuser LC user object
     * @return bool $results
     */
    public function update_data_acces($lcuser) {
        GLOBAL $DB;
        $result = $DB->update_record($this->tablelcusers, $lcuser);
        return $result;
    }

    /**
     *
     * Get % of completed profile
     * @param int $courseid Id of the course
     * @return int $percent
     */
    public function get_percent($courseid) {
        GLOBAL $DB;

        // Sql request to get total lc user and total profil completed :
        $sql = "SELECT SUM(lcu.time_completion > 0) AS completed, COUNT(lcu.id) AS total
                FROM {block_learningcoach_users} lcu
                JOIN {user} u ON u.id = lcu.fk_moodle_user_id
                JOIN {role_assignments} ra ON ra.userid = lcu.fk_moodle_user_id
                JOIN {role} r ON ra.roleid = r.id
                JOIN {context} con ON ra.contextid = con.id
                JOIN {course} c ON c.id = con.instanceid AND con.contextlevel = 50
                WHERE lcu.data_acces = 1
                AND c.id = :courseid
                AND r.shortname = 'student'
            ";
        $params = ['courseid' => $courseid];
        $results = $DB->get_record_sql($sql, $params);
        $totalprofilecompleted = intval($results->completed);

        $totallcuser = intval($results->total);

        if ($totalprofilecompleted == 0) {
            $percent = 0;
        } else {
            $percent = round(($totalprofilecompleted * 100) / $totallcuser);
        }
        return (int)$percent;
    }

    /**
     *
     *  Add logg to LC log error table
     *
     * @param string $type type of error (error, warning, info) _ then origin e.g.:error_lcapi
     * @param string $description description of error
     * @return bool $results
     */
    public function log_error($type, $description) {
        global $DB;
        $dataobject = new \stdClass();
        $dataobject->type = $type;
        $dataobject->description = $description;
        $dataobject->date_time_rec = time();
        return $DB->insert_record($this->tablelcerror, $dataobject);
    }

    /**
     *
     *  Clean old logs to have only 10 logs in the table
     *
     * @return bool $results
     */
    public function log_error_clean_older_recs() {
        global $DB;
        $sql = "DELETE log
                FROM {block_learningcoach_log_err} log
                JOIN
                    ( SELECT date_time_rec, id
                        FROM {block_learningcoach_log_err}
                        ORDER BY date_time_rec DESC, id DESC
                        LIMIT 1 OFFSET 10
                    ) AS lim
                ON log.date_time_rec < lim.date_time_rec
                OR log.date_time_rec = lim.date_time_rec AND log.id < lim.id ";

        $results = $DB->execute($sql);
        return $results;
    }

}

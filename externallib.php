<?php

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
 * External Web Service Template
 *
 * @package    localusercourses
 * @copyright  2021 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

class local_usercourses_external extends external_api {

    /**
     * list_users method parameters
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function list_users_parameters() {
        return new external_function_parameters(
            array(
                'limit' => new external_value(
                    PARAM_INT, 'A limit of the number of user records to return. Default is 50',
                    VALUE_DEFAULT, 50
                )
            )
        );
    }

    /**
     * Returns list of users
     * @return integer limit
     */
    public static function list_users($limit = 50) {
        global $DB;

        list($limit) = self::validate_parameters(
            self::list_users_parameters(),
            array('limit' => $limit)
        );

        self::permCheck();
        $fields_sql =  <<<SQL
            id, username, fullname, firstname, lastname, email,
            address, phone1, phone2
SQL;
        return $DB->get_records('user', null, 'DESC', $fields_sql, 0, $limit);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function list_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'The user record id'),
                    'username'   => new external_value(PARAM_NOTAGS, 'The user name of the user'),
                    'fullname'   => new external_value(PARAM_NOTAGS, 'The full name of the user'),
                    'firstname'   => new external_value(PARAM_NOTAGS, 'The first name(s) of the user'),
                    'lastname'    => new external_value(PARAM_NOTAGS, 'The family name of the user'),
                    'email' => new external_value(PARAM_EMAIL, 'A valid and unique email address'),
                    'address' => new external_value(PARAM_TEXT, 'The user home address'),
                    'phone1' => new external_value(PARAM_ALPHANUMEXT, 'The user primary phone'),
                    'phone2' => new external_value(PARAM_ALPHANUMEXT, 'The user secondary phone'),
                )
            )
        );
    }

    /**
     * list_courses method parameters
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function list_courses_parameters() {
        return new external_function_parameters(
            array(
                'limit' => new external_value(
                    PARAM_INT, 'A limit of the number of user records to return. Default is 50',
                    VALUE_DEFAULT, 50
                )
            )
        );
    }

    /**
     * Returns list of users
     * @return integer limit
     */
    public static function list_courses($limit = 50) {
        global $DB;

        list($limit) = self::validate_parameters(
            self::list_users_parameters(),
            array('limit' => $limit)
        );

        self::permCheck();
        return $DB->get_records('course', null, 'DESC', '*', 0, $limit);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function list_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'The course record id'),
                    'fullname' => new external_value(ARAM_ALPHANUMEXT, 'The full name of the course'),
                    'shortname' => new external_value(ARAM_ALPHANUMEXT, 'The short name of the course'),
                )
            )
        );
    }

    /**
     * list_users_courses method parameters
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function list_users_courses_parameters() {
        return new external_function_parameters(
            array(
                'limit' => new external_value(PARAM_INT, 'A limit of the number of user records to return. Default is 50', VALUE_DEFAULT, 50)
            )
        );
    }
    
    /**
     * Returns list of users enrolled in the courses and their grades
     * @return integer limit
     */
    public static function list_users_courses($limit = 50) {
        global $CFG, $DB;
        require_once("$CFG->dirroot/user/lib.php");

        list($limit) = self::validate_parameters(
            self::list_users_parameters(),
            array('limit' => $limit)
        );

        self::permCheck();
        $data = [];
        $users = $DB->get_records('user', null, 'DESC', '*', 0, $limit);
        $fields =  array(
            'id', 'username', 'fullname', 'firstname', 'lastname', 'email',
            'address', 'phone1', 'phone2', 'enrolledcourses'
        );
        foreach ($users as $user) {
            $data = user_get_user_details($user, null, $fields);
            // Todo: find user grades
        }

        return $data;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function list_users_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'The user record id'),
                    'username'   => new external_value(PARAM_NOTAGS, 'The user name of the user'),
                    'fullname'   => new external_value(PARAM_NOTAGS, 'The full name of the user'),
                    'firstname'   => new external_value(PARAM_NOTAGS, 'The first name(s) of the user'),
                    'lastname'    => new external_value(PARAM_NOTAGS, 'The family name of the user'),
                    'email' => new external_value(PARAM_EMAIL, 'A valid and unique email address'),
                    'address' => new external_value(PARAM_TEXT, 'The user home address'),
                    'phone1' => new external_value(PARAM_ALPHANUMEXT, 'The user primary phone'),
                    'phone2' => new external_value(PARAM_ALPHANUMEXT, 'The user secondary phone'),
                    'enrolledcourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'The course record id'),
                                'fullname' => new external_value(ARAM_ALPHANUMEXT, 'The full name of the course'),
                                'shortname' => new external_value(ARAM_ALPHANUMEXT, 'The short name of the course'),
                            )
                        ), 'User enrolled courses', VALUE_OPTIONAL
                    )
                )
            )
        );
    }

    private static function permCheck()
    {
        global $USER;

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }
    }
}

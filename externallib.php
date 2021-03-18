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

        $params = self::validate_parameters(
            self::list_users_parameters(),
            array('limit' => $limit)
        );

        self::permCheck();
        $fields_sql =  <<<SQL
            id, username, firstname, lastname, email
SQL;
        return $DB->get_records('user', null, null, $fields_sql, 0, $params['limit']);
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
                    'username' => new external_value(PARAM_NOTAGS, 'The user name of the user'),
                    'firstname' => new external_value(PARAM_NOTAGS, 'The first name(s) of the user'),
                    'lastname' => new external_value(PARAM_NOTAGS, 'The family name of the user'),
                    'email' => new external_value(PARAM_TEXT, 'A unique email address'),
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

        $params = self::validate_parameters(
            self::list_users_parameters(),
            array('limit' => $limit)
        );

        $fields_sql =  <<<SQL
            id, fullname, shortname, idnumber, category
SQL;
        self::permCheck();
        return $DB->get_records('course', null, null, $fields_sql, 0, $params['limit']);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function list_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'course id'),
                    'fullname' => new external_value(PARAM_RAW, 'full name'),
                    'shortname' => new external_value(PARAM_RAW, 'course short name'),
                    'category' => new external_value(PARAM_INT, 'category id'),
                    'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
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
        require_once("$CFG->dirroot/grade/querylib.php");
        require_once("$CFG->dirroot/user/lib.php");

        $params = self::validate_parameters(
            self::list_users_parameters(),
            array('limit' => $limit)
        );

        self::permCheck();
        $data = [];
        $user_fields = ['id', 'username', 'firstname', 'lastname', 'email'];
        $users = $DB->get_records('user', null, null, '*', 0, $params['limit']);
        foreach ($users as $user) {
            $user_data = [];
            // Set user info
            foreach ($user_fields as $field) {
                $user_data[$field] = $user->$field;
            }


            $enrolledcourses = $grades = [];
            $courses = enrol_get_users_courses($user->id, true);
            foreach ($courses as $course) {
                if ($course->category) {
                    // Set enrolled courses
                    $coursecontext = context_course::instance($course->id);
                    $enrolledcourse = [];
                    $enrolledcourse['id'] = $course->id;
                    $enrolledcourse['fullname'] = format_string($course->fullname, true, array('context' => $coursecontext));
                    $enrolledcourse['shortname'] = format_string($course->shortname, true, array('context' => $coursecontext));

                    $enrolledcourses[] = $enrolledcourse;

                    // Set user course grades
                    $grade = grade_get_course_grade($user->id, $course->id);
                    $grades[] = ['courseid' => $course->id, 'shortname' => $course->shortname, 'grade' => $grade->grade];
                }
            }
            $user_data['enrolledcourses'] = $enrolledcourses;
            $user_data['grades'] = $grades;
            $data[] = $user_data;
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
                    'firstname'   => new external_value(PARAM_NOTAGS, 'The first name(s) of the user'),
                    'lastname'    => new external_value(PARAM_NOTAGS, 'The family name of the user'),
                    'email' => new external_value(PARAM_TEXT, 'A unique email address'),
                    'enrolledcourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'The course record id'),
                                'fullname' => new external_value(PARAM_ALPHANUMEXT, 'The full name of the course'),
                                'shortname' => new external_value(PARAM_ALPHANUMEXT, 'The short name of the course'),
                            )
                        ), 'User enrolled courses'
                    ),
                    'grades' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'courseid' => new external_value(PARAM_INT, 'The course record id'),
                                'shortname' => new external_value(PARAM_ALPHANUMEXT, 'The short name of the course'),
                                'grade' => new external_value(PARAM_INT, 'The user course grade'),
                            )
                        ), 'User course grades'
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

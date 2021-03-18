<?php

/**
 * Web service local plugin template external functions and service definitions.
 *
 * @package    localusercourses
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
    'local_usercourses_list_users' => array(
        'classname'   => 'local_usercourses_external',
        'methodname'  => 'list_users',
        'classpath'   => 'local/usercourses/externallib.php',
        'description' => 'Returns a list of users',
        'type'        => 'read',
    ),
    'local_usercourses_list_courses' => array(
        'classname'   => 'local_usercourses_external',
        'methodname'  => 'list_courses',
        'classpath'   => 'local/usercourses/externallib.php',
        'description' => 'Returns a list of courses',
        'type'        => 'read',
    ),
    'local_usercourses_list_users_courses' => array(
        'classname'   => 'local_usercourses_external',
        'methodname'  => 'list_users_courses',
        'classpath'   => 'local/usercourses/externallib.php',
        'description' => "Returns a list of users' courses",
        'type'        => 'read',
    )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'User Courses Service' => array(
        'functions' => array('local_usercourses_list_users', 'local_usercourses_list_courses', 'local_usercourses_list_users_courses'),
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'user-courses-service'
    )
);

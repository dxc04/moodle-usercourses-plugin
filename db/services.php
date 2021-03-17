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
    )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'User Courses Service' => array(
        'functions' => array ('local_usercourses_list_users'),
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'user-courses-service'
    )
);

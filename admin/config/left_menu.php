<?php
/**
 * Definition of the left menu in the admin.
 *
 * No more than two levels of the array
 *
 * the "sub" array links, MUST contain the parent's index at the start of the filename
 * i.e.
 * menu: Users
 * All the sub menus must have links, starting with "users",
 *  like
 *      users_settings.php
 *      users_permissions.php
 *      etc...
 *
 */

$left_menu = array(
    'Users'  => array(
        'Users'         => '?page=users_settings',
        // 'Groups'        => '?page=users_groups',
        // 'Permissions'   => '?page=users_permissions',
    ),
);
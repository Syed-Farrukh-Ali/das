<?php

return [

    'default_page_size' => 5,

    // Configurations for the user
    'users' => [

        'super_admin' => 'Super Admin',
        'campus' => 'Campus',
        'staff_member' => 'Staff Member',
        'teacher' => 'Teacher',
        'parent' => 'Parent',
        'student' => 'Student',

        /*
        * Whether or not new users need to be approved by an administrator before logging in
        * If this is set to true, then confirm_email is not in effect
        */
        'requires_approval' => env('REQUIRES_APPROVAL', true),

    ],

    // Configuration for roles
    'roles' => [
        // Whether a role must contain a permission or can be used standalone as a label
        'role_must_contain_permission' => true,
    ],

    'Apis' => [
        //
    ],
];

<?php if (!defined('PURCHASE_REQS')) { die('Oops!'); }

$config = [
    'database' => [
        'database_type' => 'sqlite',
        'database_file' => BASE_PATH . '/testing.db',
        'server' => 'localhost',
        'username' => 'USER',
        'password' => 'PASSWORD',
        'charset' => 'utf8',
        'option' => [],
    ],

    'ldap' => [
        'ldap_url' => 'ldaps://ldap.server.com',
        'bind_dn' => 'USER@DOMAIN',
        'bind_pw' => 'PASSWORD',
        'base_dn' => 'OU=Users,OU=MyBusiness,DC=d3,DC=local',
        'user_params' => [
            'username' => 'samaccountname',
            'fullname' => 'displayname',
            'email' => 'mail',
            'groups' => 'memberof',
        ],
    ],
];

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
];

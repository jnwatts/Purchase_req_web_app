<?php
define('PURCHASE_REQS', 1);
define('BASE_PATH', __DIR__);
require BASE_PATH . '/config.inc.php';
$loader = require BASE_PATH . '/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$container = new \Slim\Container([
    'settings' => [
        'displayErrorDetails' => true,
    ],
    'config' => $config,
    'db' => new medoo($config['database']),
]);
$app = new \Slim\App($container);

$app->get('/requests[/{req}]', '\PurchaseReqs\Requests:get');
$app->post('/requests[/{req}]', '\PurchaseReqs\Requests:post');
$app->delete('/requests/{req}', '\PurchaseReqs\Requests:delete');

$app->run();

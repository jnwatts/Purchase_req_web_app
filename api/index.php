<?php
define('PURCHASE_REQS', 1);
define('BASE_PATH', __DIR__);

$config_file = BASE_PATH . '/config.inc.php';
if (!file_exists($config_file)) {
    die('You must create ' . $config_file . ' before using this API. See config.inc.sample.php for inspiration.');
}
require $config_file;

$loader = require BASE_PATH . '/vendor/autoload.php';

$container = new \Slim\Container([
    'settings' => [
        'displayErrorDetails' => true,
    ],
    'config' => $config,
    'db' => new medoo($config['database']),
]);
$app = new \Slim\App($container);

$users = new \PurchaseReqs\Users($container);
try {
    $users->initAuthUser($_SERVER["PHP_AUTH_USER"]);
} catch (\Exception $e) {
    die($e->getMessage());
}

use \Psr7Middlewares\Middleware\TrailingSlash;
$app->add(new TrailingSlash(false));

$app->get('/requests[/{req}]', '\PurchaseReqs\Requests:get');
$app->post('/requests[/{req}]', '\PurchaseReqs\Requests:post');
$app->delete('/requests/{req}', '\PurchaseReqs\Requests:delete');

$app->get('/users[/{user}]', '\PurchaseReqs\Users:get');

$app->run();

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

$users = new \PurchaseReqs\Controllers\Users($container);
try {
    if (isset($_SERVER["PHP_AUTH_USER"])) {
        $users->initAuthUser($_SERVER["PHP_AUTH_USER"]);
    } else {
        $users->initAuthUser("");
    }
} catch (\Exception $e) {
    die($e->getMessage());
}

use \Psr7Middlewares\Middleware\TrailingSlash;
$app->add(new TrailingSlash(false));

$app->get('/requests[/{id}]', '\PurchaseReqs\Controllers\Requests:get');
$app->post('/requests[/{id}]', '\PurchaseReqs\Controllers\Requests:post');
$app->delete('/requests/{id}', '\PurchaseReqs\Controllers\Requests:delete');

$app->get('/users[/{id}]', '\PurchaseReqs\Controllers\Users:get');

$app->get('/', function() use($app) {
    return 'TODO: Self-documentation here';
});

$app->run();

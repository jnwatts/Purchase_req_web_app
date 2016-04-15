<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
    ],
]);

$container = $app->getContainer();
$container['db'] = new medoo([
    'database_type' => 'mysql',
    'database_name' => 'DATABASE',
    'server' => 'localhost',
    'username' => 'USER',
    'password' => 'PASSWORD',
    'charset' => 'utf8',
]);

$container['user'] = null;
if (isset($_SERVER["PHP_AUTH_USER"])) {
    $container['user'] = $_SERVER["PHP_AUTH_USER"];
}

class PurchaseRequests {
    protected $ci;

    public function __construct($ci) {
        $this->ci = $ci;
        $this->db = $ci->db;
    }

    public function get($request, $response, $args) {
        $where = null;
        if (isset($args->req)) {
            $where = ['requests.id' => $args->req];
        }

        /* TODO: Explicitly select columns */
        $reqs = $this->db->select(
                'requests',
                '*',
                $where);

        foreach ($reqs as &$r) {
            /* TODO: Explicitly select columns */
            $r['items'] = $this->db->select(
                'request_items',
                '*',
                ['request_items.request_id' => $r['id']]
                );
        }

        $result = $reqs;

        $response->getBody()->write(json_encode($result, true));
        return $response;
    }

    public function post($request, $response, $args) {
        $r = json_decode($request->getBody()->getContents());
        $result = -1;

        $this->db->action(function ($db) use (&$r, &$result) {
            $success = true;
            try {

                // Remove items from request object
                $items = $r->items;
                unset($r->items);

                /* TODO: Explicitly extract specific columns and sanitize(?) values */
                $req = $db->insert('requests', (array)$r);

                foreach ($items as &$i) {
                    $i->request_id = $req;

                    /* TODO: Explicitly extract specific columns and sanitize(?) values */
                    $db->insert('request_items', (array)$i);
                    $this->checkError($db);
                }
                $result = $this->formatResponse(['id' => $req]);
            } catch(Exception $e) {
                $result = $this->formatError(400, $e->getMessage());
                $success = false;
            }

            return $success;
        });

        $response->getBody()->write($result);
        return $response;
    }

    public function checkError($db) {
        $err = $db->error();
        if ($err[1] != null) {
            throw new Exception($err[2]);
        }
    }

    public function formatResponse($data) {
        return json_encode(['data' => $data],true);
    }

    public function formatError($code, $error) {
        return json_encode([
            'errors' => [
                [
                    'status' => (string)$code,
                    'detail' => $error,
                ]
            ]
        ]);
    }
}

$app->get('/requests[/{req}]', '\PurchaseRequests:get');
$app->post('/requests[/{req}]', '\PurchaseRequests:post');

$app->get('/whoami', function($request, $response) { $response->getBody()->write($this->user); });

$app->run();

?>

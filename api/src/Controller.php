<?php
namespace PurchaseReqs;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

class Controller {
    protected $ci;

    public function __construct($ci) {
        $this->ci = $ci;
        $this->config = $ci->config;
    }

    public function formatResponse($data, $response = null) {
        $body = json_encode($data);

        if ($response) {
            $response = $response->withHeader('Content-Type', 'application/json; charset=UTF-8');
            $response->getBody()->write($body);
            return $response;
        } else {
            return $body;
        }
    }

    public function formatError($code, $error, $response = null) {
        $body = json_encode([
            'errors' => [
                [
                    'status' => (string)$code,
                    'detail' => $error,
                ]
            ]
        ]);

        if ($response) {
            $response = $response->withStatus($code)->withHeader('Content-Type', 'application/json; charset=UTF-8');
            $response->getBody()->write($body);
            return $response;
        } else {
            return $body;
        }
    }
}


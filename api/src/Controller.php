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

    public function get($request, $response, $args) {
        $result = null;
        $id = null;
        if (isset($args["id"])) {
            $id = $args["id"];
        }

        try {

            if ($id) {
                $result = $this->model->byId($id);
            } else {
                $result = $this->model->all();
            }

            if ($id && (!$result || count($result) == 0)) {
                $response = $this->formatError(404, "Not found", $response);
            } else {
                $response = $this->formatResponse($result, $response);
            }
        } catch(\Exception $e) {
            $response = $this->formatError(400, $e->getMessage(), $response);
        }

        return $response;
    }

    public function post($request, $response, $args) {
        $object = json_decode($request->getBody()->getContents());
        $result = -1;

        $id = null;
        if (isset($args["id"])) {
            $id = $args["id"];
        }

        try {
            if ($id) {
                $this->model->update($id, $object);
            } else {
                $id = $this->model->add($object);
            }

            $result = $this->model->byId($id);
            if ($result && count($result) > 0) {
                $response = $this->formatResponse($result, $response);
            }
        } catch(\Exception $e) {
            $response = $this->formatError(400, $e->getMessage(), $response);
        }

        return $response;
    }

    public function delete($request, $response, $args) {
        $id = null;
        if (isset($args["id"])) {
            $id = $args["id"];
        } else {
            return $this->formatError(400, "Missing request id", $response);
        }

        try {
            $this->model->delete($id);
            $result = $this->formatResponse(['result' => true], $response);
        } catch(\Exception $e) {
            $response = $this->formatError(400, $e->getMessage(), $response);
        }

        return $response;
    }
}


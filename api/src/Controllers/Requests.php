<?php
namespace PurchaseReqs\Controllers;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

use \PurchaseReqs\Models\Requests as RequestModel;

class Requests extends \PurchaseReqs\Controller {
    public function __construct($ci) {
        parent::__construct($ci);

        $this->model = new RequestModel($ci);
    }

    public function get($request, $response, $args) {
        $result = null;
        $id = null;
        if (isset($args["req"])) {
            $id = $args["req"];
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
        $c = $request->getBody()->getContents();
        $r = json_decode($c);
        $result = -1;

        $id = null;
        if (isset($args["req"])) {
            $id = $args["req"];
        }

        try {
            if ($id) {
                $this->model->update($id, $r);
            } else {
                $id = $this->model->add($r);
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
        if (isset($args["req"])) {
            $id = $args["req"];
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

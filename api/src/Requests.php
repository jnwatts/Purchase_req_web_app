<?php
namespace PurchaseReqs;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

class Requests extends Controller {
    public function __construct($ci) {
        $this->tables = [
            'requests', 'request_items',
        ];
        parent::__construct($ci);
    }

    public function get($request, $response, $args) {
        $db = $this->db();
        $where = null;
        if (isset($args["req"])) {
            $where = ['requests.id' => $args["req"]];
        }

        try {
            // TODO: Explicitly select columns
            $reqs = $db->select(
                    'requests',
                    '*',
                    $where);
            $this->checkDbError($db);

            if (isset($args["req"]) && (!$reqs || count($reqs) == 0)) {
                return $this->formatError(404, "Not found", $response);
            }

            if ($reqs) {
                foreach ($reqs as &$r) {
                    // TODO: Explicitly select columns
                    $r['items'] = $db->select(
                        'request_items',
                        '*',
                        ['request_items.request_id' => $r['id']]
                        );
                    $this->checkDbError($db);
                }
            }
            $response = $this->formatResponse($reqs, $response);
        } catch(\Exception $e) {
            $response = $this->formatError(400, $e->getMessage(), $response);
            $success = false;
        }

        return $response;
    }

    public function post($request, $response, $args) {
        $c = $request->getBody()->getContents();
        $r = json_decode($c);
        $result = -1;

        $req = null;
        if (isset($args["req"])) {
            $req = $args["req"];
        }

        // Strip id field: Should be passed in req, or should be undefined for new instance
        if (isset($r->id)) {
            unset($r->id);
        }

        $this->db()->action(function ($db) use (&$r, &$request, &$response, &$req) {
            $success = true;
            try {

                // Remove items from request object
                $items = null;
                if (isset($r->items)) {
                    $items = $r->items;
                    unset($r->items);
                }

                /* TODO: Explicitly extract specific columns and sanitize(?) values */
                if ($req) {
                    $db->update('requests', (array)$r, ['id' => $req]);
                } else {
                    $req = $db->insert('requests', (array)$r);
                }
                $this->checkDbError($db);

                if ($items) {
                    $db->delete('request_items', ['request_id' => $req]);
                    $this->checkDbError($db);

                    foreach ($items as &$i) {
                        $i->request_id = $req;

                        /* TODO: Explicitly extract specific columns and sanitize(?) values */
                        $db->insert('request_items', (array)$i);
                        $this->checkDbError($db);
                    }
                }

                $response = $this->get($request, $response, ['req' => $req]);
            } catch(\Exception $e) {
                $response = $this->formatError(400, $e->getMessage(), $response);
                $success = false;
            }

            return $success;
        });

        return $response;
    }

    public function delete($request, $response, $args) {
        if (!isset($args["req"])) {
            return $this->formatError(400, "Missing request id", $response);
        }
        $req = $args["req"];
        $db = $this->db();
        $success = true;

        try {
            $db->delete('requests', ['id' => $req]);
            $this->checkDbError($db);
            $db->delete('request_items', ['request_id' => $req]);
            $this->checkDbError($db);
            $result = $this->formatResponse(['result' => $success], $result);
            $response->getBody()->write($result);
        } catch(Exception $e) {
            $response = $this->formatError(400, $e->getMessage(), $response);
            $success = false;
        }

        return $response;
    }
}

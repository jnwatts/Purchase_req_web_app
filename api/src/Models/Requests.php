<?php
namespace PurchaseReqs\Models;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

class Requests extends \PurchaseReqs\Model {
    public function __construct($ci) {
        $this->tables = [
            'requests', 'request_items', 'request_user_role',
        ];
        parent::__construct($ci);
    }

    public function get($where = null) {
        $db = $this->db();
        $reqs = $db->select(
                'requests',
                '*',
                $where);
        $this->checkDbError($db);

        if ($reqs) {
            foreach ($reqs as &$r) {
                // TODO: Explicitly select columns
                $r['items'] = $db->select(
                    'request_items',
                    '*',
                    ['request_items.request_id' => $r['id']]
                    );
                $this->checkDbError($db);
                foreach ($r['items'] as &$i) {
                    unset($i['request_id']);
                }
            }
        }

        return $reqs;
    }

    public function all() {
        return $this->get();
    }

    public function byId($id) {
        return $this->get(['id' => $id]);
    }

    public function add($req) {
        return $this->update(null, $req);
    }

    public function update($id, $req) {
        $error = null;
        $this->db()->action(function ($db) use (&$id, &$req, &$error) {
            $success = true;

            try {
                $items = null;
                if (isset($req->items)) {
                    $items = $req->items;
                    unset($req->items);
                }

                if ($id) {
                    $db->update('requests', (array)$req, ['id' => $id]);
                } else {
                    $id = $db->insert('requests', (array)$req);
                }
                $this->checkDbError($db);

                if (is_array($items)) {
                    $db->delete('request_items', ['request_id' => $id]);
                    $this->checkDbError($db);

                    foreach ($items as &$i) {
                        $i->request_id = $id;

                        /* TODO: Explicitly extract specific columns and sanitize(?) values */
                        $db->insert('request_items', (array)$i);
                        $this->checkDbError($db);
                    }
                }
            } catch(\Exception $e) {
                $error = $e;
                $success = false;
            }

            return $success;
        });

        if ($error) {
            throw $error;
        }

        return $id;
    }

    public function delete($id) {
        $error = null;
        $this->db()->action(function ($db) use (&$id, &$error) {
            $success = true;

            try {
                $db->delete('requests', ['id' => $id]);
                $this->checkDbError($db);
                $db->delete('request_items', ['request_id' => $id]);
                $this->checkDbError($db);
            } catch(Exception $e) {
                $error = $e;
                $success = false;
            }

            return $success;
        });

        if ($error) {
            throw $error;
        }
    }
}

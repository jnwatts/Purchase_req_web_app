<?php
namespace PurchaseReqs\Models;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

use \PurchaseReqs\Models\Roles as RolesModel;

class Users extends \PurchaseReqs\Model {
    private $roles = null;

    public function __construct($ci) {
        $this->tables = [
            'users',
        ];
        parent::__construct($ci);

        $this->roles = new RolesModel($ci);
    }

    protected function get($where = null) {
        $db = $this->db();
        $users = $db->select(
                'users',
                '*',
                $where);
        $this->checkDbError($db);

        if ($users) {
            foreach($users as &$user) {
                //TODO: Groups/roles
                if (array_key_exists('groups', $user))
                    unset($user['groups']);
                // $user['groups'] = json_decode($user['groups'], true);

                $user['roles'] = $this->roles->rolesFromUser($user['id']);
            }
        }

        return $users;
    }

    public function all() {
        return $this->get();
    }

    public function byId($id) {
        return $this->get(['id' => $name]);
    }

    public function exists($username) {
        $db = $this->db();
        $result = $db->select('users', 'id', ['users.username[~]' => $username]);
        return ($result && count($result) != 0);
    }

    public function add($user) {
        return $this->update(null, $user);
    }

    public function update($id, $user) {
        $error = null;
        $this->db()->action(function ($db) use (&$id, &$user, &$error) {
            $success = true;

            try {
                //TODO: Groups/roles
                if (array_key_exists('groups', $user))
                    unset($user['groups']);
                // $user['groups'] = json_encode($user['groups']);

                if ($id) {
                    $db->update('users', (array)$user, ['id' => $id]);
                } else {
                    $db->insert('users', (array)$user);
                }
                $this->checkDbError($db);
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
}

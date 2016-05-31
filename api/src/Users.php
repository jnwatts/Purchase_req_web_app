<?php
namespace PurchaseReqs;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

class Users extends Controller {
    private $override_auth_user = false;
    private $user_debug = [];

    public function __construct($ci) {
        $this->tables = [
            'users',
        ];
        parent::__construct($ci);

        if (isset($ci->config['user_debug'])) {
            $user_debug = $ci->config['user_debug'];
        } else {
            $user_debug = [];
        }
        if ($user_debug && isset($user_debug['override_auth_user'])) {
            $this->override_auth_user = $user_debug['override_auth_user'];
            $this->user_debug = $user_debug;
        }
    }

    public function get($request, $response, $args) {
        $db = $this->db();
        $where = null;
        if (isset($args["user"])) {
            $where = ['users.id' => $args["user"]];
        }

        try {
            // TODO: Explicitly select columns
            $users = $db->select(
                    'users',
                    '*',
                    $where);
            $this->checkDbError($db);

            if (isset($args["user"]) && (!$users || count($users) == 0)) {
                return $this->formatError(404, "Not found", $response);
            }

            foreach($users as &$user) {
                if (isset($user['groups']))
                    unset($user['groups']);
            }

            $result = $users;
            $response->getBody()->write(json_encode($result, true));
        } catch(\Exception $e) {
            $response = $this->formatError(400, $e->getMessage(), $response);
            $success = false;
        }

        return $response;
    }

    public function post($request, $response, $args) {
        
    }

    public function delete($request, $response, $args) {
    }

    public function initAuthUser($username) {
        if ($this->override_auth_user) {
            $username = $this->user_debug['user_fields']['username'];
        }
        if (empty($username)) {
            throw \Exception("Username is empty");
        }
        if (!$this->exists($username)) {
            $this->import($username);
        }
    }

    public function exists($username) {
        $db = $this->db();
        $result = $db->select('users', 'id', ['users.username[~]' => $username]);
        return ($result && count($result) != 0);
    }

    public function import($username) {
        $error = null;
        $ldap = new Ldap($this->ci);
        if ($this->override_auth_user) {
            $ldap_user = $this->user_debug['user_fields'];
        } else {
            $ldap_user = $ldap->getUser($username);
        }
        $this->db()->action(function ($db) use (&$ldap_user, &$error) {
            $success = true;

            try {
                //TODO: Groups/roles
                $ldap_user['groups'] = json_encode($ldap_user['groups'], true);
                $db->insert('users', $ldap_user);
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
    }
}

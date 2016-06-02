<?php
namespace PurchaseReqs\Controllers;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

use \PurchaseReqs\Ldap as Ldap;
use \PurchaseReqs\Models\Users as UserModel;

class Users extends \PurchaseReqs\Controller {
    private $usermodel;
    private $override_auth_user = false;
    private $user_debug = [];

    public function __construct($ci) {
        parent::__construct($ci);

        $this->model = new UserModel($ci);

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
        $result = null;
        $id = null;
        if (isset($args["user"])) {
            $id = $args["user"];
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
        if (!$this->model->exists($username)) {
            $this->import($username);
        }
    }

    public function import($username) {
        $error = null;
        $ldap = new Ldap($this->ci);
        if ($this->override_auth_user) {
            $ldap_user = $this->user_debug['user_fields'];
        } else {
            $ldap_user = $ldap->getUser($username);
        }
        $this->model->add($ldap_user);
    }
}

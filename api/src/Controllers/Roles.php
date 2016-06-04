<?php
namespace PurchaseReqs\Controllers;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

use \PurchaseReqs\Models\Roles as RolesModel;

class Roles extends \PurchaseReqs\Controller {
	    public function __construct($ci) {
        parent::__construct($ci);

        $this->model = new RolesModel($ci);
    }

    public function post($request, $response, $args) {
        return $this->formatError(401, "Not authorized", $response);
    }

    public function delete($request, $response, $args) {
        return $this->formatError(401, "Not authorized", $response);
    }
}
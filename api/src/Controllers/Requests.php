<?php
namespace PurchaseReqs\Controllers;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

use \PurchaseReqs\Models\Requests as RequestModel;

class Requests extends \PurchaseReqs\Controller {
    public function __construct($ci) {
        parent::__construct($ci);

        $this->model = new RequestModel($ci);
    }
}

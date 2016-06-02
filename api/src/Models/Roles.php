<?php
namespace PurchaseReqs\Models;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

class Roles extends Model {
	private $roles = [
		Roles::Role(1, "Admin");
	];

	static Role(id, description) {
		return [
			'id' => id,
			'description' => description,
		];
	}

	public function __construct($ci) {
		parent::__construct($ci);
	}
}


<?php
namespace PurchaseReqs\Models;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

class Roles extends \PurchaseReqs\Model {
	const ADMIN = 1;
	const PM = 2;
	const ACCT = 3;
	const MGMT = 4;

	private $roles = [];

	private static function Role($id, $description) {
		return [
			'id' => $id,
			'description' => $description,
		];
	}

	public function __construct($ci) {
        $this->tables = [
            'user_role',
        ];

		parent::__construct($ci);

		$this->roles = [
			Roles::Role(Roles::ADMIN, "Admin"),
			Roles::Role(Roles::PM, "Project Manager"),
			Roles::Role(Roles::ACCT, "Accounting"),
			Roles::Role(Roles::MGMT, "Management"),
		];
	}

	public function rolesFromUser($user_id) {
		$db = $this->db();
		$user_roles = $db->select('user_role', '*', ['user_role.user_id' => $user_id]);
		$this->checkDbError($db);

		$roles = [];

		foreach ($user_roles as $user_role) {
			$roles[] = $user_role['role_id'];
		}

		return $roles;
	}

	public function usersWithRole($role_id) {
		$user_roles = $db->select('user_role', '*', ['user_role.role_id' => $role_id]);
		$this->checkDbError($db);

		$users = [];

		foreach ($user_roles as $user_role) {
			$users[] = $user_role['user_id'];
		}

		return $users;
	}

	public function byId($role_id) {
		assert($role_id > 0);
		assert($role_id <= count($this->roles));
		return $this->roles[$role_id - 1];
	}

	public function all() {
		return $this->roles;
	}
}


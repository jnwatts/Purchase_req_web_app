<?php
namespace PurchaseReqs;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

class Model {
	protected $ci;
	protected $_db;
    protected $tables;

	public function __construct($ci) {
        $this->ci = $ci;
        $this->config = $ci->config;
        $this->initDb();
	}

    protected function db() {
        if (!$this->_db) {
            $this->_db = new \medoo($this->config['database']);
        }
        return $this->_db;
    }

    protected function initDb() {
        $db = $this->db();

        foreach ($this->tables as $t) {
            $db->select($t, '*');
            try {
                $this->checkDbError($db);
            } catch (\Exception $e) {
                $sql = $this->getTableSchema($t);
                $result = $db->query($sql)->fetchAll();
            }
        }

        unset($db);
        $this->_db = null;
    }

    protected function getTableSchema($table) {
        $path = BASE_PATH . '/schema/' . $table . '.' . $this->config['database']['database_type'] . '.sql';
        return file_get_contents($path);
    }

    public function checkDbError($db) {
        $err = $db->error();
        if ($err[1] != null) {
            throw new \Exception($err[2]." ".$db->last_query());
        }
    }
}
<?php

require 'config.php';

class DB
{

	private $db;
	
	function __construct() {
		if(!empty(dbconfig)) 
			$this->db = new PDO('mysql:host='.dbhost.';dbname='.dbname.';port=3306;charset=utf8', dbuser, dbpass);
		else 
			throw new Exception("Database config empty or undefined", 1);
	}

	public function getStudent($id) {
		$a = $this->db->query('SELECT * FROM `table 1` WHERE `IIN` LIKE \'' . $id . '\'', PDO::FETCH_ASSOC);
		// die(var_dump('SELECT * FROM `table 1` WHERE `IIN` LIKE \'' . $id . '\''));
		return $a->fetchAll();
	}
}
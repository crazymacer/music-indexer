<?php
	class DB{
		private $connection = null;
		
		public function __construct(){
			
			require('config.php');			
			
			$engine = $conf['database']['engine'];
			if($engine=='mysql') {			
				$host = $conf['database']['host'];
				$user = $conf['database']['user'];
				$password = $conf['database']['password'];
				$dbname = $conf['database']['dbname'];
				$this->connection = new PDO("{$engine}:host={$host};dbname={$dbname}", $user, $password);
			}
		}
	
		public function query_unsafe($input){
			return $this->connection->query($input);
		}
			
		public function insert($to, $input){
			$query = $this->connection->prepare('INSERT INTO `'.$to.
				'` ('.implode(", ", array_keys($input)).') VALUES (:'
				.implode(", :", array_keys($input)).')');
			$query->execute($input);
		}
		
		public function select($from, $fields = '*', $where = null){
			$query = $this->connection->prepare('SELECT '.$fields.' FROM '.$from.
			($where ? ' WHERE '.$where : '')
			);
			$query->execute();
			return $query -> fetchAll(PDO::FETCH_ASSOC);
		}
	}
?>
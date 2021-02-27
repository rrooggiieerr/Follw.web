<?php
class DataStore {
	private static $instance = NULL;
	var $pdo = NULL;

	private function __construct() {
		global $configuration;
		$dbConfiguration = $configuration['database'];

		$dsn = NULL;
		if(array_key_exists('dsn', $dbConfiguration)) {
			$dsn = $dbConfiguration['dsn'];
		} else {
			$dsn = $dbConfiguration['driver'] . ':host=' . $dbConfiguration['host'] . ';dbname=' . $dbConfiguration['dbname'];

			if(array_key_exists('port', $dbConfiguration)) {
				$dsn .= ';port=' . $dbConfiguration['port'];
			}

			if(array_key_exists('charset', $dbConfiguration)) {
				$dsn .= ';charset=' . $dbConfiguration['charset'];
			} else {
				$dsn .= ';charset=utf8';
			}
		}

		$this->pdo = new PDO($dsn, $dbConfiguration['username'], $dbConfiguration['password']);
		// Set the PDO error mode to exception
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);

		// Check if tables exist
		$query = 'SHOW TABLES';
		if($statement = $this->pdo->query($query)) {
			$tables = $statement->fetchAll(PDO::FETCH_COLUMN);
			$diff = array_diff(['issuedids', 'followers', 'locations'], $tables);
			if(count($diff) > 0) {
				// Create tables if one does not exist
				$this->create();
			}
		}
	}

	static function getInstance() {
		if (self::$instance == NULL) {
			self::$instance = new DataStore();
		}

		return self::$instance;
	}

	function create() {
		// Check if issuedids table exist
		$query = 'SHOW TABLES LIKE \'issuedids\'';
		$statement = $this->pdo->query($query);
		if($statement->rowCount() === 0) {
			// Create issuedids table if not exist
			$query = 'CREATE TABLE `issuedids` (
						  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `hash` binary(16) NOT NULL DEFAULT \'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\',
						  `type` enum(\'share\',\'follow\',\'deleted\',\'reserved\') NOT NULL DEFAULT \'deleted\',
						  `config` text CHARACTER SET utf8 NOT NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `hash` (`hash`)
						) ENGINE=InnoDB DEFAULT CHARSET=latin1';
			$statement = $this->pdo->prepare($query);
			$statement->execute();
		}

		// Check if followers table exist
		$query = 'SHOW TABLES LIKE \'followers\'';
		$statement = $this->pdo->query($query);
		if($statement->rowCount() === 0) {
			// Create followers table if not exist
			$query = 'CREATE TABLE `followers` (
						  `shareid` int(10) unsigned NOT NULL,
						  `followid` int(10) unsigned NOT NULL,
						  `followidencrypted` binary(24) NOT NULL DEFAULT \'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\',
						  `enabled` tinyint(1) NOT NULL DEFAULT \'0\',
						  `starts` timestamp NULL DEFAULT NULL,
						  `expires` timestamp NULL DEFAULT NULL,
						  `delay` time DEFAULT NULL,
						  UNIQUE KEY `followid` (`followid`),
						  KEY `shareid` (`shareid`),
						  CONSTRAINT `followers_ibfk_1` FOREIGN KEY (`shareid`) REFERENCES `issuedids` (`id`),
						  CONSTRAINT `followers_ibfk_2` FOREIGN KEY (`followid`) REFERENCES `issuedids` (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=latin1';
			$statement = $this->pdo->prepare($query);
			$statement->execute();
		}

		// Check if locations table exist
		$query = 'SHOW TABLES LIKE \'locations\'';
		$statement = $this->pdo->query($query);
		if($statement->rowCount() === 0) {
			// Create locations table if not exist
			$query = 'CREATE TABLE `locations` (
						  `id` int(10) unsigned NOT NULL,
						  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						  `location` text CHARACTER SET utf8 NOT NULL,
						  PRIMARY KEY (`id`),
						  CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`id`) REFERENCES `issuedids` (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=latin1';
			$statement = $this->pdo->prepare($query);
			$statement->execute();
		}
	}

	/**
	 * Initiates a transaction
	 * @return boolean true on success or false on failure
	 */
	function beginTransaction() {
		return $this->pdo->beginTransaction();
	}

	/**
	 * Rolls back a transaction
	 * @return boolean true on success or false on failure
	 */
	function rollback() {
		return $this->pdo->rollback();
	}

	/**
	 * Commits a transaction
	 * @return boolean true on success or false on failure
	 */
	function commit() {
		return $this->pdo->commit();
	}

	/**
	 * Prepares and executes a statement and returns a statement object, or false on failure
	 * @param string $statement This must be a valid SQL statement template for the target database server.
	 * @param array $input_parameters An array of values with as many elements as there are bound parameters in the SQL statement being executed.
	 * @return PDOStatement|false
	 */
	function execute(string $statement, array $input_parameters = NULL) {
		if($input_parameters) {
			$statement = $this->pdo->prepare($statement);
			if(!$statement->execute($input_parameters)) {
				return FALSE;
			}
			return $statement;
		}

		return $this->pdo->query($statement);
	}

	function lastInsertId() {
		return $this->pdo->lastInsertId();
	}
}
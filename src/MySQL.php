<?php

namespace NoEngine;

class MySQL {
    private $pdo;
    private $tb;
    private $schema;
    
    public function __construct(array $config) {
        $user  = (string) @$config["db_user"];
        $pass  = (string) @$config["db_pass"];
        $host  = (string) @$config["db_host"];
        $name  = (string) @$config["db_name"];

        $this->schema = (array) @$config["db_schema"];

        $this->pdo = self::connect($user,$pass,$host,$name);
    }
    final public function connect(string $user, string $pass, string $host, string $db) : object {
        $dsn = "mysql:host=$host;dbname=$db";
		$pdo = new \PDO($dsn, $user,$pass);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $pdo;
    }
    final public function setupSchema(array $schema = []): bool{
		// SCHEMA - { table: [column,...]}
        $schema = $schema ?? $this->schema;
		$query = "";

		foreach ($schema as $table => $columns) {

			$primary_key = false; // DEFAULT - Automatically set to true if id column is exists;
			$cols = ""; // Columns - translated into SQL query

			foreach ($columns as $column) {
				if ($column === "id") {
					// Primary key has a default max value of 11
					$primary_key = true;
					$cols .= "id int(11) AUTO_INCREMENT PRIMARY KEY,";
				} else {
					$cols .= "$column LONGTEXT NOT NULL,";
				}
			}

			$cols = rtrim($cols, ",");
			$query .= "CREATE TABLE IF NOT EXISTS $table($cols);";
		}

		($this->pdo)->exec($query);
		return TRUE;
	}
	final public function forceSetupSchema(array $schema = []): bool {
		// SCHEMA - { table: [column,...]}

        $schema = $schema ?? $this->schema;
		$query = "";

		foreach ($schema as $table => $columns) {

			$primary_key = false; // DEFAULT - Automatically set to true if id column is exists;
			$cols = ""; // Columns - translated into SQL query

			foreach ($columns as $column) {
				if ($column === "id") {
					// Primary key has a default max value of 11
					$primary_key = true;
					$cols .= "id int(11) AUTO_INCREMENT PRIMARY KEY,";
				} else {
					$cols .= "$column LONGTEXT NOT NULL,";
				}
			}

			$cols = rtrim($cols, ",");
			$query .= "DROP TABLE IF EXISTS $table;";
			$query .= "CREATE TABLE $table($cols);";
		}

		($this->pdo)->exec($query);
		return TRUE;
    }
    public function table(string $table): void{

		$this->tb = $table;
	}
	public function createData(array $data): bool{
		// DATA - {key: value}
		$tb = $this->tb;
		$keys = "";
		$pdo_values = "";
		$values = [];

		foreach ($data as $key => $value) {
			$keys .= $key . ",";
			$pdo_values .= "?,";
			$values[] = $value;
		}

		$keys = rtrim($keys, ",");
		$pdo_values = rtrim($pdo_values, ",");

		$query = "INSERT INTO $tb($keys)VALUE($pdo_values)";
		$q = ($this->pdo)->prepare($query);
		$q->execute($values);
		if ($q->rowCount() > 0) {
			return true;
		}
		return false;
	}
	public function createUniqueData(string $key, array $data): bool {
		if (isset($data[$key]) && count($this->readData([$key => $data[$key]])) > 0) {
			return FALSE;
		}

		return $this->createData($data);
	}
	public function readData(array $find, array $advance = []): array{

		// find - ["name" => "jericho"]
		$tb = $this->tb;

		$prep_bind = $order = $limit = $offset = "";
		$bind_data = [];

		foreach ($find as $key => $value) {
			$prep_bind .= "$key=?,";
			$bind_data[] = $value;
		}

		if (isset($advance["order"])) {
			$order = " ORDER BY $advance[order]";
		}
		// Advance - ["order" => "id ASC|DESC"]
		if (isset($advance["limit"])) {
			$limit = " LIMIT $advance[limit]";
		}
		// Advance - ["limit" => 3]
		if (isset($advance["offset"])) {
			$offset = " OFFSET $advance[offset]";
		}
		// Advance - ["offset" => 1]

		$bind = (strlen($prep_bind) > 0) ? " WHERE " . rtrim($prep_bind, ",") : "";
		$query = "SELECT * FROM $tb$bind$order$limit$offset";

		$q = ($this->pdo)->prepare($query);
		$q->execute($bind_data);

		return $q->fetchAll() ?? [];
	}
	public function readAllData(array $advance = []): array{
		$tb = $this->tb;

		$order = $limit = $offset = "";

		if (isset($advance["order"])) {
			$order = " ORDER BY $advance[order]";
		}
		// Advance - ["order" => "id ASC|DESC"]
		if (isset($advance["limit"])) {
			$limit = " LIMIT $advance[limit]";
		}
		// Advance - ["limit" => 3]
		if (isset($advance["offset"])) {
			$offset = " OFFSET $advance[offset]";
		}
		// Advance - ["offset" => 1]

		$query = "SELECT * FROM $tb$order$limit$offset";

		$q = ($this->pdo)->prepare($query);
		$q->execute();

		return $q->fetchAll() ?? [];
	}
	public function updateData(array $find, array $data): bool{
		// Find - [key => value]
		// Data = [key => value]
		$tb = $this->tb;

		$prep_data = $prep_find = "";
		$new_data_find = [];

		foreach ($data as $data_key => $data_val) {
			$prep_data .= "$data_key=?,";
			$new_data_find[] = $data_val;
		}

		foreach ($find as $find_key => $find_val) {
			$prep_find .= "$find_key=?,";
			$new_data_find[] = $find_val;
		}

		$prep_data = rtrim($prep_data, ",");
		$prep_find = rtrim($prep_find, ",");

		$query = "UPDATE $tb SET $prep_data WHERE $prep_find";

		$q = ($this->pdo)->prepare($query);
		$q->execute($new_data_find);

		if ($q->rowCount() > 0) {
			return TRUE;
		}

		return FALSE;
	}
	public function deleteData(array $find): bool{
		// Find - [key => value]

		$tb = $this->tb;

		$prep_find = "";
		$new_find = [];

		foreach ($find as $key => $value) {
			$prep_find .= "$key=?,";
			$new_find[] = $value;
		}

		$prep_find = rtrim($prep_find, ",");

		$query = "DELETE FROM $tb WHERE $prep_find";
		$q = ($this->pdo)->prepare($query);
		$q->execute($new_find);

		if ($q->rowCount() > 0) {
			return TRUE;
		}

		return FALSE;
	}
}
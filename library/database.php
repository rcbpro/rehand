<?php

class database {

	protected $_mysql;
	protected $_where = array();
	protected $_query;
	protected $_paramTypeList;
	static public $instance = NULL;
	static private $_db_params = array();
	private $numofRows = 0;
	
	public function __construct() {       
	
		try{
			$keys = array_keys(self::$_db_params);          
			$this->_mysql = new mysqli(self::$_db_params[$keys[0]], self::$_db_params[$keys[1]], self::$_db_params[$keys[2]], self::$_db_params[$keys[3]]);
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
	
	/**
	* @return the instance of database class
	*/
	public static function getInstance() {
	
		self::$_db_params = self::get_database_settings();	   
		if (self::$instance == NULL) self::$instance = new self;
		return self::$instance;		   
	}
	
	/**
	* @return array of database settings
	*/
	function get_database_settings() {
	
		$db_settings = new _define();
		return $db_settings->db_param;
	}
	
	/**
	*
	* @param string $query Contains a user-provided select query.
	* @param int $numRows The number of rows total to return.
	* @return array Contains the returned rows from the query.
	*/
	public function query($query) {

		$this->numofRows = 0;
		//$this->_query = filter_var($query, FILTER_SANITIZE_STRING);
		$this->_query = $query;
		$stmt = $this->_prepareQuery();
		$stmt->execute();
		$stmt->store_result();
		$words = explode(" ", $query);
		if (in_array("SELECT",$words)){
			$this->numofRows = $stmt->num_rows;
			$results = $this->_dynamicBindResults($stmt);
		}else{
			if ($stmt->affected_rows) $results= true;
			else $results = false;
		}
		return $results;
	}
	
	/**
	*
	* @param string $tableName The name of the database table to work with.
	* @param int $numRows The number of rows total to return.
	* @return array Contains the returned rows from the select query.
	*/
	public function get($tableName, $fields = NULL, $numRows = NULL, $limitStart = NULL, $orderby = NULL, $like = NULL) {
		
		$this->numofRows = 0;
		if ($fields != NULL){
			for($i = 0; $i < count($fields); $i++)
				$fieldsList .= ($i != count($fields)-1) ? ($fields[$i] . ' ,') : ($fields[$i]);
		}
		if ($tableName != NULL){
			if (is_array($tableName)){			
				for($i = 0; $i < count($tableName); $i++)
					$tables .= ($i != count($tableName)-1) ? ($tableName[$i] . ' ,') : ($tableName[$i]);
			}else{
				$tables = $tableName;
			}
		}
		
		$this->_query = "SELECT {$fieldsList} FROM $tables";
			
		$stmt = $this->_buildQuery($numRows, $limitStart, false, $like, $orderby);
		$stmt->execute();
		$stmt->store_result();
		$this->numofRows = $stmt->num_rows;
		$results = $this->_dynamicBindResults($stmt);
		return $results;
	}
	
	/**
	*
	* @param <string $tableName The name of the table.
	* @param array $insertData Data containing information for inserting into the DB.
	* @return boolean Boolean indicating whether the insert query was completed succesfully.
	*/
	public function insert($tableName, $insertData) {
	
		$this->_query = "INSERT into $tableName";
		$stmt = $this->_buildQuery(NULL, NULL, $insertData);
		$stmt->execute();		
		if ($stmt->affected_rows) return true;
	}
	
	/**
	* Update query. Be sure to first call the "where" method.
	*
	* @param string $tableName The name of the database table to work with.
	* @param array $tableData Array of data to update the desired row.
	* @return boolean
	*/
	public function update($tableName, $tableData) {
	
		$this->_query = "UPDATE $tableName SET ";
		$stmt = $this->_buildQuery(NULL, NULL, $tableData);
		$stmt->execute();		
		if ($stmt->affected_rows) return true;
	}
	
	/**
	* Delete query. Call the "where" method first.
	*
	* @param string $tableName The name of the database table to work with.
	* @return boolean Indicates success. 0 or 1.
	*/
	public function delete($tableName) {
	
		$this->_query = "DELETE FROM $tableName";
		$stmt = $this->_buildQuery();
		$stmt->execute();		
		if ($stmt->affected_rows) return true;
	}
	
	/**
	* This method allows you to specify a WHERE statement for SQL queries.
	*
	* @param string $whereProp A string for the name of the database field to update
	* @param mixed $whereValue The value for the field.
	*/
	public function where($whereProp, $whereValue) { $this->_where[$whereProp] = $whereValue; }
	
	/**
	* This methos will reset the where parameters as necessary
	* @param string $whereProp
	*/
	public function resetWhere() { unset($this->_where); }
	
	/**
	* This method is needed for prepared statements. They require
	* the data type of the field to be bound with "i" s", etc.
	* This function takes the input, determines what type it is,
	* and then updates the param_type.
	*
	* @param mixed $item Input to determine the type.
	* @return string The joined parameter types.
	*/
	protected function _determineType($item) {
	
		switch (gettype($item)) {
			case 'string': $typeExt = 's'; break;			
			case 'integer': $typeExt = 'i'; break;
			case 'blob': $typeExt = 'b'; break;			
			case 'double': $typeExt = 'd'; break;
		}
		return $typeExt;
	}
	
	/**
	* Abstraction method that will compile the WHERE statement,
	* any passed update data, and the desired rows.
	* It then builds the SQL query.
	*
	* @param int $numRows The number of rows total to return.
	* @param array $tableData Should contain an array of data for updating the database.
	* @return object Returns the $stmt object.
	*/
	protected function _buildQuery($numRows = NULL, $limitStart = NULL, $tableData = false , $like = NULL, $orderby = NULL) {
	
		$hasTableData = NULL;
		if (gettype($tableData) === 'array')
			$hasTableData = true;

		// Did the user call the "where" method?
		if (!empty($this->_where)) {
			$keys = array_keys($this->_where);         
			$where_prop = $keys[0];         
			$where_value = $this->_where[$where_prop];
		
			// if update data was passed, filter through
			// and create the SQL query, accordingly.
			if ($hasTableData) {
				$i = 1;
				$pos = strpos($this->_query, 'UPDATE');
				if ($pos !== false) {
					foreach ($tableData as $prop => $value) {
						// determines what data type the item is, for binding purposes.
						$this->_paramTypeList .= $this->_determineType($value);
		
						// prepares the reset of the SQL query.
						if ($i === count($tableData)){
							//$this->_query .= $prop . " = ? WHERE " . $where_prop . " = '" . $where_value . "'";							
							$this->_query .= $prop . " = '{$value}' WHERE " . $where_prop . " = '" . $where_value . "'";
							// There was a problem with the update query
						} else {
							$this->_query .= $prop . " = '{$value}', ";
						}
						$i++;
					}
				}
			} else {
				if ((is_array($this->_where)) && (count($this->_where) > 1)){
					$this->_query .= " WHERE ";
					$i = 0;
					foreach($this->_where as $eachKey => $eachVal){
						$this->_paramTypeList = $this->_determineType($where_value);
						$this->_query .= ($i < count($this->_where)-1) ? ($eachKey . " = '" . $eachVal . "' AND ") : ($eachKey . " = '" . $eachVal . "'");
						$i++;
					}	
				}else{
					// no table data was passed. Might be SELECT statement.
					$this->_paramTypeList = $this->_determineType($where_value);
					$this->_query .= " WHERE " . $where_prop . " =  '{$where_value}' ";
				}
			}
			
		}
		
		// Determine if is INSERT query
		if ($hasTableData) {
			$pos = strpos($this->_query, 'INSERT');
			if ($pos !== false) {
				//is insert statement
				$keys = array_keys($tableData);
				$values = array_values($tableData);
				$num = count($keys);
				// wrap values in quotes
				$i = 0;
				foreach ($values as $key => $val) {
					$values[$key] = "'{$val}'";
					if ($i < count($values)-1){
						$this->_paramTypeList .= $this->_determineType($val);
					}	
					$i++;
				}
				$this->_query .= '(' . implode($keys, ', ') . ')';
				$this->_query .= ' VALUES(';
				while ($num !== 0) {
					($num !== 1) ? $this->_query .= '?, ' : $this->_query .= '?)';
					$num--;
				}	
			}
		}

		// Did the user set a like
		if ((isset($like)) && ($like != "") && ($like != NULL) && (is_array($like))){
			$likeStatement = $like['searchK'] . " LIKE '%" . $like['searchQ'] . "%'";
			if (!isset($this->_where)){
				$this->_query .= " WHERE " . $likeStatement;
			}else{
				$this->_query .= $likeStatement;
			}
		}
		
		// Did the user set a orderby
		if (isset($orderby)) {
			$this->_query .= " ORDER BY " . $orderby;
		}
		
		// Did the user set a limit
		if ((isset($numRows)) && (isset($limitStart))) {
			$this->_query .= " LIMIT " . (int) $limitStart . ' ,' . (int) $numRows;
		}
		
		//echo $this->_query;
		// Prepare query
		$stmt = $this->_prepareQuery();
		
		// Bind parameters
		if ($hasTableData) {
			$args = array();
			$args[] = $this->_paramTypeList;
			foreach ($tableData as $prop => $val) $args[] = &$tableData[$prop];
			call_user_func_array(array($stmt, 'bind_param'), $args);
		} else {
			if ($this->_where){
				$stmt->bind_param($this->_paramTypeList, $where_value);
			}
		}
		return $stmt;
	}
	
	/**
	* This helper method takes care of prepared statements' "bind_result method
	* , when the number of variables to pass is unknown.
	*
	* @param object $stmt Equal to the prepared statement object.
	* @return array The results of the SQL fetch.
	*/
	protected function _dynamicBindResults($stmt) {
	
		$parameters = array();
		$results = array();
		
		$meta = $stmt->result_metadata();
		
		while ($field = $meta->fetch_field()) $parameters[] = &$row[$field->name];
		
		call_user_func_array(array($stmt, 'bind_result'), $parameters);
		
		while ($stmt->fetch()) {
			$x = array();
			foreach ($row as $key => $val) $x[$key] = $val;
			$results[] = $x;
		}
		return $results;
	}
	
	/**
	* Method attempts to prepare the SQL query
	* and throws an error if there was a problem.
	*/
	protected function _prepareQuery() {       
	
		if (!$stmt = $this->_mysql->prepare($this->_query)) trigger_error("Problem preparing query", E_USER_ERROR);
		return $stmt;
	}

	/*return number of rows*/
	public function num_rows() { return $this->numofRows; }
	
	public function _destruct() { $this->_mysql->close(); }
	
	public function getLastInisertedId(){ return $this->_mysql->insert_id; }
}
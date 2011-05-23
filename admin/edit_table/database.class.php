<?
if (!defined("_DB_CLASS")) {
	define('_DB_CLASS', 1);
	define('_MAX_RESULTS', 1000);

	/**
	*
	* Example:
	*
		require ("path/to/database.class.php");	
		
		// no need to create an object
		
		// execute query
		$result = database::query ("select * from users");
		--
		// insert data into a table - Useful when receiving POST variables:
		/*
		* EXAMPLE:
		*	-----------------------------------------------------
		*	if (isset($_POST[submit_user]))
		*		database::insert("users", $_POST);
		*		
		*	?>
		*	<form action="" method="post">
		*	name : <input type="text" name="username" /><br>
		*	password : <input type="password" name="password" /><br>
		*	<input type="submit" name="submit_user">
		*	</form>
		*	-------------------------------------------------------
		*
		$arr = array(
			"username" => "grisha",
			"password" => "chris",
			"superUser" => "1"
			);
		database::insert ("users", $arr);
		--
		database::update ("uses", $arr, array('id'=>4));
		
	*/

	// This class holds a useful functions for dataabase management
	class database {

		// the last SQL
		static $last_executed_sql;

		// the array that holds all the info from the sql
		static $arr_list;
		
		// the log file for all the operations made by this class
		static $log_file = "database.class.log";
		static $logging_enabled = true;
		static $log_file_max_size = 4000000; // 4Mb limit //
		
		/**
		*	The function places all the fields of a table into an array 
		*/
		function get_fields_names($table_name)
		{
			if (!$table_name) return null;
			$sql = "describe `$table_name`";
			$res = @mysql_query($sql) or die (mysql_error() . "<br>sql : $sql");
			$names_array = array();
			while ($row = mysql_fetch_assoc($res))
				$names_array[] = $row["Field"];
			
			return $names_array;
		}

		/**
		* The function writes a log into the file
		*
		* parameter $string - the string that is written into the file.
		*/
		function log($string)
		{
			if (!database::$logging_enabled) 
				return;
				
			$i=0;
			$dir_prefix = dirname(__FILE__) . DIRECTORY_SEPARATOR;
			$filename = $dir_prefix . database::$log_file;
			while (file_exists($filename) && filesize($filename) > database::$log_file_max_size) {
				$filename = $dir_prefix . str_replace(".log", (++$i).".log", database::$log_file);
			}
			
			if (!$f = @fopen ($filename, "a")) 
				return false;
			$string = str_replace('\n', ' ', $string);
			$string = str_replace('\r', ' ', $string);
			$string = str_replace('\t', ' ', $string);
			fwrite($f, date("d/m/Y H:i:s") . "\t\t" . $string . "\r\n");
			fclose($f);
		}
		
		/**
		*	The function inserts data to a table according to the indexes names:
		*	e.g.
		*		database::insert ("users", array (
		*			"username" => "foo",
		*			"password" => "bar",
		*			"su" => "1"
		*			));
		*	This function will be usefull for inserting a POST variable into the database
		*	
		*	note: The database MUST be connected
		*/
		function insert ($table_name, $array)
		{
			if (!$array) { echo "error : no array!"; return false;}
			
			// get the list of fields (names)
			$fields_array = database::get_fields_names($table_name);
			
			$fields = array();
			$values = array();
			
			foreach ($array as $key=>$val)
				if (in_array($key, $fields_array))
				{
					$fields[] = '`'.$key.'`';
					$values[] = "'" . mysql_real_escape_string($val) . "'";
				}
				
			$fields_list = implode(",", $fields);
			$values_list = implode(",", $values);
			$sql = "insert into `$table_name` ($fields_list) values ($values_list)";
			database::$last_executed_sql = $sql;
			database::$arr_list = array();
			
			database::query($sql);
			return true;
				
		}	

		/**
		*	the function executes a query and returns a result
		*/
		function query ($sql)
		{
			database::$arr_list = array();
			if (defined("DEBUG")) echo "<b>debug sql</b> : <div style='margin:5px; border: 1px solid black;background-color: #DDDDDD;'><pre>$sql</pre></div>";
			// check
			if (!($sql))
			{ 
				return false; 
			}
			
			// execute
			if (!$temp_res = @mysql_unbuffered_query($sql))
			{
				database::log("*FAIL* ".$sql);
				if (defined("DEBUG")) 
				{
					echo "<span style='color: red'>Failed</span><br>";
					echo "sql : " . $sql . "<br>" . mysql_error();
				}
				
				die (mysql_error());
				return false;
			} else {
				database::log("*SUCC* ".$sql);
			}

			database::$last_executed_sql = $sql;
			database::$arr_list = array();
			
			// move all the results to local (static) array.
			$i = 0;
			while ($row = @mysql_fetch_assoc($temp_res))
			{
				// check for max results
				if (++$i > _MAX_RESULTS) 
					break;
					
				$row_array = array();
				foreach ($row as $idx=>$val)
					$row_array[$idx] = $val;
				database::$arr_list[] = $row_array;
			}
			
			if (defined("DEBUG") && !empty(database::$arr_list)) 
				echo "<div style='margin:5px; border:1px solid red; background:#DDD;'><b>Total records</b>: ".count(database::$arr_list)."</div>";
			
			return true;
		}
		
		/**
		*	function updates the record with condition written in $where
		*
		*	*$where - string WITHOUT "WHERE" string.
		*	Ex: database::update ("users", array ('password' => '123456'), array('id'=>5));
		*/
		function update ($table_name, $arr, $where)
		{
			if (!$arr || !$where || !$table_name) return false;
			
			// init the sql string
			$table_name = mysql_real_escape_string($table_name);
			$sql = "update `$table_name` set ";
			
			// gather "SET" sql section variables
			$set_values = array();
			foreach ($arr as $index=>$val)
			{
				$val 	= mysql_real_escape_string($val);
				$index 	= mysql_real_escape_string($index);
				$set_values[] = "`{$index}` = '{$val}'";
			}
			
			// complete sql
			$sql .= implode (",", $set_values);
			
			// build the 'where' clause
			$where_conds = array();
			foreach($where as $idx=>$val) {
				$val 	= mysql_real_escape_string($val);
				$index 	= mysql_real_escape_string($index);
				$where_conds[] = "`{$idx}`='{$val}'";
			}
			
			if (!empty($where_conds)) {
				$sql .= ' WHERE ' . implode(' AND ', $where_conds);
			}

			database::$last_executed_sql = $sql;
			database::$arr_list = array(mysql_affected_rows());
			return database::query($sql);
		}
	} // class
} // define
?>
<?php
namespace Also;

class Model {
    public $_table = '';
    public $hash = PASSWORD_DEFAULT;
    public $q = '';
    public $where = '';

	function __construct($con) {
		if(gettype($con) == 'string') { // if sqlite
        // Set sqlite settings in php.ini
            $phpPath = str_replace('php.exe', '', PHP_BINARY).'/ext';
            ini_set('extension','pdo_sqlite');
            ini_set('extension','sqlite3');
            ini_set('sqlite3.extension_dir',$phpPath);

            if (!file_exists(dirname($con))) mkdir(dirname($con), 0777, true);
			$this->con = new \Sqlite3($con);
		} else if(gettype($con) == 'array') { // if mysql
			$this->con = call_user_func_array('mysqli_connect',$con);
			if(mysqli_connect_errno()) echo 'something wrong';
		}
	}

    public function run() {
        $this->q .= $this->where;
        return $this->query($this->q);
    }

	public function query($q) {
        error_reporting(E_ERROR);
        preg_match_all('/\;/',$q,$matches);
		if(get_class($this->con) == 'SQLite3') {
            if(count($matches[0]) > 1) $result = $this->con->exec($q);
            else $result = $this->con->query($q);
            return $this->getResult($result,$q,'sqlite');
		} else if(get_class($this->con) == 'mysqli') {
            if(count($matches[0]) > 1) $result = mysqli_multi_query($this->con,$q);
			else $result = mysqli_query($this->con,$q);
            return $this->getResult($result,$q,'mysql');
		}
	}

	public function table($tableName) {
		$this->_table = $tableName;
        return $this;
	}

	public function createTable($tableName,$fields) {
        $fieldString = '';
		foreach ($fields as $key => $field) {
			if(get_class($this->con) == 'mysqli') {
				if(strpos('INTEGER DEFAULT',$field) !== null) $field = str_replace('INTEGER DEFAULT','INTEGER NOT NULL',$field);
			}
			$fieldString .= $key.' '.$field.',';
		}
		$fieldString = substr($fieldString,0,-1);
		$q = "CREATE TABLE $tableName ($fieldString)";
		return $this->query($q);
    }

	public function dropTable($tableName) {
        $q = "DROP TABLE ${tableName};";
        return $this->query($q);
    }

    public function create($array) {
        if($this->_table == '') return 'Please add tableName';
        $q = '';
        $keys = '(';
        $values = '(';
        foreach ($array as $key => $value) {
            $keys .= $key.',';
            $value = $this->prepareValue($value,$key).',';
            $values .= $value;
        }
        $values = substr_replace($values ,"", -1);
        $keys = substr_replace($keys ,"", -1);
        $keys .= ')';
        $values .= ')';
        $q .= "INSERT INTO ".$this->_table." ".$keys." VALUES ".$values.';';
        $result = $this->query($q);

        if(gettype($result) !== 'array') {
            return $this->where('id',$result)->get();
        } else return $result;
    }

    public function createMany($arrays) {
        $result = [];
        foreach ($arrays as $key => $data) {
            $result[] = $this->create($data);
        }
        return $result;
    }

    public function set($sets) {
        if($this->_table == '') return 'Please add tableName';
        $this->q = 'UPDATE '.$this->_table.' SET ';
        foreach ($sets as $key => $value) {
            $value = $this->prepareValue($value,$key);
            $this->q .= " $key = $value,";
        }
        $this->q = substr_replace($this->q ,"", -1);
        return $this->run();
    }

    public function where($key,$value,$glue = '=') {
        $value = $this->prepareValue($value,$key);
        $this->where .= " WHERE $key $glue $value ";
        return $this;
    }

    public function all() {
        if($this->_table == '') return 'Please add tableName';
		$this->q = "SELECT * FROM ".$this->_table;
		return $this->run();
    }

    public function first() {
        $this->limit(1);
        $this->get();
    }

    public function get() {
        if($this->_table == '') return 'Please add tableName';
        $this->q = 'SELECT * FROM '.$this->_table;
        return $this->run();
    }

    public function id($id) {
        $this->where = " WHERE id = $id ";
        return $this;
    }

    public function and($key,$value,$glue = '='){
        $value = $this->prepareValue($value,$key);
        $this->where .= " AND $key $glue $value ";
        return $this;
    }

    public function or($key,$value,$glue = '='){
        $value = $this->prepareValue($value,$key);
        $this->where .= " OR $key $glue $value ";
        return $this;
    }

    public function not($key,$value,$glue = '='){
        $value = $this->prepareValue($value,$key);
        $this->where .= " WHERE NOT $key $glue $value ";
        return $this;
    }

    public function delete($id = '') {
        if($this->_table == '') return 'Please add tableName';
        if($id !== '') $id = " WHERE id= $id ";
        $this->q = 'DELETE FROM '.$this->_table.$id;
        return $this->run();
    }

    public function asc() {
        $this->where .= " ASC ";
        return $this;
    }

    public function desc() {
        $this->where .= " DESC ";
        return $this;
    }

    public function orderBy($order) {
        $this->where .= " ORDER BY ". $order .' ';
        return $this;
    }

    public function limit($amount) {
        $this->where .= " LIMIT ".$amount.' ';
        return $this;
    }

    private function getResult($result,$q,$db) {
        if($db == 'sqlite') {
            $error = $this->con->lastErrorMsg();
            if($error == 'not an error') {
                if(strpos($q,'SELECT') !== false) $result = $this->fetchSqlite($result);
                else if(strpos($q,'DELETE') !== false || strpos($q,'SET') !== false) $result = $this->con->changes();
                else if(strpos($q,'INSERT') !== false ) $result = $this->con->lastInsertRowID();
                else $result = true;
            } else $result = ['sql' => $q,'error' => $error];
        } else if($db = 'mysql') {
            $error = mysqli_error($this->con);
            if($error == '') {
                if(strpos($q,'SELECT') !== false) $result = $this->fetchMysql($result);
                else if(strpos($q,'DELETE') !== false || strpos($q,'SET') !== false) $result = mysqli_affected_rows($this->con);
                else if(strpos($q,'INSERT') !== false ) $result = $this->con->insert_id;
                else $result = true;
            } else $result = ['sql' => $q,'error' => $error, 'warnings' => $this->warnings()];
        }

        $this->q = '';
        $this->where = '';
        return $result;
    }

    private function fetchSqlite($result) {
        $array = [];
        if($result->numColumns()) {
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $array[] = $row;
            }
            if(count($array[0]) == 1) return $array[0][0];
            else return $array[0];
        } else return $result;
    }

    private function fetchMysql($result) {
        $array = [];
        if($result !== 1 && $result->num_rows !== 0) {
            while($row = $result->fetch_assoc()) {
                $array[] = $row;
            }
        }
        if(count($array) == 1) return $array[0];
        else return $array;
    }

    private function warnings() {
        $array = [];
        $j = mysqli_warning_count($this->con);
        if ($j > 0) {
            $e = mysqli_get_warnings($this->con);
            for ($i = 0; $i < $j; $i++) {
                $array[] = $e;
                $e->next();
            }
        }
        return $array;
    }

    private function prepareValue($value,$key) {
        if($key == 'password') $value = password_hash($value, $this->hash); 
        if(gettype($value) == 'string') {
            $value = str_replace("'",'',$value);
            $value = str_replace("`",'',$value);
            $value = str_replace('"','',$value);
            $value = "'".$value."'";
        }
        return $value;
    }

}

?>
<?php
namespace Also;

class Model {
    public $table = '';
    public $hash = PASSWORD_DEFAULT;

	function __construct($con) {
		if(gettype($con) == 'string') { // if sqlite
			$this->con = new \Sqlite3($con);
		} else if(gettype($con) == 'array') { // if mysql
			$this->con = call_user_func_array('mysqli_connect',$con);
			if(mysqli_connect_errno()) echo 'something wrong';
		}
	}

	public function model($tableName) {
		$this->table = $tableName;
        return $this;
	}
	
	public function query($q) {
        error_reporting(E_ERROR);
        preg_match_all('/\;/',$q,$matches);
		if(get_class($this->con) == 'SQLite3') {
            if(count($matches[0]) > 1) $result = $this->con->exec($q);
            else $result = $this->con->query($q);
            $error = $this->con->lastErrorMsg();
            return [
                'result'=>$result,
                'error'=>$error,
                'sql'=>$q,
                'changes'=>$this->con->changes()
            ];
		} else if(get_class($this->con) == 'mysqli') {
            if(count($matches[0]) > 1) $result = mysqli_multi_query($this->con,$q);
			else $result = mysqli_query($this->con,$q);
            $error = mysqli_error($this->con);
            return [
                'result'=>$result,
                'error'=>$error,
                'sql'=>$q,
                'warnings'=>$this->warnings($this->con),
                'changes' => mysqli_affected_rows($this->con)
            ];
		}
	}

    private function warnings($db) {
        $array = [];
        $j = mysqli_warning_count($db);
        if ($j > 0) {
            $e = mysqli_get_warnings($db);
            for ($i = 0; $i < $j; $i++) {
                $array[] = $e;
                $e->next();
            }
        }
        return $array;
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

    public function insert($arrays,$table = '') {
        if($table == '') $table = $this->table;
        if($this->table == '') return 'Please add tableName';
        $q = '';
        foreach ($arrays as $array) {
            $keys = '(';
            $values = '(';
            foreach ($array as $key => $value) {
                $keys .= $key.',';
                $value = $this->prepareValue($value,$key);
                $values .= $value;
            }
            $values = substr_replace($values ,"", -1);
            $keys = substr_replace($keys ,"", -1);
            $keys .= ')';
            $values .= ')';
            $q .= "INSERT INTO ".$table." ".$keys." VALUES ".$values.';';
        }
        return $this->query($q);
    }

    public function update($sets,$wheres) {
        if($this->table == '') return 'Please add tableName';
        $set = ' SET ';
        foreach ($sets as $key => $value) {
            $value = $this->prepareValue($value,$key);
            $set .= " $key = $value";
        }
        $set = substr_replace($set ,"", -1);
        $q = 'UPDATE '.$this->table.$set.$this->_where($wheres).';';
        return $this->query($q);
    }

    private function prepareValue($value,$key) {
        $values = '';
        if($key == 'password') $value = password_hash($value, $this->hash); 
        $value = str_replace("'",'',$value);
        $value = str_replace("`",'',$value);
        $value = str_replace('"','',$value);
        $value = "'".$value."',";
        return $value;
    }

    public function all($options =[]) {
        if($this->table == '') return 'Please add tableName';
		$q = "SELECT * FROM ".$this->table." ".$this->options($options).';';
		$result = $this->query($q);
        return $this->fetch($result,$q);
    }

    public function where($wheres,$options =[]) {
        if($this->table == '') return 'Please add tableName';
        $q = 'SELECT * FROM '
        .$this->table.$this->_where($wheres)
        .$this->options($options).';';
        $result = $this->query($q);
        return $this->fetch($result,$q);
    }

    public function delete($wheres) {
        if($this->table == '') return 'Please add tableName';
        $q = 'DELETE FROM '.$this->table.' '.$this->_where($wheres).';';
        return $this->query($q);
    }

    private function options($options) {
        $q = '';
        if(isset($options['orderby'])) {
            $q .= " ORDER BY ". $options['orderby'].' ';
        }
        if(isset($options['order'])) {
            $q .= ' '.$options['order'].' ';
        }
        if(isset($options['limit']) && is_numeric($options['limit'])) {
            $q .= " LIMIT ".$options['limit'].' ';
        }
        return $q;
    }

    private function _where($wheres) {
        $q = ' WHERE ';
        foreach ($wheres as $key => $where) {
            if($key < count($wheres) && $key > 0) {
                if(strpos($where,'||') !== false) {
                    $where = str_replace('||',' OR ',$where);
                } else $where = ' AND '.$where;
            }
            $q .= $where;
        }
        return $q;
    }

    private function fetch($result,$q) {
        if(gettype($result['result']) !== 'boolean' || $result['result']) {
            $array = [];
            if(get_class($this->con) == 'SQLite3') {
                while ($row = $result['result']->fetchArray(SQLITE3_ASSOC)) {
                    $array[] = $row;
                }
            } else if(get_class($this->con) == 'mysqli') {
                while($row = $result['result']->fetch_assoc()) {
                    $array[] = $row;
                }
            }
            $result = ['result' => $array,'error' => $result['error'],'sql'=>$q];
        } 
        return $result;
    }


}


?>
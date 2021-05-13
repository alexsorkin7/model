<?php
namespace Also;

class Model {
	function __construct($con) {
		if(gettype($con) == 'string') { // if sqlite
			$this->con = new Sqlite3($con);
		} else if(gettype($con) == 'array') { // if mysql
			$this->con = call_user_func_array('mysqli_connect',$con);
			if(mysqli_connect_errno()) echo 'something wrong';
		}
	}

	public function model($tableName) {
		$this->table = $tableName;
	}
	
	public function query($q) {
		if(get_class($this->con) == 'SQLite3') {
		try{
			$this->con->enableExceptions(true);
			return ['result'=>$this->con->query($q),'error' => null,'sql' => $q];
		} catch(Exception $e) {
			preg_match('/Exception\\:.*in/',$e,$errors);
			return ['result' => null,'error' => $errors,'sql' => $q];
        }
		} else if(get_class($this->con) == 'mysqli') {
			$result = mysqli_query($this->con,$q);
			if($this->con->error == '') return ['result'=>$result,'error'=>null,'sql' => $q];
			else return ['result'=>null,'error'=>$this->con->error,'sql' => $q];
		}
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

    public function all($table = '') {
		if($table == '') $table = $this->table;
		$q = "SELECT * FROM $table;";
		return $this->query($q);
    }


	// obj(sql) {
    //     let obj = {
    //         sql,
    //         semicolon: () => {obj.sql = obj.sql.replace(';','')},
    //         order: (field,order = 0) => {
    //             obj.semicolon()
    //             if(!order) order = ' ASC'
    //             else order = ' DESC'
    //             obj.sql += ` ORDER BY "${field}"${order};`; 
    //             return obj
    //         },
    //         limit:(num)=> {
    //             obj.semicolon()
    //             obj.sql += ` LIMIT ${num};`; 
    //             return obj
    //         },
    //         run: (fn) => {this.query([obj.sql],fn)},
    //         first: (fn) => {
    //             obj.limit(1)
    //             this.query([obj].sql,fn)
    //         },
    //         async: async () => await this.queryAsync(sql)
    //     }
    //     return obj
    // }

    // actionObj(sql) {
    //     return {
    //         sql,
    //         run: (fn) => {this.query([sql],fn)},
    //         async: async () => await this.queryAsync(sql)
    //     } 
    // }


    // where(where,table=this.table) {
    //     if(table !== undefined) {
    //         where = this.objToString(where)
    //         let sql = `SELECT * FROM ${table} WHERE ${where};`
    //         return this.obj(sql)
    //     } else console.log('Table not specified')
    // }

    // delete(where,table=this.table) {
    //     if(table !== undefined) {
    //         where = this.objToString(where)
    //         let sql = `DELETE FROM ${table} WHERE ${where};`
    //         return this.actionObj(sql)
    //     } else console.log('Table not specified')
    // }

    // update(set,where,table=this.table) {
    //     if(table !== undefined) {
    //         where = this.objToString(where)
    //         set = this.objToString(set,true)
    //         let sql = `UPDATE ${table} SET ${set} WHERE ${where};`
    //         return this.actionObj(sql)
    //     } else console.log('Table not specified')
    // }

    // insert(array,table = this.table) {
    //     if(table !== undefined) {
    //         let keys = '('+Object.keys(array[0]).toString()+')'
    //         let values = ''
    //         array.forEach(element => {
    //             element = this.password(element)
    //             let elementValues = Object.values(element)
    //             values += '('
    //             elementValues.forEach(elementValue => {
    //                values += `"${elementValue}",` 
    //             });
    //             values= values.slice(0, -1)
    //             values += '),'
    //         });
    //         values = values.slice(0, -1)
    //         var sql = `INSERT INTO ${table} ${keys} VALUES ${values};`
    //         return this.actionObj(sql)
    //     } else console.log('Table not specified')
    // }

    // password(obj) {
    //     if(obj.password !== undefined) {
    //         let crypto = require('crypto')
    //         obj.password = crypto.createHmac('sha256', obj.password).digest('hex')
    //     }
    //     return obj
    // }

    // objToString(obj,isSet = false) {
    //     let string = ''
    //     let glue
    //     let i = 0;
    //     for(let key in obj) {
    //         let sign = '='
    //         let value = obj[key]
    //         let crypto = require('crypto')
    //         if(key == 'password') value = crypto.createHmac('sha256', value).digest('hex')
    //         if(value !== undefined) {
    //             value = value.toString()
    //             if(value.includes('<')) {
    //                 if(value.includes('<=')) {
    //                     value = value.replace('<=','')
    //                     sign = '<='
    //                 } else {
    //                     value = value.replace('<','')
    //                     sign = '<'
    //                 }
    //             } else if(value.includes('>')) {
    //                 if(value.includes('>=')) {
    //                     value = value.replace('>=','')
    //                     sign = '>='
    //                 } else {
    //                     value = value.replace('>','')
    //                     sign = '>'
    //                 }
    //             }
    //             if(isSet) glue = ','
    //             else if(value.includes('|')) {
    //                 value = value.replace('|','')
    //                 glue = ' OR '
    //             } else glue = ' AND '
    //             if(i!== 0) string += glue
    //             string += `${key}${sign}'${value}'`
    //             i++
    //         } else return {err:'something wrong'}
    //     }
    //     return string
    // }

    // createTable(tableName,fields) {
    //     let fieldString = ''
    //     for(let field in fields) {
    //         fieldString += field + ' '+ fields[field]+','
    //     }
    //     fieldString = fieldString.slice(0, -1)
    //     let sql = `CREATE TABLE ${tableName} (${fieldString})`
    //     return this.actionObj(sql)
    // }

}


?>
# Model

## About
Also-model has three files:
1. model.php for queries to sqlite and mysql dbs
2. migration.php for creating tables, fake data and migration via cli
3. types - for building types of table fields

## Install

1. Run instalation via composer
```console
composer require also/model
```
2. Create model.php (or yourName) on your root folder and copy paste the code below. 
* For mysql, define you server, username, password and dbname. 
* For sqlite, define path to database. 
* Set path to tables for migration
* Don't forget to delete $con for db you are not using. 


```php
<?php
namespace Also;
include_once 'vendor/autoload.php';
define('ROOT',__DIR__.'\\');

$con = ROOT.'db/data.db', // DataBase for sqlite
$con => array("server","username","password", "dbName"), // DataBase for mySql
$tablePath = ROOT.'db/tables' // Place for tables

$model = new Model($con);
$migrate = new Migration($tablePath,$model);
$migrate->cli();

```

If sqlite is not working, do the folowing:
Inside php.ini, Remove the semicolon in front of:
1. extension=pdo_sqlite # 
2. extension=sqlite3 
3. sqlite3.extension_dir = "path top php folder\ext"


## Migration and types

Now, then you have created model.php, you can use it as command line. 
Here the options:
1. php model.php table tableName
2. php model.php migrate
3. php model.php delete tableName
4. php model.php restore tableName
5. php model.php fake tableName times
6. php model.php query

Each of the command will be explained next. 

### php model.php table tableName

Example:
```console
$ php model.php table test
```

The example above, creates file ``tablePath\test.php`` with $table and $fake variables. 
$table - includes the fields for creating new table with name test. 
$table - includes fakers to put fake data inside db (after migration)

It may look like this:
```php
<?php
namespace Also;

$table = [
    "id"=> $types->id,
    "username" => $types->text(50).$types->def(),
    "password" => $types->text("1b").$types->def(),
    "email" => $types->text("1b").$types->def(),
    "name" => $types->text(50),
    "last_name" => $types->text(50),
    "middle_name" => $types->text(50),
    "status" => $types-> num(1).$types->def("0"), // 1-active, 0-not active
    "is_admin" => $types->num(1).$types->def("0"), // 1-admin, 0-not not admin
    "timestamp" => $types->timestamp
];

function fake() {
    $faker = \Faker\Factory::create();
    $fake = [
        "username" => $faker->userName(),
        "password" => $faker->password(),
        "email" => $faker->email(),
        "name" => $faker->name(),
        "last_name" => $faker->lastName(),
        "status" => 0,
        "is_admin" => 0
    ];
    return $fake;
}
```
For using faker - read here https://fakerphp.github.io/.
The exmple above, using types.php to define types. 

#### types

##### **text**

  text is a function 
  ```php
  $types->text($size = '16b');
  
  ```
  * size (minimum is 1, maximum is 4g):
    * CHAR(size): 1-254
    * TINYTEXT: 255 1b+
    * VARCHAR(size): 256-65535 or 1b-256b
    * TEXT: 65536 or 256b+
    * MEDIUMTEXT: 65537-16777215 or 256b+ - 64k (by default)
    * LONGTEXT: 16777216-4294967295 or 64k+ - 16m


##### **num**

  syntax:
  ```php
  $types->num($size = '64m');
  // minimum = 1 
  // maximum = 268435456g
  ```
  * size
    * BOOL: 0-2
    * BIT(size): 0-64 or 1b
    * TINYINT(size): 65-255 or 1b-4b
    * SMALLINT(size): 256-65535 or 4b+ - 1k
    * MEDIUMINT(size): 65536-16777215 or 1k+ - 256k
    * INT(size): 16777216-4294967295 or 256k - 64m
    * BIGINT(size): 4294967296-9223372036854775807 or 64m+ - 268435456g

##### **float**

  syntax:
  ```php
  $types->float($size=24,$d=2);
  ```
  * size
    * FLOAT(size): If size is from 0 to 24, the data type becomes FLOAT(). If size is from 25 to 53, the data type becomes DOUBLE()
    * DOUBLE(size, d): if d not undefined. The total number of digits is specified in size. The number of digits after the decimal point is specified in the d parameter


##### **def**
def is a function for define default value. 

  Syntax:
  ```javascript
  $types->def($defaultValue = null)
  ```
  * defaultValue
    * null - default value NOT NULL - by default
    * Any string - default value for this field

##### **Other types**
DATE,DATETIME(fsp),TIME(fsp) and YEAR, not included because their syntax is simple and clear. 

Althought that you can use those types with def() function. 

For example:
```php
'DATE'.def()
'DATETIME'.def()
```


### migration

```console
$ php model.php migrate
```
If table not exists, it will be created. 

### delete table
```console
$ php model.php delete test
```

The example above will drop table test if it exists.


### restore table
```console
$ php model.php restore test
```

The example above will drop table test if it exists and then will create it again.

### Faker
```console
php model.php fake test times
```

The example above, will create fake data with fake function and fakers you have seted and then will insert it to table test.


### query

```console
$ php model.php query
```

The command above, will run query cli. 
You have to set model name with model modelName. And then you can use model commands which will be explained next. 
For example ``all()`` will fetch all data from model you have choosen. 

``exit`` or ctrl+c will terminate the cli. 

``history`` will show your history of commands. 

```console
nomodel: model test
test: all()
test: exit
```


## Model

### Choosing a model. 
There are two options to choose a model:
1. By passing model name to public property $table:
```php
  $model->table = 'tableName';
```

2. By using model method:
```php
$model->model('tableName);
```

There are few methods for query to model:
1. insert([[fields],[filds],...])
2. where([conditions],[options])
3. all([options])
4. delete([conditions])
5. update([data],[conditions])
6. delete([conditions])
7. createTable(tableName,[fields])
8. dropTable(tableName)
9. query(sqlQuery)

All the methods, return $result, which looks like this: 
``[$result,$error,$sql,$changes]``

1. $result - containes result of query
2. $error - containes error if occured
3. $sql - containes sql query to db
4. $changes - containes number of affected rows
5. $warnings (only in mysql) - warnings


Here the example for each query:

```php

$model->insert([
    ['username'=>'Alex','password'=>'bbb','email'=>'aaa@mail.com']
]);

$model->where(
    ["name <> 'NULL'", 'id>3'], // where
    ['limit'=>5,'orderby'=>'username','order'=>'ASC'] // options
); 

$model->model('test')->all();

$model->delete(["name IS NULL"]);

$model->update(['name'=>'Alex'],['id=80']);

$model->createTable($tableName,$fields);

$model->dropTable($tableName);

$model->query('SELECT * FROM test;);

```


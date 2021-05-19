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
define('ROOT',__DIR__.'/');

$con = ROOT.'db/data.db', // DataBase for sqlite
$con => array("server","username","password", "dbName"), // DataBase for mySql
$tablePath = ROOT.'db/tables' // Place for tables

$model = new Model($con);
$migrate = new Migration($tablePath,$model);
$migrate->cli();

```

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

The example above, creates file ``tablePath\test.php`` with $table, $data and fake function. 
$table - includes the fields for creating new table with name test. 
fake_tableName - includes fakers to create fake data inside database
$data = includes data to create on migration

For using faker - read here https://fakerphp.github.io/.

#### types

* 'char($size='')'
* 'varchar($size='')'
* 'text() - TEXT'
* 'mtext() - MEDIUMTEXT'
* 'ltext() =>  LONGTEXT'
* 'bit($size ='')'
* 'tinyInt($size ='')'
* 'smallInt($size ='')'
* 'mediumInt($size ='')'
* 'int($size ='')'
* 'bigInt($size ='')'
* 'float($size ='')'
* 'double($size ='',$d=2)'
* '$id'
* '$timestamp'
* 'notNull()'
* 'def($def = '')'

Example:

```php
$table = [
    "id"=> $types->id,
    "username" => $types->notNull()->char(50),
    "password" => $types->notNull()->varchar(),
    "email" => $types->notNull()->char(),
    "name" => $types->char(50),
    "last_name" => $types->char(50),
    "middle_name" => $types->char(50),
    "status" => $types->def(0)->int(1), // 1-active, 0-not active
    "is_admin" => $types->def(0)->int(1), // 1-admin, 0-not not admin
    "timestamp" => $types->timestamp
];
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
  $model->_table = 'tableName';
```

2. By using model method:
```php
$model->table('tableName);
```

There are few methods for query to model:
CRUD methods:

* create
  * ``create($dataToInsert:array):rowOfInsertedData``
  * ``createMany([$dataToInsert,$dataToInsert,...]):collection``
* read
  * ``all():conllection or row``
  * ``get():conllection or row``
  * ``first():row``
* update
  * ``set($dataToUpdate:array):mountOfChanges``
* delete
  * ``delete(id=''):mountOfChanges``

select methods
* ``where($key,$value,$glue):modelObject``
* ``and($key,$value,$glue):modelObject``
* ``or($key,$value,$glue):modelObject``
* ``not($key,$value,$glue):modelObject``
* ``id($id):modelObject``

order and limit
* ``orderBy($field)::modelObject``
* ``asc()::modelObject``
* ``desc()::modelObject``
* ``limit($amount)::modelObject``

Other
* ``query($sql)``
* ``createTable($tableName,$tableFields):true``
* ``dropTable($tableName):true``

In case of error, ``array($error,$sql)`` will be retturned;


Here the example for each query:

```php
$model->table('test');

$model->create([
  'username'=>'Alex',
  'password'=>'bbb',
  'email'=>'aaa@mail.com'
]);

$model->model('test')->all();

$model->where('id',3,'>')
->not('name','NULL')
->orderBy('username')
->asc()
->limit(10);

$model->where("name","NULL")->delete();

$model->id(80)->set(['name'=>'Alex']);

$model->createTable($tableName,$fields);

$model->dropTable($tableName);

$model->query('SELECT * FROM test;);

```


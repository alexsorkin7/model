<?php
namespace Also;

class Types {
    // public $id = [
    //     'INTEGER DEFAULT AUTO_INCREMENT PRIMARY KEY', // for sqlite
    //     'INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY', // for mysql
    // ];
   
    private $default = '';
    public $id ='INTEGER DEFAULT AUTO_INCREMENT PRIMARY KEY'; // for sqlite
    public $timestamp = 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP';
    public function notNull() {$this->default = " NOT NULL";return $this;}
    public function def($def) {$this->default = " DEFAULT '$def'";return $this;}
    public $char = fn($size='') =>  "CHAR($size) ".$this->default;
    public $varchar = fn($size='') =>  "VARCHAR($size) ".$this->default;
    public $text = fn() =>  "TEXT ".$this->default;
    public $mtext = fn() =>  "MEDIUMTEXT ".$this->default;
    public $ltext = fn() =>  "LONGTEXT ".$this->default;
    public $bit = fn($size ='') =>  "BIT($size) ".$this->default;
    public $tinyInt = fn($size ='') =>  "TINYINT($size) ".$this->default;
    public $smallInt = fn($size ='') =>  "TINYINT($size) ".$this->default;
    public $mediumInt = fn($size ='') =>  "MEDIUMINT($size) ".$this->default;
    public $int = fn($size ='') =>  "INT($size) ".$this->default;
    public $bigInt = fn($size ='') =>  "BIGINT($size) ".$this->default;
    public $float = fn($size ='') =>  "FLOAT($size) ".$this->default;
    public $double = fn($size ='',$d=2) =>  "DOUBLE($size,$d) ".$this->default;
}

$types = new Types();

?>

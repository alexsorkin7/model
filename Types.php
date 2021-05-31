<?php
namespace Also;
 
class Types {
    // public $id = [
    //     'INTEGER DEFAULT AUTO_INCREMENT PRIMARY KEY', // for sqlite
    //     'INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY', // for mysql
    // ];
   
    private $default = '';
    private function param($com,$p = '',$p1 = '') {
        $this->default = '';
        if($p == '') return $com;
        else if($p !== '') return $com.'('.$p.') ';
        else if($p !== '' && $p1 !== '') return $com.'('.$p.','.$p1.') ';
    }
    public $id ='INTEGER DEFAULT AUTO_INCREMENT PRIMARY KEY '; // for sqlite
    public $timestamp = 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ';
    public function null() {$this->default = " DEFAULT NULL";return $this;}
    public function def($def) {$this->default = " DEFAULT '$def'";return $this;}
    public function char($size='') {return $this->param("CHAR",$size).$this->default;}
    public function varchar($size=16383) {return "VARCHAR($size) ".$this->default;}
    public function text() {return "TEXT ".$this->default;}
    public function mtext() {return "MEDIUMTEXT ".$this->default;}
    public function ltext() {return "LONGTEXT ".$this->default;}
    public function bit($size ='') {return $this->param("BIT",$size).$this->default;}
    public function tinyInt($size ='') {return $this->param("TINYINT",$size).$this->default;}
    public function smallInt($size ='') {return $this->param("TINYINT",$size).$this->default;}
    public function mediumInt($size ='') {return $this->param("MEDIUMINT",$size).$this->default;}
    public function int($size ='') {return $this->param("INT",$size).$this->default;}
    public function bigInt($size ='') {return $this->param("BIGINT",$size).$this->default;}
    public function float($size ='') {return $this->param("FLOAT",$size).$this->default;}
    public function double($size ='',$d=2) {return $this->param("DOUBLE",$size,$d).$this->default;}
}

?>

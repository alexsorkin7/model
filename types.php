<?php
namespace Also;

class Types {
    // public $id = [
    //     'INTEGER DEFAULT AUTO_INCREMENT PRIMARY KEY', // for sqlite
    //     'INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY', // for mysql
    // ];
    public $id ='INTEGER DEFAULT AUTO_INCREMENT PRIMARY KEY'; // for sqlite

    public $timestamp = 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP';

    public function text($size = '16b') { // byte = 256, min 1, max 4g
        $size = $size;
        $plus = 0;
        if(strpos($size,'+') !== false) {
            $size = str_replace('+','',$size);
            $plus = 1;
        }
        if(strpos($size,'b') !== false) {
            $size = trim(str_replace('b','',$size));
            $size = $size*256-1;
        }
        else if(strpos($size,'k') !== false) {
            $size = trim(str_replace('k','',$size));
            $size = $size*256*1024-1;
        }
        else if(strpos($size,'m') !== false) {
            $size = trim(str_replace('m','',$size));
            $size = $size*256*1024*1024-1;
        }
        else if(strpos($size,'g') !== false) {
            $size = trim(str_replace('g','',$size));
            $size = $size*256*1024*1024*1024-1;
        }
        if($plus) $size++;
        if($size >= 1 && $size <255) $size = "CHAR($size)";
        else if($size == 255) $size = "TINYTEXT";
        else if($size >= 256 && $size < 65535) {
            $realSize = $size/256;
            $size = "VARCHAR($realSize)";
        }
        else if($size == 65536) $size = "TEXT";
        else if($size >= 65537 && $size < 16777215) $size = "MEDIUMTEXT";
        else if($size >= 16777216) $size = "LONGTEXT";
        return $size;
    }

    public function num($size = '64m'){ // bit == 64
        $plus = 0;
        if(strpos($size,'+') !== false) {
            $size = str_replace('+','',$size);
            $plus = 1;
        }
        if(strpos($size,'b') !== false) {
            $size = trim(str_replace('b','',$size));
            $size = $size*64;
        }
        else if(strpos($size,'k') !== false) {
            $size = trim(str_replace('k','',$size));
            $size = $size*64*1024-1;
        }
        else if(strpos($size,'m') !== false) {
            $size = trim(str_replace('m','',$size));
            $size = $size*64*1024*1024-1;
        }
        else if(strpos($size,'g') !== false) {
            $size = trim(str_replace('g','',$size));
            $size = $size*64*1024*1024*1024-1;
        }

        if($plus) $size++;
        if($size<=2) $size = "BOOLEAN";
        else if($size<=64) $size = "BIT(${$size})";
        else if($size>64 && $size<=255) $size = "TINYINT($size)";
        else if($size>=256 && $size<=65535) {
            $realSize = $size/256;
            $size = "SMALLINT($realSize))";
        } else if($size>=65536 && $size<=16777215) {
            $realSize = $size/65536;
            $size = "MEDIUMINT($realsize)";
        }
        else if($size>=16777216 && $size<=4294967295) {
            $realSize = $size/16777216;
            $size = "INT($realsize)";
        }
        else if($size>=4294967295 && $size<=18446744073709551615) {
            $realSize = $size/4294967295;
            $size = "BIGINT($realsize)";
        }
        return $size;
    }
    
    public function float($size=24,$d=2) {
        if($size <= 24) $size = "FLOAT";
        else $size = "DOUBLE($size,$d)";
        return $size;
    }

    public function def($defaultValue = '') {
        if($defaultValue == '') return " NOT NULL";
        else return " DEFAULT \"$defaultValue\"";
    }
}

$types = new Types();

?>

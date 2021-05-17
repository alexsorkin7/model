<?php
namespace Also;

class Migration {
    public $tablesPath;
    public $model;
    public $template;

    function __construct($tablesPath,$model,$template = '') {
        $this->tablesPath = $tablesPath;
        if (!file_exists($tablesPath)) mkdir($tablesPath, 0777, true);
        $this->model = $model;
        if($template !== '') $this->template = $template;
    }

    private function createTableFile($tableName) { 
        $myfile = fopen($this->tablesPath.'/'.$tableName.'.php', "w") or die("Unable to open file!");
        fwrite($myfile, $this->modelTemplate);
        fclose($myfile);
    }

    private function migrate() {
        $fileList = scandir($this->tablesPath);
        foreach ($fileList as $file) {
            if(pathinfo($file)['extension'] == 'php') {
                $this->newTable(basename($file,".php"),$file);
            }
        }
    }

    public function newTable($tableName,$file) {
        include_once __DIR__.'/types.php';
        include_once $this->tablesPath.'/'.$file;
        $result = $this->model->createTable($tableName,$table);
        if(isset($result['result'])) {
            echo "Table $tableName created. ";
            if(count($data)) {
                $result = $this->model
                ->model($tableName)
                ->insert([$data]);
                if(isset($result['changes'])) echo $result['changes'].' rows was inserted';
                else echo $result['error'];
            }
        } else echo "Error: ".$result['error'];
    }

    public function dropTable($tableName) {
        $result = $this->model->dropTable($tableName);
        $this->result($result,"Table $tableName deleted.");
    }

    private function result($result,$msg) {
        if(isset($result['result'])) echo $msg;
        else echo "Error: ".$result['error'];
    }

    public function restoreTable($table) {
        $this->dropTable($table);
        $this->newTable($table,$table.'.php');
    }
    
    public function fake($tableName,$times) {
        include_once __DIR__.'/types.php';
        include_once $this->tablesPath.'/'.$tableName.'.php';
        $array = [];
        for($i=0; $i<$times; $i++) {
            $array[] = fake();
        }
        print_r($array);
        $this->model->model($tableName);
        $result = $this->model->insert($array);
        $this->result($result,'Done');
    }

    public function cli() {
        global $argv;
        if(isset($argv[1])) {
            if(strpos('table',$argv[1]) !== false) $this->createTableFile($argv[2]);
            else if(strpos('migrate',$argv[1]) !== false) $this->migrate();
            else if(strpos('delete',$argv[1]) !== false) $this->dropTable($argv[2]);
            else if(strpos('restore',$argv[1]) !== false) $this->restoreTable($argv[2]);
            else if(strpos('fake',$argv[1]) !== false) $this->fake($argv[2],$argv[3]);
            else if(strpos('query',$argv[1]) !== false) $this->query();
        }
    }

    public function query() {
        $line = '';
        $model = 'nomodel';
        $error = '';
        $resource = fopen('php://stdin', 'r');
        while(true) {
            echo "$model : ";
            $line = fgets($resource);
            // $line = readline($model.': ');
            if($line == 'exit') exit;
            else if(strpos($line,'model') !== false) {
                $line = str_replace('model','',$line);
                $line = trim($line,' ');
                $line = trim(preg_replace('/\s\s+/', ' ', $line));
                $this->model = $this->model->model($line);
                $model = $line;
            } else if($line == 'history') print_r(readline_list_history())."  \r\n";
            else if($line !== '') {
                error_reporting(0);
                echo eval('print_r($this->model->'.$line.');')." \r\n ";
            }
            // readline_add_history($line);
        }
        // print_r(readline_list_history()); //dump history
        // print_r(readline_info()); //dump variables
    }

    public $modelTemplate = '<?php
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

$data = [];
';
}
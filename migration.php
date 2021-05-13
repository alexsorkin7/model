<?php
namespace Also;

class Migration {
    public $tablesPath;
    public $model;
    public $template;

    function __construct($tablesPath,$model,$template = '') {
        $this->tablesPath = $tablesPath;
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
        global $types;
        // $table = include_once $this->tablesPath.'/'.$file;
        include_once $this->tablesPath.'/'.$file;
        $result = $this->model->createTable($tableName,$table);
        $this->result($result,"Table $tableName created.");
    }

    public function dropTable($tableName) {
        $result = $this->model->dropTable($tableName);
        $this->result($result,"Table $tableName deleted.");
    }

    private function result($result,$msg) {
        if($result['error'] == null) echo $msg;
        else if($result['error'] !== null) echo "Error: ".$result['error'][0];
    }

    public function restoreTable($table) {
        $this->dropTable($table);
        $this->newTable($table,$table.'.php');
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

    public function fake($tableName,$times) {
        $array = [];

    }

    // fake(tableName,times) {
    //     let array = []
    //     for (let i = 0; i < times; i++) {
    //         let tablePath = this.path.join(this.tablesPath,tableName+'.js')
    //         let fake = require(tablePath)['fake']()
    //         console.log(fake)
    //         array.push(fake)
    //     }
    //     this.model.table = tableName
    //     this.model.insert(array).run(data => console.log(data))
    // }

    // query() {
    //     const readline = require('readline');
    //     const rl = readline.createInterface({
    //         input: process.stdin,
    //         output: process.stdout
    //     });
    //     rl.on('line', (input) => {
    //         if(input.includes('.')) {
    //             let tableName = input.split('.')[0]
    //             let query = input.split('.')[1]
    //             this.model.table = tableName
    //             new Function('model',`
    //                 model.${query}.run(result=>{
    //                     console.log(result)
    //                 })
    //             `)(this.model)
    //         }
    //         if(input == 'exit') rl.close();
    //     });
    // }


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

    ';



}
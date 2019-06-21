<?php

namespace Maxcore\Db;

use \Maxcore\App;
use PDO;
use PDOException;
use stdClass;

class Model {

    private $dbConnection;
    private $dbEngine;
    private $dbConfig;
    private $dbdriver;
    private $connLib;
    private $prepare;
    private $returnSql = false;
    private $dumpSql = false;
    private $querySql;
    /*
     * Tablo sistemi ile
     */
    protected $defined_table = false;
    public $table;
    public $tableName;
    public $col;
    protected $disable_default = false;
    public $selectedId;
    protected $select = " * ";
    protected $from = NULL;
    protected $join = "";
    protected $orderby = NULL;
    protected $in = NULL;
    protected $where = NULL;
    protected $whereTemp = NULL;
    protected $whereQuery = "";
    protected $limit = NULL;
    protected $getAllQuery;

    public function __construct($lib = null, $config = null) {

        $this->connLib = "pdo";

        $this->dbdriver = $config == null ? App::getConfig("db", "driver") : $config['driver'];

        $this->dbConfig = $config == null ? App::getConfig("db") : $config;

        $this->pdoConnect();

        $this->cols = new stdClass();
    }

    protected function pdoConnect() {
        try {
            $database_connection = new PDO(
                    'mysql:host=' . $this->dbConfig["host"] . ';dbname=' .
                    $this->dbConfig["database"] . '', $this->dbConfig["username"], $this->dbConfig["password"], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $database_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->dbConnection = $database_connection;
        } catch (PDOException $e) {

            echo "Veritabanı Bağantı Hatası!:" . $e->getMessage();
            
                 if(isset($_SESSION["extra_config"]["db"]["database"])){
                         unset($_SESSION["extra_config"]);
                    }
                    
                    
            die();
        }
    }

    public function getConnection() {
        return $this->dbConnection;
    }

    public function getEngine() {
        return $this->dbEngine;
    }

    public function getLibName() {
        return $this->connLib;
    }

    public function select($select = NULL) {

        if ($select != NULL) {
            
            if(is_array($select)){
                
                $this->select = "";
                
                
                foreach ($select as $key) {
                    
                    
                    $this->select.=" ".$key." ,";
                    
                }
                
                $this->select = rtrim($this->select,",");
                
                
            }else{
                 $this->select = $select;
            }

           
        }

        return $this;
    }

    public function from($from = NULL) {

        if ($from != NULL) {

            $this->from = $from;
        }

        return $this;
    }


    public function where($where = NULL) {

        if ($where != NULL) {

            if (is_array($where)) {


                if ($this->whereTemp == NULL) {

                    $this->whereTemp = $where;
                } else {

                    foreach ($where as $key => $value) {
                        $this->whereTemp[$key] = $value;
                    }
                }
            } else {

                $sql = $where;


                if (strpos($sql, '<=')) {
                    $ex = explode("<=", $sql);
                    $this->whereTemp[$ex[0]] = ["<=", $ex[1]];
                } else if (strpos($sql, '>=')) {
                    $ex = explode(">=", $sql);
                    $this->whereTemp[$ex[0]] = [">=", $ex[1]];
                } else if (strpos($sql, '<>')) {
                    $ex = explode("<>", $sql);
                    $this->whereTemp[$ex[0]] = ["<>", $ex[1]];
                } else if (strpos($sql, '!=')) {
                    $ex = explode("!=", $sql);
                    $this->whereTemp[$ex[0]] = ["!=", $ex[1]];
                } else if (strpos($sql, '!==')) {
                    $ex = explode("!==", $sql);
                    $this->whereTemp[$ex[0]] = ["!==", $ex[1]];
                } else if (strpos($sql, '===')) {
                    $ex = explode("===", $sql);
                    $this->whereTemp[$ex[0]] = ["===", $ex[1]];
                } else if (strpos($sql, '==')) {
                    $ex = explode("==", $sql);
                    $this->whereTemp[$ex[0]] = ["==", $ex[1]];
                } else if (strpos($sql, '=')) {
                    $ex = explode("=", $sql);
                    $this->whereTemp[$ex[0]] = ["=", $ex[1]];
                } else if (strpos($sql, '>')) {
                    $ex = explode(">", $sql);
                    $this->whereTemp[$ex[0]] = [">", $ex[1]];
                } else if (strpos($sql, '<')) {
                    $ex = explode("<", $sql);
                    $this->whereTemp[$ex[0]] = ["<", $ex[1]];
                }
            }
        }
        return $this;
    }

    public function whereSql($sql) {

        $this->whereQuery .= " " . $sql;
        return $this;
    }

    public function disableDefault() {

        $this->disable_default = true;

        return $this;
    }

    public function getWhere($type = NULL) {


        if (isset($this->table->selectWhere)) {

            if (is_array($this->table->selectWhere) && $this->disable_default == false) {

                if (is_array($this->whereTemp)) {

                    $this->whereTemp = array_merge($this->whereTemp, $this->table->selectWhere);
                    
                } else {
                    $this->whereTemp = $this->table->selectWhere;
                }
            }
        }


        $whereString = "";

        $i = 0;


        if (is_array($this->whereTemp)) {


            foreach ($this->whereTemp as $key => $val) {

                if ($i != 0) {

                    $whereString .= " and ";
                }

                if (is_numeric($val[1])) {


                    $whereString .= $key . " " . $val[0] . " " . $val[1];
                } else {

                    $whereString .= $key . " " . $val[0] . " \"$val[1]\" ";
                }

                $i++;
            }
        }


        if ($whereString == "") {

            if ($whereString != "" && $whereString != NULL) {

                $this->where = "WHERE " . $whereString;
            } else {

                $this->where = "";
            }
        } else {

            $this->where = "WHERE " . $whereString;
        }


        return true;
    }

    public function getFrom() {

        if ($this->defined_table) {

            return $this->tableName;
        } else {

            return $this->from;
        }
    }

    public function orderBy($orderBy, $orderDir = null) {


        if (!is_null($orderDir)) {


            $this->orderby = $orderBy . ' ' . strtoupper($orderDir);
        } else {

            if (stristr($orderBy, ' ') || $orderBy == 'rand()') {

                $this->orderby = $orderBy;
            } else {
                $this->orderby = $orderBy . ' ASC';
            }
        }



        return $this;
    }

    public function limit($limit, $limitEnd = null) {

        if (!is_null($limitEnd)) {
            $this->limit = $limit . ', ' . $limitEnd;
        } else {
            $this->limit = $limit;
        }



        return $this;
    }

    public function in($column, $in) {

        $this->in = " {$column} in ($in)";

        return $this;
    }

    public function returnSql($status = true) {

        $this->returnSql = $status;

        return $this;
    }

    public function dumpSql($status = true) {

        $this->dumpSql = $status;

        return $this;
    }

    public function prepareQuery($queryString = NULL) {

        $this->querySql = "SELECT {$this->select} FROM {$this->getFrom()}";
        
        
        $this->querySql.=$this->join;


        if ($this->getWhere()) {

            $this->querySql .= ' ' . $this->where;

            if ($this->in != NULL) {

                $this->querySql .= ' and ' . $this->in;
            }
        } else {

            if ($this->in != NULL) {

                $this->querySql .= ' WHERE ' . $this->in;
            }
        }

        if ($queryString != NULL) {

            $this->querySql .= ' ' . $queryString;
        }

        if ($this->orderby != NULL) {

            $this->querySql .= ' ' . $this->orderby;
        }

        if ($this->limit != NULL) {

            $this->querySql .= ' ' . $this->limit;
        }
    }

    public function get($queryString = NULL) {

        $this->prepareQuery($queryString);

        if ($this->dumpSql) {
            var_dump($this->querySql);
        }

        if ($this->returnSql) {

            return $this->querySql;

            die();
        } else {

            if ($this->querySql != "" && $this->querySql != NULL) {


                $result = $this->dbConnection->query($this->querySql)->fetch();


                if ($this->defined_table) {

                    if ($result) {

                        $this->col = array_merge($this->col, $result);
                    }
                }


                return $result;
            } else {
                return false;
            }
        }
    }

    public function getAll($queryString = NULL) {


        $this->prepareQuery($queryString);
        if ($this->dumpSql) {
            var_dump($this->querySql);
        }

        if ($this->returnSql) {


            return $this->querySql;

            die();
        } else {

            return $this->dbConnection->query($this->querySql)->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function exists($queryString = NULL) {

        $sql = "SELECT {$this->select} FROM {$this->getFrom()}";

        if ($this->getWhere()) {

            $sql .= ' ' . $this->where;
        }

        if ($queryString != NULL) {

            $sql .= ' ' . $queryString;
        }

        if ($this->returnSql) {
            return $sql;
            die();
        }

        $getAll = $this->dbConnection->query($sql);


        if ($getAll->rowCount() > 0) {

            return true;
        } else {

            return false;
        }
    }

    public function getSql() {
        return $this->querySql;
    }

    public function setSql($sql) {
        $this->querySql = $sql;
        return $this;
    }

    public function run($arguments = NULL) {

        $query = $this->dbConnection->prepare($sql);

        if ($arguments == NULL) {

            return $query->execute();
        } else {

            if (is_array($arguments)) {

                return $query->execute($arguments);
            } else {

                return false;
            }
        }
    }

    //------------------------Table Sistem Methodları--------------------------//

    public function table($tableName, $args = NULL, $return = false) {

        if (file_exists($tableFile = APP_DIR . DS . "Tables/{$tableName}.php")) {

            require_once $tableFile;

            if (class_exists($tableName)) {
                
                if($this->defined_table){
                    
                    $this->reset();
                    
                }
                
                

                $this->tableName = $tableName;

                if ($args == NULL) {

                    $table = new $tableName();
                } else {

                    $table = new $tableName($args);
                }


                $this->table = $table;

                if (isset($table->cols)) {

                    if (is_array($table->cols)) {

                        $this->col = $table->cols;
                    }
                }

                $this->defined_table = true;
            } else {

                exit("Table dosyasında sınıf tanımlı değil: $tableName");
            }
        } else {
            exit("Table dosyası bulunamadı: {$tableName}.php");
        }

        if ($return) {

            return $this->col;
        } else {

            return $this;
        }
    }

    public function col($key, $val) {

        if (array_key_exists($key, $this->col)) {

            $this->col[$key] = $val;

            return $this;
        } else {


            exit("Sütün Bulanamadı! = " . $key);
        }
    }

    public function find($id, $return = false) {

        $this->selectedId = $id;

        $this->where([$this->table->index => ["=", $id]]);

        $this->prepareQuery();

        $result = $this->dbConnection->query($this->querySql)->fetch(PDO::FETCH_ASSOC);

        if ($this->defined_table) {

            if ($result) {

                $this->col = array_merge($this->col, $result);
            }
        }

        if ($return) {

            return $this->col;
        } else {
            return $this;
        }
    }
    
    
        public function findWhere($where, $return = false) {

        $this->where($where);

        $this->prepareQuery();

        $result = $this->dbConnection->query($this->querySql)->fetch(PDO::FETCH_ASSOC);

        if ($this->defined_table) {

            if ($result) {

                $this->col = array_merge($this->col, $result);
            }
        }

        if ($return) {

            return $this->col;
        } else {
            return $this;
        }
    }
    
    
    public function increase_($col,$number){
        
         if ($this->selectedId) {

            $this->querySql = "UPDATE {$this->tableName} SET ";
            
            $this->querySql .= " {$col} = {$col}+{$number} ";

            $this->querySql .= " WHERE id = {$this->selectedId}";

            $this->prepare = $this->dbConnection->prepare($this->querySql);

            return $this->prepare->execute();
        } else {
            return false;
        }
        
        
    }
    
    
    public function decrease_($col,$number){
        
              if ($this->selectedId) {

            $this->querySql = "UPDATE {$this->tableName} SET ";
            
            $this->querySql .= " {$col} = {$col}-{$number} ";

            $this->querySql .= " WHERE id = {$this->selectedId}";

            $this->prepare = $this->dbConnection->prepare($this->querySql);

            return $this->prepare->execute();
        } else {
            return false;
        }
        
        
    }
    

    public function update_() {

        if ($this->selectedId) {

            $this->querySql = "UPDATE {$this->tableName} SET ";


            $this->col = array_merge($this->col, $this->table->update);

            foreach ($this->col as $key => $value) {

                if ($key != $this->table->index) {

                    if (is_numeric($value)) {

                        $this->querySql .= " {$key} = {$value} ,";
                    } else {

                        $this->querySql .= " {$key} = \"{$value}\" ,";
                    }
                }
            }

            $this->querySql = rtrim($this->querySql, ",");

            $this->querySql .= " WHERE id = {$this->selectedId}";

            $this->prepare = $this->dbConnection->prepare($this->querySql);

            return $this->prepare->execute();
        } else {
            return false;
        }
    }
    
    
    public function paginate($request,$quantity = NULL){
        
        $sayfa=1;
 
        $sayfada = $quantity == null ? 10 : $quantity;  

        if($request != null){

            if($request->has("quantity")){

                $sayfada = $request->input("quantity");

            }

            if($request->has("page")){

               $sayfa=$request->input("page"); 

            }
        
        }
 
        $select="SELECT {$this->select} ";
        
        $from = "FROM {$this->getFrom()} ";
        
        $sql = " FROM {$this->getFrom()}";
        
        $selectSql = $sql;
        
       if($this->join != null){
           
           $selectSql .= ' ' . $this->join;
       }
       
       

        if ($this->getWhere()) { $sql .= ' ' . $this->where; $selectSql .= ' ' . $this->where;}

        if($sayfa < 1){
            
            $sayfa = 1; 
        }
        /*
         * Toplam İçerik Ve Sayfa Sayısı
         */
        $result = $this->dbConnection->prepare("SELECT COUNT(*) AS toplam $sql"); 
        $result->execute(); 
        $toplam_icerik = $result->fetchColumn(); 
        $toplam_sayfa = ceil($toplam_icerik / $sayfada);
        
        
        if($sayfa > $toplam_sayfa) {
            
            $sayfa = $toplam_sayfa; 
            
        }

        $limit = ($sayfa - 1) * $sayfada;
        
        if($limit < 0){
            
            $limit = 0;
            
        }
        
        
        $sql_string = $select.$selectSql." LIMIT " . $limit . ', ' . $sayfada;

        $getQuery = $this->dbConnection->prepare($sql_string);
        
        $getQuery->execute();
        
        $getAll=$getQuery->fetchAll(PDO::FETCH_ASSOC);
        
  
        
      return [
          'result'=>$getAll,
          'paginate'=>[
            'total_page'=>$toplam_sayfa,
            'quantity'=>$sayfada,
            'now_page'=>$sayfa
          ]

      ];
        
         
        
    }

    public function save_() {

        $this->querySql = "INSERT INTO {$this->tableName} ";

        $keys = "";

        $vals = "";

        $exec_data = [];

        $i = 0;

        $this->col = array_merge($this->col, $this->table->insert);

        foreach ($this->col as $key => $value) {

            if ($key != $this->table->index) {

                if ($value != NULL) {
                    $keys .= "{$key} ,";

                    $vals .= "? ,";

                    $exec_data[$i] = $value;

                    $i++;
                }
            }
        }

        $keys = rtrim($keys, ",");

        $vals = rtrim($vals, ",");

        $this->querySql .= " ({$keys}) VALUES ({$vals})";

        $this->prepare = $this->dbConnection->prepare($this->querySql);

        $result =  $this->prepare->execute($exec_data);
        
        
        if($result){
            
             return $this->dbConnection->lastInsertId();
        }else{
            
            return false;
        }
    }

    public function remove_() {
        if ($this->selectedId) {

            $this->querySql = "UPDATE {$this->tableName} SET ";


            $this->table->update["remove"] = 1;

            foreach ($this->table->update as $key => $value) {

                if ($key != $this->table->index) {

                    if (is_numeric($value)) {

                        $this->querySql .= " {$key} = {$value} ,";
                    } else {

                        $this->querySql .= " {$key} = \"{$value}\" ,";
                    }
                }
            }

            $this->querySql = rtrim($this->querySql, ",");

            $this->querySql .= " WHERE id = {$this->selectedId}";

            $this->prepare = $this->dbConnection->prepare($this->querySql);

            return $this->prepare->execute();
        } else {
            return false;
        }
    }
    
    public function reset(){
        
    $this->returnSql = false;
    $this->dumpSql = false;
    $this->querySql=NULL;
    $this->defined_table = false;
    $this->table= NULL;
    $this->tableName= NULL;
    $this->col= NULL;
    $this->disable_default = false;
    $this->selectedId= NULL;
    $this->select = " * ";
    $this->from = NULL;
    $this->join = "";
    $this->orderby = NULL;
    $this->in = NULL;
    $this->where = NULL;
    $this->whereTemp = NULL;
    $this->whereQuery = "";
    $this->limit = NULL;
    $this->getAllQuery= NULL;
    return $this;
        
    }

    public function innerjoin($joinData=null) {
        if($joinData != NULL){
            
            if(is_array($joinData)){
                
                foreach ($joinData as $key => $value) {
                    
                    $this->join.=" INNER JOIN $key ON {$value}";
                }
            }else{
                
                $this->join.= $joinData;
            }
        }
        return $this;
        
    }

}

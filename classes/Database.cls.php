<?php
require_once('./classes/Model.cls.php');

class Database{
  public $mysql;

  public function __construct($dbhost, $dbname, $dbuser, $dbpw){
    $this->mysql = mysql_connect($dbhost, $dbuser, $dbpw);
    if (!$this->mysql) {
      die('Could not connect: ' . mysql_error());
    }
    mysql_select_db($dbname, $this->mysql);
  }

  private function _escape_string($s){
    return mysql_real_escape_string($s, $this->mysql);
  }

  private function _preprocess_vals($vals){
    $vs = array();
    foreach($vals as $val){
      if(gettype($val) == "string"){
        $val = "'" . $this->_escape_string($val) . "'";
      }
      if(!$val){
        $val = 'NULL';
      }
      array_push($vs, $val);
    }

    return $vs;
  }

  public function query($statement){
    return mysql_query($statement, $this->mysql);
  }

  public function findAll($table){
    $res = $this->query("SELECT * FROM `{$table}`");
    $ret = array();
    while ($row = mysql_fetch_assoc($res)) {
      array_push($ret, $this->model($table, $row));
    }
    return $ret;
  }

  public function findById($table, $id){
    $res = $this->query("SELECT * FROM `{$table}` WHERE id={$id};");
    $row = mysql_fetch_assoc($res);
    return $this->model($table, $row);
  }

  public function create($table, $cols, $vals){
    $vals = $this->_preprocess_vals($vals);

    $vals = join(', ', $vals);
    $cols = join(', ', $cols);
    $sql = "INSERT INTO `{$table}` ($cols) VALUES ({$vals});";
    $res = $this->query($sql);

    if(!$res) return false;
    else return mysql_insert_id($this->mysql);
  }

  public function update($table, $id, $cols, $vals){
    $vals = $this->_preprocess_vals($vals);

    $zipped = array_map(null, $cols, $vals);
    foreach($zipped as &$cv){
      $cv = join('=', $cv);
    }
    $zipped = join(', ', $zipped);

    $sql = "UPDATE `{$table}` SET {$zipped} WHERE id={$id};";
    return $this->query($sql);
  }

  public function remove($table, $id){
    $sql = "DELETE FROM `{$table}` WHERE id={$id};";
    return $this->query($sql);
  }

  public function model($table, $props){
    return new Model($this, $table, $props);
  }
}
?>

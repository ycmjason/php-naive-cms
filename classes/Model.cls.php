<?php
class Model{
  public $id;
  private $_db, $_name, $_table, $_fields;

  public function __construct($db, $table, $props){
    $this->_db = $db;
    $this->_table = $table;
    $this->_fields = array_keys($props);
    $this->_initProps($props);
  }

  private function _initProps($props){
    foreach($props as $f => $v){
      $this->$f = $v;
    }
  }

  private function _getVals(){
    $vals = array();
    foreach($this->_fields as $f){
      array_push($vals, $this->$f);
    }
    return $vals;
  }

  public function save(){
    if($this->id){
      $this->_db->update($this->_table, $thids->id, $this->_fields, $this->_getVals());
    } else {
      $this->id = $this->_db->create($this->_table, $this->_fields, $this->_getVals());
    }
    return $this;
  }
}
?>

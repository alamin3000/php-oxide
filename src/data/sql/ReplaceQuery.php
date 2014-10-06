<?php
namespace oxide\data\sql;

class ReplaceQuery extends InsertQuery {
   
   /**
    *
    * @access public
    * @return string 
    */
   public function render() {
      $sql = "REPLACE INTO " . 
                  $this->table . " " .
                  $this->set();
                  
      return $sql;
   }
}
?>
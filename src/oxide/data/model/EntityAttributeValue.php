<?php
namespace oxide\data\model;
use oxide\data;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EntityAttributeValue
 *
 * @author aahmed753
 */
class EntityAttributeValue 
{
   protected
      $_entityTable = null,
      $_attributeTable = null,
      $_valueTable = null,
      $_conn = null;


   /**
    *
    * @access public
    * @param string $entity_table
    * @param string $attribute_table
    * @param string $value_table
    * @param data\Connection $conn 
    */
   public function __construct($entity_table, $attribute_table, $value_table, $conn = null)
   {
      $this->_entityTable = $entity_table;
      $this->_attributeTable = $attribute_table;
      $this->_valueTable = $value_table;
      if($conn) $this->_conn = $conn;
   }
   
   /**
    *
    * @param data\Connection $conn 
    */
   
   /**
    *
    * @access public
    * @param data\Connection $conn 
    */
   public function setConnection(data\Connection $conn)
   {
      $this->_conn = $conn;
   }
   
   /**
    *
    * @access public
    * @return data\Connection
    */
   public function getConnection()
   {
      if(!$this->_conn) {
         $this->_conn = data\Connection::sharedInstance();
      }
      
      return $this->_conn;
   }
   
   
}

?>

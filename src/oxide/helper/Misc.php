<?php
namespace oxide\helper;

abstract class Misc {
   
   public static function buildTreeFromFlatHierarchyArray($tags, $idkey, $parentkey, $childrenkey = 'children')
   {
      $refs = array();
      $list = array();

      foreach($tags as $data) {
         $thisref = &$refs[ $data[$idkey] ];

         foreach($data as $key => $value) {
            $thisref[$key] = $value;
         }

         if ($data[$parentkey] == 0) {
             $list[ $data[$idkey] ] = &$thisref;
         } else {
             $refs[ $data[$parentkey] ][$childrenkey][ $data[$idkey] ] = &$thisref;
         }
     }

     return $list;
   }
  
  
  public static function traverseTreeArraySimple(array $tree, $children_key = 'children', $branch_start_callback = null, $branch_end_callback = null, $leaf_callback = null)
  {
     // defining recursive function
     $traverse = function(array $arr,  $bstart,  $bend,  $leaf) use (&$traverse, $children_key) {
         static $level = 0;
      
         if($bstart) $bstart($arr, $level);
         foreach ($arr as $key => $value) {
            if($leaf) $leaf($value, $level);
            
            if (!empty($value[$children_key])) { // this has sub nodes
               
               $level++;
               $traverse($value[$children_key], $bstart, $bend, $leaf);
               $level--;
            }
         }

         if($bend) $bend($arr, $level);

     };
     
     $traverse($tree, $branch_start_callback, $branch_end_callback, $leaf_callback);
  }
  
  public static function traverseTreeArray(array $tree, $children_key = 'children', $list_start_callback = null, $list_end_callback = null,  $branch_start_callback = null, $branch_end_callback = null, $leaf_callback = null)
  {
     // defining recursive function
     $traverse = function(array $arr, $list_start, $list_end, $branch_start,  $branch_end,  $leaf) use (&$traverse, $children_key) {
         static $level = 0;
      
         if($list_start) $list_start($arr, $level);
         foreach ($arr as $value) {
            
            if (!empty($value[$children_key])) { // this has sub nodes
               if($branch_start) $branch_start($value, $level);
               
               $level++;
               $traverse($value[$children_key], $list_start, $list_end, $branch_start, $branch_end, $leaf);
               $level--;
               
               if($branch_end) $branch_end($value, $level);
               
            } else {
               if($leaf) $leaf($value, $level);
            }
         }

         if($list_end) $list_end($arr, $level);

     };
     
     $traverse($tree, $list_start_callback, $list_end_callback, $branch_start_callback, $branch_end_callback, $leaf_callback);
  }

  
}
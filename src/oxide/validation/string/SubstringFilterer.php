<?php
namespace oxide\validation;

class SubstringFilterer implements Filterer
{
   private
           $_start = 0,
           $_length = null,
           $_type = null;

   const
      BREAK_TYPE_CHAR = 'char',
      BREAK_TYPE_WORD = 'word',
      BREAK_TYPE_SENTENCE = 'sentence';
   
   public function __construct($start, $length = null, $type = self::BREAK_TYPE_CHAR)
   {
      $this->_start = $start;
      $this->_length = $length;
      $this->_type = $type;
   }

   public function filter($value)
   {
      $start = $this->_start;
      $length = $this->_length;
      $type = $this->_type;
      
      if($type == self::BREAK_TYPE_CHAR) { return substr($value, $start, $length); }
      if($type == self::BREAK_TYPE_WORD) {
         $filtered=$value;
         $match = null;
         if (preg_match('/^.{' . $start . ',' . $length .'}\b/s', $value, $match)) {
             $filtered=$match[0];
         }
         return $filtered;
      }
      
      if($type == self::BREAK_TYPE_SENTENCE) {
         $sentences = preg_split('/(\.|\?|\!)(\s)/',$value);
         if (count($sentences) <= $length) { return $value; }
         $stopAt = 0;
         foreach ($sentences as $i => $sentence) {
             $stopAt += strlen($sentence);
             if ($i >= $length - 1) { break; }
         }
         $stopAt += ($length * 2);
         return substr($value, 0, $stopAt);
      }
   }
}
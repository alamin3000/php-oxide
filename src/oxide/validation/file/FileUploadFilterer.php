<?php
namespace oxide\validation\file;
use oxide\validation\Filterer;

/**
 * File upload filterer
 * 
 * Prepare for file upload
 * Validates file array
 * filter/sanitize file name
 */
class FileUploadFilterer implements Filterer {
   public function filter($values)
   {
      // filter out if file is not provided
      if(!is_array($values)) return NULL; // not array, $_FILE entry is array
      if(!isset($values['error'])) return NULL; // not $_FILE type, needs to have error entry
      if($values['error'] == UPLOAD_ERR_NO_FILE) {
         // no file was given
         // make empty/null
         return NULL;
      }
      
      // we will filter the file name
      $name = $values['name'];
      $filter = new FilenameFilterer();
      $filtered_name = $filter->filter($name);
      $values['name'] = $filtered_name;
      
      return $values;
   }
}
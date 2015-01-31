<?php
namespace oxide\validation\file;
use oxide\validation\Result;


/**
 * File upload processor
 * 
 * Handle the uploaded file process
 */
class FileUploadProcessor {
   protected 
      $_chmod = 0644,
      $_folder_chmod = 0755,
      $_allow_override = true,
      $_upload_dir = null,
      $_makedir = false;
   
   
   /**
    * construction
    * 
    * @param string $upload_dir directory where the file should be uploaded
    * @param type $chmod
    * @param bool $allow_file_override indicates if file should be overriden if alreay exists
    * @param bool $make_dir idicates if attempt to to create directory recursively
    */
   public function __construct($upload_dir, $chmod = 0644, $allow_file_override = true, $make_dir = false) {
      $this->_upload_dir = $upload_dir;
      $this->_chmod = $chmod;
      $this->_allow_override = $allow_file_override;
      $this->_makedir = $make_dir;
   }
   
   /**
    * Handles the file save process
    * 
    * Subclassing classes should override this method to handle any custom file saving process
    * @param string $source_file
    * @param string $destination_file
    * @param \oxide\validation\Result $result
    * @return bool
    */
   protected function processFileSave($source_file, $destination_file, Result &$result) {
      // preserve file from temporary directory
      $success = move_uploaded_file($source_file, $destination_file);     
      
      return $success;
   }
   
   /**
    * 
    * @param type $name
    * @param type $dir
    * @return type
    */
   protected function generateFileName($name, $dir) {
      $destination_file = "{$dir}/{$name}";
      return $destination_file;
   }


   /**
    * Process method, implements the Processor interface
    * 
    * Performs some misc validation and attempt the save the uploaded file
    * @param type $value
    * @param \oxide\validation\Result $result
    * @return null
    */
   public function process($value, Result &$result = null) {
      if(!$result) $result = new Result();
      
      // first we want to make sure that we have correct value type
      // this must be $_FILE value format array
      if(!is_array($value)) {
         $result->addError('Incorrect value passed.');
      }
      
      $upload_dir = $this->_upload_dir;

      // check if folder already exists
      if(!file_exists($upload_dir)) {
         // folder doesn't exists
         // should we create it?
         if($this->_makedir) {
            if(!mkdir($upload_dir, $this->_folder_chmod, true)) { // creates recursively
               $result->addError('Unable to create directory.');
               return NULL;
            }
         } else {
            $result->addError('Upload folder does not exists.');
            return NULL;
         }
      }
      
      // now check if directory is wriable
      if(!is_writable($upload_dir)) {
         $result->addError('Upload directory is not wriable.');
         return NULL;
      }
      
      $name = $value['name'];
      $uploaded_file = $value['tmp_name'];

      // generate destintation file
      $destination_file = $this->generateFileName($name, $upload_dir);
      
      // check for some issues with 
      if(strpos($destination_file, $upload_dir) !== 0) {
         $result->addError('Incorrect uploaded file name.');
         return NULL;
      }
      
      // if override is not allowed, then we need to check if file already
      // exists or not
      if(!$this->_allow_override) {
         if(file_exists($destination_file)) {
            $result->addError('File already exists with same filename.');
            return NULL;
         }
      }

      // perform file save operation
      $success = $this->processFileSave($uploaded_file, $destination_file, $result);
      
      if($success) {
         // change the file permission
         chmod($destination_file, $this->_chmod);
      }
      else {
         $result->addError('Error saving uploaded file.');
         return NULL;
      }
      
      return $destination_file;
   }
}
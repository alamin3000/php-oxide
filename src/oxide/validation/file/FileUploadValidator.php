<?php
namespace oxide\validation\file;
use oxide\validation\Result;
use oxide\validation\ValidatorAbstract;

class FileUploadValidator extends ValidatorAbstract
{
   protected
           $_allowedext = [],
           $_min_filesize = null,
           $_max_filesize = null;
           
   
   /**
    * 
    * @param array $allowed_file_types allowed mime types ie( image/png)
    * @param type $max_filesize maximum file size in bytes
    * @param type $min_filesize minimum file size in bytes
    */
   public function __construct(array $allowed_file_types = null, $max_filesize = null, $min_filesize = null)
   {
      parent::__construct();
      
      if($allowed_file_types) { 
         $allowed_file_types = array_map('trim',$allowed_file_types);
         $this->_allowedext = $allowed_file_types;
      }
      if($max_filesize) $this->_max_filesize = $max_filesize;
      if($min_filesize) $this->_min_filesize = $min_filesize;
   }
   
   public function setMaxFileSize($byte)
   {
      $this->_max_filesize = $byte;
   }
   
   public function setMinFileSize($byte)
   {
      $this->_min_filesize = $byte;
   }
   

   public function validate($value, Result &$result = null)
   {
      if(!$result) {
         $result = new Result();
      }
      
      // first check for standard values
      if(!is_array($value)) {
         $result->addError('Incorrect uploaded information provided.');
         return false;
         
      }
      
      // check for standard upload error messages first
      if(isset($value['error'])) {
         if($value['error'] != UPLOAD_ERR_OK) {
            // there is an error
            // we will add the appropriate error message to the result
            switch ($value['error']) {
               case UPLOAD_ERR_INI_SIZE:
                  $result->addError('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
                  break;

               case UPLOAD_ERR_FORM_SIZE:
                  $result->addError('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
                  break;

               case UPLOAD_ERR_PARTIAL:
                  $result->addError('The uploaded file was only partially uploaded.');
                  break;

               case UPLOAD_ERR_NO_FILE:
                  $result->addError('No file was uploaded.');
                  break;

               case UPLOAD_ERR_NO_TMP_DIR:
                  $result->addError('Missing a temporary folder.');
                  break;

               case UPLOAD_ERR_CANT_WRITE:
                  $result->addError('Failed to write file to disk.');
                  break;

               case UPLOAD_ERR_EXTENSION:
                  $result->addError('A PHP extension stopped the file upload.');
                  break;

               default:
                  $result->addError('Unknown upload error occured.');
                  break;
            }
            
            
            return false;
         }
      } else {
         // error entry isn't there.
         // some thing is up
         $result->addError('Invalid file input information provided.');
         return false;
      }
      
      $filename = $value['tmp_name'];
      
      // now check for file type errors
      $allowedext = $this->_allowedext;
      if($allowedext) {
         // check against provided extensions
         $finfo = finfo_open(FILEINFO_MIME_TYPE);
         $mime=finfo_file($finfo, $filename);
         finfo_close($finfo);         
                  
         if(!in_array($mime, $allowedext)) {
            $result->addError('File type is not supported/allowed.');
            return false;
         }
      }
      
      
      // now check for filesize limit constrain, if any
      if($this->_min_filesize || $this->_max_filesize) {
         $filesize = $value['size'];      
//         $filesize = filesize($filename);

         if($this->_max_filesize && $filesize > $this->_max_filesize) {
            // error, file size is too big 
            $result->addError('File size is too big.');
            return false;
         }

         if($this->_min_filesize && $filesize < $this->_min_filesize) {
            // error, file size is too small
            $result->addError('File size is too small.');
            return false;
         }
      }

      return true;
   }
}
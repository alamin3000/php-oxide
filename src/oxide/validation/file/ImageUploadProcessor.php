<?php
namespace oxide\validation\file;
use oxide\ui\gd\Image;


/**
 * Image upload processor
 * 
 * Performs actual image save processing
 */
class ImageUploadProcessor extends FileUploadProcessor {
   protected 
      $_image_width = 0,
      $_image_height= 0;


   /**
    * Create new Image upload processor
    * 
    * @param string $upload_dir
    * @param bool $allow_file_override
    * @param int $width
    * @param int $height
    */
   public function __construct($upload_dir, $allow_file_override, $mk_dir = false, $width = null, $height = null) {
      parent::__construct($upload_dir, 0644, $allow_file_override, $mk_dir);
      if($width) $this->_image_width = $width;
      if($height) $this->_image_height = $height;
   }
   
   /**
    * setup size for the image
    * 
    * @param int $width
    * @param int $height
    */
   public function setImageResize($width, $height) {
      $this->_image_width = $width;
      $this->_image_height = $height;
   }
   
   /**
    * 
    * @param type $name
    * @param type $dir
    * @return type
    */
   protected function generateFileName($name, $dir) {
      return parent::generateFileName($name, $dir);
   }
   
   /**
    * 
    * @param type $source_file
    * @param type $destination_file
    * @param \oxide\validation\Result $result
    * @return type
    */
   protected function processFileSave($source_file, $destination_file, \oxide\validation\Result &$result) {
      $image = Image::createFromFile($source_file);
      $success = $image->load($result);
      if($this->_image_width || $this->_image_height) {
         $image->resize($this->_image_width, $this->_image_height);
      }
      
      if($success) {
         $success = $image->output($destination_file);
      }
      
      return $success;
   }
   
   public function __destruct() {
   }
}
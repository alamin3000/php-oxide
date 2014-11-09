<?php
namespace oxide\validation\file;
use oxide\validation\ValidationResult;
use oxide\validation\ValidationComponent;
use oxide\helper\_util;
use oxide\validation\file\FileUploadFilterer;
use oxide\validation\file\ImageUploadValidator;
use oxide\validation\file\ImageUploadProcessor;

class ImageUploadValidation implements ValidationComponent {
   protected 
      $_options = [
          'allowed_mimes' => ["image/gif", "image/jpeg", "image/png"],
          'image_width' => null,
          'image_height' => null,
          'image_min_width' => null,
          'image_min_height' => null,
          'image_max_width' => null,
          'image_max_height' => null,
          'min_filesize' => null,
          'max_filesize' => null,
          'upload_folder' => null
      ];
   
   
   /**
    * 
    * @param array $options options for uploads
    */
   public function __construct(array $options = null) {
      $this->_options = array_merge($this->_options, $options);
   }
   /**
    * 
    * @param type $value
    * @return type
    */
   public function filter($value) {
      $filterer = new FileUploadFilterer();
      return $filterer->filter($value);
   }
   
   /**
    * validation
    * 
    * @param type $value
    * @param \oxide\validation\ValidationResult $result
    * @return type
    */
   public function validate($value, ValidationResult &$result = null) {
      if($this->_options['allowed_mimes']) {
         $mimes = $this->_options['allowed_mimes'];
         if(!is_array($mimes)) {
            $mimes = explode(',', $this->_options['allowed_mimes']);
         }
      } else {
         throw new \Exception("Allowed mime types aren't provided.");
      }
            
      $image_width = $this->_options['image_min_width'];
      $image_height = $this->_options['image_min_height'];
      $max_filesize = $this->_options['max_filesize'];
      $min_filesize = $this->_options['min_filesize'];
      
      $validator = new ImageUploadValidator($mimes, $max_filesize, $min_filesize);
      $validator->setMinImageSize($image_width, $image_height);  
      
      return $validator->validate($value, $result);
   }
   
   /**
    * 
    * @param type $value
    * @param \oxide\validation\ValidationResult $result
    * @return type
    */
   public function process($value, ValidationResult &$result = null) { 
      $options = $this->_options;
      $upload_dir = rtrim(_util::value($options, 'upload_folder', null, true), '/');      
      $width = $options['image_width'];
      $height = $options['image_height'];
      
      // create the processor
      $processor = new ImageUploadProcessor($upload_dir, true, true);
      $processor->setImageResize($width, $height);
      $path = $processor->process($value, $result);
      
      if(!$result->isValid()) {
         return $path;
      }
      
      return $path;
   }
}
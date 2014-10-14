<?php
namespace oxide\ui\misc;
use oxide\ui\html\InputControl;
use oxide\ui\misc\ImageFileControlComponent;
use oxide\util\ArrayString;
use oxide\validation\string\ReplaceFilterer;
use oxide\validation\FilterProcessor;

class ImageUrlUploadControl extends InputControl {
   protected
      $_imageUploadControl = null;
   
   public function __construct($name, $value = null, $label = null, $attrbs = null) {
      parent::__construct(self::TYPE_TEXT, $name, $value, $label, $attrbs);
      
      // now create the image upload control
      $file_control_name = $this->getName().'_file';
      $this->_imageUploadControl = new ImageFileControlComponent($file_control_name);
   }
   
   /**
    * Get the internal image file control component
    * @return ImageFileControlComponent
    */
   public function getImageUploadControl() {
      return $this->_imageUploadControl;
   }
   
   public function setForm(\oxide\ui\html\Form $form = null) {
      parent::setForm($form);
      $validation = $form->getValidationProcessor();
      // we need to make the upload file path relative to the root document, so it can be accessed by website
      $replacefilter = new ReplaceFilterer(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'), '');
      $validation->getProcessorContainer()->addProcessor(
              new FilterProcessor($replacefilter), $this->_imageUploadControl->getName());

      $validation->addProcessCallbacks(null, function(&$values) {
         print 'here';
         $urlname = $this->getName();
         $uploadname = $this->_imageUploadControl->getName();
         $imageurl = null;
         if(!empty($values[$uploadname])) { // upload value submitted
            $imageurl = $values[$uploadname];
         } else {
            $imageurl = $values[$urlname];
         }
         
         $values[$urlname] = $imageurl;
         $this->setValue($imageurl);
      });
   }
   
   protected function onPreRender(ArrayString $buffer) {
      parent::onPreRender($buffer);
      $this->append($this->_imageUploadControl);
   }
}

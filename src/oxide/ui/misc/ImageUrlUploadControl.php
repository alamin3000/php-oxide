<?php
namespace oxide\ui\misc;
use oxide\ui\html\Form;
use oxide\ui\html\InputControl;
use oxide\ui\html\FileControl;
use oxide\util\ArrayString;
use oxide\validation\file\ImageUploadValidation;
use oxide\validation\string\ReplaceFilterer;
use oxide\validation\FilterProcessor;
use oxide\helper\Util;

/**
 * ImageUrlUploadControl
 * 
 * Provides machanism for both image URL or uploading.  Internally it provides two
 * controls, FileControl and InputControl.  
 */
class ImageUrlUploadControl extends InputControl {
   protected
      $_options = null,
      $_imageUploadControl = null;
   
   /**
    * 
    * @param type $name
    * @param type $value
    * @param type $label
    * @param type $attrbs
    */
   public function __construct($name, $value = null, $label = null, $attrbs = null) {
      parent::__construct(self::TYPE_TEXT, $name, $value, $label, $attrbs);
      // now create the image upload control
      $file_control_name = $this->getName().'_file';
      $this->_imageUploadControl = new FileControl($file_control_name);
   }
   
   /**
    * Set options for image uploading
    * @param array $options
    */
   public function setOptions(array $options) {
      $this->_options = $options;
   }
   
   /**
    * Get the internal image file control component
    * @return ImageFileControlComponent
    */
   public function getImageUploadControl() {
      return $this->_imageUploadControl;
   }
   
   /**
    * Adds various validations
    * @param Form $form
    */
   public function setForm(Form $form = null) {
      parent::setForm($form);
      $imgcontrol = $this->getImageUploadControl();
      $imgcontrol->form = $form->id; // add the form 
//      $imgcontrol->setForm($form); // call the file controls set form manually, since it is not being added to the form
      // add validation components for the image control
      $options = $this->_options;
      $validation = $form->getValidationProcessor();
      $validation->addValidationComponent(new ImageUploadValidation($options), $imgcontrol->getName());
      
      // make the uploaded url accessable from the web
      $replacefilter = new ReplaceFilterer(Util::value($options, 'document_root', null,  true), '');
      $validation->getProcessorContainer()->addProcessor(
              new FilterProcessor($replacefilter), $imgcontrol->getName());
      
      // performing post processing
      $validation->addProcessCallbacks(null, function(&$values) use($form) {
         $urlname = $this->getName();
         $uploadname = $this->_imageUploadControl->getName();
         $imageurl = null;
         if(!empty($values[$uploadname])) { // upload value submitted
            $imageurl = $values[$uploadname];
         } else {
            $imageurl = $values[$urlname];
         }
         
         unset($values[$uploadname]);
         $values[$urlname] = $imageurl;
         $form->setValue($urlname, $imageurl);
      });
   }
   
   protected function onPreRender(ArrayString $buffer) {
      parent::onPreRender($buffer);
      $this->append($this->_imageUploadControl);
   }
}

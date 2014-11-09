<?php
namespace oxide\ui\misc;
use oxide\ui\html\Form;
use oxide\ui\html\Fieldset;
use oxide\ui\html\InputControl;
use oxide\helper\_util;
use oxide\validation\string\ReplaceFilterer;
use oxide\validation\FilterProcessor;

/**
 * 
 */
class ImageUploadFieldset extends Fieldset {
   protected 
      /**
       * @var ImageUrlControlComponent 
       */
      $_imageUrlControl = null,
      /**
       * @var ImageFileControlComponent 
       */
      $_imageUploadControl = null,
      /**
       * @var InputControl
       */
      $_hiddenControl = null,
      /**
       * @var FigureElementControl
       */
      $_imageElementControl = null;
      
   /**
    * 
    * @param string $name
    * @param string $values
    * @param string $legend
    * @param array $attributes
    * @param array $options
    */
   public function __construct($name, $values = null, $legend = null, $attributes = null, array $options = null) {
      parent::__construct($name, $values, $legend, $attributes);
      $this->prepare($options);
   }
   
   /**
    * @param \module\content\model\Content $content
    * @param array $options
    */
   protected function prepare(array $args = null) {
      $upload_folder = _util::value($args, 'upload_folder', null);
      $max_size = 1048576 * 2; // 2MB
      $options = array(
          'allowed_mimes' => _util::value($args, 'allowed_mimes', 'image/gif,image/jpeg,image/png'),
          'image_width' => _util::value($args, 'upload_image_width', null),
          'image_height' => _util::value($args, 'upload_image_height', null),
          'image_min_width' => null,
          'image_min_height' => null,
          'image_max_width' => null,
          'image_max_height' => null,
          'min_filesize' => null,
          'max_filesize' => _util::value($args, 'upload_max_size',$max_size),
          'upload_folder' => $upload_folder,
          'current_image_url' => null
      );
      
      $url_control_name = _util::value($args, 'url_control_name', $this->getName() . '_url');
      $file_control_name = _util::value($args, 'file_control_name', $this->getName().'_file');
            
      // create the image url control
      $this->_imageUrlControl = new ImageUrlControlComponent($url_control_name, $this->_value, 'Image Url');
      $this->_imageUrlControl->allowSubmittedValue = false;
      $this->addControl($this->_imageUrlControl);
      
      // now create the image upload control
      $this->_imageUploadControl = new ImageFileControlComponent($file_control_name, null, null, $options);
      $this->addControl($this->_imageUploadControl);
      
      $this->_imageElementControl = new FigureElementControl($this->getName() . '_img', $this->getValue(), 'Current Image');
      $this->addControl($this->_imageElementControl);
      
      $this->_hiddenControl = new InputControl('hidden', $this->getName(), $this->getValue());
      $this->addControl($this->_hiddenControl);
   }
      
   /**
    * Adds the validation components when it is being added to the form
    * 
    * @param \oxide\ui\html\Form $form
    */
   public function setForm(Form $form = null) {
      parent::setForm($form);
      if($form) {
         $validation = $form->getValidationProcessor();
         $replacefilter = new ReplaceFilterer(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'), '');
         $validation->getProcessorContainer()->addProcessor(new FilterProcessor($replacefilter), $this->_imageUploadControl->getName());
         $validation->setProcessCallbacks(null , function(&$values) {
            // we need to copy upload url to the file + image control
            // we also have to update the value 
            $name = $this->getName();
            $name_file = $this->_imageUploadControl->getName();
            $name_url = $this->_imageUrlControl->getName();
            if(isset($values[$name_file])) {
               $url = $values[$name_file];
            } else {
               $url = $values[$name_url];
            }
            
            $values[$name] = $url; // upload the value for saving
            $this->_imageUrlControl->setValue($url); // upload url control for rendering
            $this->_imageElementControl->setValue($url); // upload image for rendering
         });
      }
   }
}
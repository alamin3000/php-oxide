<?php
namespace oxide\ui\misc;
use oxide\ui\html\InputControl;
use oxide\ui\misc\ImageFileControlComponent;
use oxide\util\ArrayString;

class ImageUrlUploadControl extends InputControl {
   protected
      $_imageUploadControl = null;
   
   public function __construct($name, $value = null, $label = null, $options = null, $attrbs = null) {
      parent::__construct(self::TYPE_TEXT, $name, $value, $label, $attrbs);
      
      // now create the image upload control
      $file_control_name = $this->getName().'_file';
      $this->_imageUploadControl = new ImageFileControlComponent($file_control_name, null, null, $options);
      $this->_imageUploadControl->wrapElement->setTag(null);
   }
   
   protected function onPreRender(ArrayString $buffer) {
      parent::onPreRender($buffer);
      $this->append($this->_imageUploadControl);
   }
}

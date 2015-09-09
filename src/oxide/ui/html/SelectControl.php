<?php
namespace oxide\ui\html;
use oxide\base\ArrayString;

/**
 * SelectControl class
 *
 * represents html SELECT form control
 * @package oxide
 * @subpackage ui
 */
class SelectControl extends Control {
   protected
      $optionTag = null,
      $optgroupTag = null;

	/**
	 * construct select control
	 * 
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @param array $data
	 * @param array $attributes
	 */
	public function __construct($name, $value = null, $label = null, $data = null, array $attributes = null) {
      parent::__construct($name, $value, $label, $data, $attributes);      
      $this->setTagName('select');
   }
   
   protected function setName($name) {
      parent::setName($name);
      $this->setAttribute('name', $name);
   }
   
	/**
	 * Set wheather or not user can select multiple options
	 * 
	 * @param bool $bool
	 */
   public function allowMultiple($bool) {
      if($bool) {
         $this->_attributes['name'] = $this->getName() . '[]';
         $this->setAttribute('multiple', 'multiple');
      } else {
         $this->_attributes['name'] = $this->getName();
         $this->removeAttribute('multiple');
      }
   }

	/**
	 * add OPTION items
	 *
	 * $value must be an associative array where key of the array is the
	 * value of the OPTION, and value of the array item is label (text)
	 * if $group is provided, then items will be grouped using OPTGROUP 
	 * @param array $values
	 * @param string $group
	 */
	public function addOptions($values, $group = "") {
		foreach($values as $value => $label) {			
			$this->addOption($value, $label, $group);
		}
	}
   
	/**
	 * add an OPTION item
	 * 
	 * @param string $value
	 * @param string $label
	 * @param string $group
	 */
	public function addOption($value, $label = null, $group = "") {
		if(!$label) {
			$label = $value;
		}
		
		if($group) {
			$this->_data[$group][$label] = $value;
		} else {
			$this->_data[$label] = $value;
		}
	}
   
   public function getOptionTag() {
      if($this->optionTag === null) {
         $this->optionTag = new Tag('option');
      }
      
      return $this->optionTag;
   }
   
   public function getOptGroupTag() {
      if($this->optgroupTag === null) {
         $this->optgroupTag = new Tag('optgroup');
      }
      
      return $this->optgroupTag;
   }
   
   public function renderInner() {
	   if(empty($this->_data)) return '';
	   
	   $values = $this->getValue();
	   if(!is_array($values)) $values = [$values];
	   $values = array_flip($values);	   
	   
	   $buffer = new ArrayString();
	   $tag = $this->getOptionTag();
	   $group = $this->getOptGroupTag();
	   
	   foreach($this->_data as $key => $value) {
		   if(is_array($value)) {
			   // this is group
            $group->setAttribute('label', $key);
			   $buffer[] = $group->renderOpen();
			   foreach($value as $gkey => $gval) {
				   if(isset($values[$gval])) $tag->setAttribute('selected', 'selected');
				   else $tag->removeAttribute('selected');
				   $tag->setAttribute('value', $gval);
				   $buffer[] = $tag->renderWithContent($gkey);
			   }
			   $buffer[] = $group->renderClose();
		   } else {
			   // simple option entry
			   if(isset($values[$value])) $tag->setAttribute('selected', 'selected');
			   else $tag->removeAttribute('selected');
			   $tag->setAttribute('value', $value);
			   $buffer[] = $tag->renderWithContent($key);
		   }
	   }
	   
	   return $buffer->__toString();
   }
}
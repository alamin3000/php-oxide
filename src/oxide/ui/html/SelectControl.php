<?php
namespace oxide\ui\html;
use oxide\util\ArrayString;

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
	 * @param array $items
	 * @param array $attrbs
	 */
	public function __construct($name, $value = null, $label = null) {
      parent::__construct($name, $value, $label);      
      $this->setTag('select');
      $this->_data = [];
   }
   
   /**
    * 
    * @param type $data
    * @throws \Exception
    */
   public function setData($data) {
      if(!is_array($data)) {
         throw new \Exception('Data must be an assoicative array.');
      }
      if(count($data) == count($data, COUNT_RECURSIVE)) {
         $this->addOptions($data, '');
      } else {
         parent::setData($data);
      }
   }
   
	/**
	 * set wheather or not user can select multiple options
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
		
		$this->_data[$group][$value] = $label;
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

   /**
	 * renders inner html
	 * 
	 * @return string
	 */
	public function renderInner() {
      if(empty($this->_data)) return '';
      $buffer = new ArrayString();
      $selected = false;
      $values = $this->getValue();
      $tag = $this->getOptionTag();
      $groupTag = $this->getOptGroupTag();
      if(!is_array($values)) $values = [$values];
      if(!$tag) $tag = new Tag('option');
      if(!$groupTag) $groupTag = new Tag('optgroup');
      
		foreach($this->_data as $grouplabel => $item) {
			$options = ''; // hold rendered item list
			foreach($item as $label => $value) {            
	         if(in_array($value, $values)) $selected = true;
	         else $selected = false;
            
            // build attributes
            $tag->setAttribute('value', $value);
            if($selected) $tag->setAttribute('selected', 'selected');
            else $tag->removeAttribute('selected');
            
            // render the option tag
            $options .= $tag->renderContent($label);
			}
         
			if($grouplabel) {
            // render the optgorup with the items and add to buffer
            $groupTag->setAttribute('label', $grouplabel);
            $buffer->append($groupTag->renderContent($options));
			} else {
            // add the rendered option to the buffer
				$buffer->append($options);
			}
		}
      
      return (string) $buffer;
	}
}
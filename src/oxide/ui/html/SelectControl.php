<?php
namespace oxide\ui\html;
use oxide\util\ArrayString;
use oxide\helper\Html;

/**
 * SelectControl class
 *
 * represents html SELECT form control
 * @package oxide
 * @subpackage ui
 */
class SelectControl extends Control
{
	protected 
      $_items = array(),
      $_groups = array();
   
   public
      /**
       * @var boolean Description
       */
      $useArrayKeyForOptionValue = true;

	/**
	 * construct select control
	 * 
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @param array $items
	 * @param array $attrbs
	 */
	public function __construct($name, $value = null, $label = null, $items = array(), $attrbs = null) {
      parent::__construct('select', $name, $value, $label,  $attrbs);      
      if($items) {
         $this->addItems($items);
      }
      
		if(!is_array($values = $this->getValue())) {
			$values = (array) $values;
		}
   }

	/**
	 * set wheather or not user can select multiple options
	 * 
	 * @param bool $bool
	 */
   public function allowMultiple($bool) {
      if($bool) {
         $this->name = $this->getName() . '[]';
         $this->multiple = 'multiple';
      } else {
         $this->name = $this->getName();
         unset($this->multiple);
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
	public function addItems($values, $group = "") {
		foreach($values as $value => $label) {			
			$this->addItem($value, $label, $group);
		}
	}
   
   public function hasItem($value) {
      foreach($this->_groups as $group => $items) {
         foreach($items as $key => $item) {
            if($item == $value) {
               return true;
            }
         }
      }
      
      return false;
   }

	/**
	 * add an OPTION item
	 * 
	 * @param string $value
	 * @param string $label
	 * @param string $group
	 */
	public function addItem($value, $label = null, $group = "") {
		if(!$label) {
			$label = $value;
		}
		
		$this->_groups[$group][$value] = $label;
	}
   
   
   /**
    * Add item using Element object
    * 
    * You should create Element object using createOptionElement or createOptionGroupElement
    * If you create your own Element object, the tag MUST be option or optgroup
    * @param \oxide\ui\html\Element $item
    * @throws \Exception
    */
   public function add(Element $item) {
      $tag = strtolower($item->tag());
      if($tag != 'option' && $tag != 'optgroup')         
         throw new \Exception('Invalid Element provided.  Only <option> and <optgroup> tags are allowed.');
      
      parent::inner($item);
   }
   
   
   public static function createOptionElement($value, $label = null, array $attr = null) {
      if(empty($label)) { $label = $value; }
      
      $el = new Element('option', $label, $attr);
      $el->value = $value;
      
      return $el;
   }
   
   public static function createOptionGroupElement($label, array $items = null, array $attrib = null) {
      $el = new Element('optgroup', $items, $attrib);
      $el->label = $label;
      
      return $el;
   }
   
   

	/**
	 * renders inner html
	 * 
	 * @return string
	 */
	public function renderInnerTag() {
      if(empty($this->_groups)) return;
      
      if(!is_array($values = $this->getValue())) {
			$values = (array) $values;
		}
      
      $buffer = new ArrayString();
		$selected = false;
		foreach($this->_groups as $grouplabel => $item) {
			$options = ''; // hold rendered item list
			foreach($item as $key => $value) {
            if($this->useArrayKeyForOptionValue) {
               $option_value = $key;
               $option_label = $value;
            } else {
               $option_value = $value;
               $option_label = $key;
            }
            
	         if(in_array($option_value, $values)) { 
	            $selected = true;
	         } else {
	            $selected = false;
	         }
            
            // build attributes
            $attrs = array('value' => $option_value);
            if($selected) $attrs['selected'] = 'selected';
            
            // render the option tag
            $options .= _html::tag('option', $option_label, $attrs);
			}
         
			if($grouplabel) {
            // render the optgorup with the items and add to buffer
            $buffer->append(_html::tag('optgroup', $options, array('label' => $grouplabel)));
			} else {
            // add the rendered option to the buffer
				$buffer->append($options);
			}
		}
      
      return (string) $buffer;
	}
}
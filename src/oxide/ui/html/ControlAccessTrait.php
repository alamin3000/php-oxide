<?php
namespace oxide\ui\html;

trait ControlAccessTrait {
   public function insert($value, $offset = null, $index = null) {
      $this->onControlAdd($value);
      parent::insert($value, $offset, $index);
   }
   
   /**
	 * add a Control to the collection
	 *
	 * @param Control $control
	 */
	public function addControl(Control $control, $allowmultiple = false) {
      $this->onControlAdd($control);
      if(isset($this->_t_array_storage[$control->getName()])) {
         if(!$allowmultiple) {
            throw new \Exception("Control named {$control->getName()} already exists.");
         }
         
         if(is_array($this->_t_array_storage[$control->getName()])) {
            // if this is already array
            // simply add
            $this->_t_array_storage[$control->getName()][] = $control;
         } else {
            // this is not array
            // so we will need to convert into array
            $current = $this->_t_array_storage[$control->getName()];
            $this->_t_array_storage[$control->getName()] = null;
            $this->_t_array_storage[$control->getName()][] = $current;
            $this->_t_array_storage[$control->getName()][] = $control;
         }
      } else {
         $this->_t_array_storage[$control->getName()] = $control;
      }		
	}

	/**
	 * add multiple controls to the collection
	 *
	 * @param array $controls
	 */
	public function addControls(array $controls) {
		foreach($controls as $control) {
			$this->addControl($control);
		}
	}
   
  
   /**
    * Search and find a control given $name
    * 
    * This method will attempt to find the control from all of its sub fieldset, if required.
    * Optional $callback method will be called if control is found
    *    Callback method singature: function(Control $contro, Fieldset $fieldset)
    * @param name $name
    * @param function $callback
    * @return mixed
    */
   public function findControl($name, $callback = null) {
      $control = null;
      
      // first check if we can find the control directly
      if(isset($this->_t_array_storage[$name])) {
         $control = $this->_t_array_storage[$name];
         if($callback) {
            $callback($control, $this);
         }
      } else {
         // we will need to loop through fieldsets
         foreach($this->_t_array_storage as $fieldset) {
            if($fieldset instanceof Fieldset) {
               $control = $fieldset->findControl($name, $callback);
               if($control) {
                  break;
               }
            }
         }
      }
      
      return $control;
   }
   
   /**
    * 
    * @param \oxide\ui\html\Control $control
    * @param type $offset
    * @return type
    */
   public function insertControlAt(Control $control, $offset) {
      return $this->insert($control, $control->name, $offset);
   }
   
   /**
    * 
    * @param \oxide\ui\html\Control $control
    * @param type $offset
    * @return type
    */
   public function insertControlAfter(Control $control, $name) {      
      // get the position of current control
      $position = array_search($name, array_keys($this->toArray()));
      if($position === FALSE) {
      } else {
         $position += 1;
      }
      
      return $this->insert($control, $control->name, $position);
   }
   
   /**
    * 
    * @param \oxide\ui\html\Control $control
    * @param type $offset
    * @return type
    */
   public function insertControlBefore(Control $control, $offset) {      
      // get the position of current control
      $position = array_search($offset, array_keys($this->toArray()));
      if($position === FALSE) {
      } else {
      }
      
      return $this->insert($control, $control->name, $position);
   }
   
   
   
   /**
    * Remove a control by given name from the collection
    * 
    * If success, removed control will be returned.
    * @param string $name
    * @return oxide\ui\html\Control
    */
   public function removeControl($name) {
      return $this->findControl($name, function(Control $control, $fieldset) {
         $this->onControlRemove($control);
         
      });
   }
   
   public function moveControl(Control $el, Element $toElement) {
      $this->findControl($el->getName(), function(Control $control, $container) use ($toElement) {
         $toElement[] = $control;
         unset($container[$control->getName()]);
      });
   }
   
   
   protected function onControlAdd(Control $control) {
      if($this instanceof Form) {
         // adding to the form
         $control->setForm($this);
      } else if($this instanceof Fieldset) {
         $form = $this->getForm();
         if($form) {
            // this is a feildset and already added to form
            // so we will also add the form to new control
            $control->setForm($form);
         }
      }
   }
   
   protected function onControlRemove(Control $control) {
      $control->setForm(null);
   }
}

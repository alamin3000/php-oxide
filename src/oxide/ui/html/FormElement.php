<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui\html;
use oxide\ui\Renderer;

interface FormElement extends Renderer {
   public function setForm(Form $form);
   public function getForm();
   public function setName($name);
   public function getName();
   public function setValue($value);
   public function getValue();
   public function setLabel($label);
   public function getLabel();
   public function setData($data);
   public function getData();
}
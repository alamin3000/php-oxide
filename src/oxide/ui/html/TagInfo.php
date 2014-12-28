<?php

/**
 * Oxide Framework
 * 
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name 
 */

namespace oxide\ui\html;

abstract class TagInfo {
   public static
      $voidTags =  [
          'area' => true,'base' => true, 'br' => true, 'col' => true, 'command' => true, 
          'embed' => true, 'hr' => true, 'img' => true, 'input' => true, 'keygen' => true, 
          'link' => true, 'meta' => true, 'param' => true, 'source' => true, 'track' => true, 'wbr' => true],
           
      $blockTags = [
          'address' => true, 'figcaption' => true, 'ol' => true, 'article' => true, 'figure' => true, 
          'output' => true, 'aside' => true, 'footer' => true, 'p' => true, 'audio' => true, 'form' => true, 
          'pre' => true, 'blockquote' => true, 'h1' => true,'h2' => true,'h3' => true,'h4' => true,'h5' => true,'h6' =>true,
          'section' => true, 'canvas' => true, 'header' => true, 'table' => true, 'dd' => true, 'hgroup' => true, 
          'ul' => true, 'div' => true, 'hr' => true, 'dl' => true, 'video' => true, 'fieldset' => true, 'noscript' => true, 'li' => true],
   
      $inputTypes = ['text' => true, 'submit' => true, 'button' => true, 'password' => true, 
          'hidden' => true, 'radio' => true, 'image' => true, 'checkbox' => true, 'file' => true ,
			 'email' => true, 'url' => true, 'tel' => true, 'number' => true, 'range' => true, 'search' => true, 
          'color' => true, 'datetime' => true, 'date' => true, 'month' => true, 'week' => true, 
          'time' => true, 'datetime-local' => true, 'button' => true],
           
      $controls = ['input', 'textarea', 'select', 'button'];
}
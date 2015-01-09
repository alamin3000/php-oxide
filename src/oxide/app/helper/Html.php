<?php
namespace oxide\app\helper;
use oxide\ui\html\Tag;

/** 
 * html helper class
 *
 * provides various utility functions to create and manupulate HTML
 * ALL print/get methods will print immediately to the screen.
 *
 * Example:
 * <code>
 * <?php
 * // store HTML page title
 * _Html::title('Hello World Sample');
 * ?>
 * <pre>
 *	<html>
 *		<head>
 *			<!-- print the title tag -->
 *			<?php _Html::title() ?>
 *
 *		</head>
 *
 *		<body>
 *			<?php _Html::h1('Hello World') ?>
 *			<?php _Html::p('Example of using Html helper', array('style' => 'color:blue') ?>
 *		</body>
 * </html>
 * </pre>
 * </code>
 */

class Html {
   public static
      $voidTags =  ['area' => true,'base' => true, 'br' => true, 'col' => true, 'command' => true, 'embed' => true, 'hr' => true, 'img' => true, 'input' => true, 'keygen' => true, 'link' => true, 'meta' => true, 'param' => true, 'source' => true, 'track' => true, 'wbr' => true],
      $blockTags = ['address' => true, 'figcaption' => true, 'ol' => true, 'article' => true, 'figure' => true, 'output' => true, 'aside' => true, 'footer' => true, 'p' => true, 'audio' => true, 'form' => true, 'pre' => true, 'blockquote' => true, 'h1' => true,'h2' => true,'h3' => true,'h4' => true,'h5' => true,'h6' =>true, 'section' => true, 'canvas' => true, 'header' => true, 'table' => true, 'dd' => true, 'hgroup' => true, 'ul' => true, 'div' => true, 'hr' => true, 'dl' => true, 'video' => true, 'fieldset' => true, 'noscript' => true, 'li' => true],
      $inputTypes = ['text' => true, 'submit' => true, 'button' => true, 'password' => true, 'hidden' => true, 'radio' => true, 'image' => true, 'checkbox' => true, 'file' => true , 'email' => true, 'url' => true, 'tel' => true, 'number' => true, 'range' => true, 'search' => true, 'color' => true, 'datetime' => true, 'date' => true, 'month' => true, 'week' => true, 'time' => true, 'datetime-local' => true, 'button' => true],
      $controls = ['input', 'textarea', 'select', 'button'];

   /**
    * 
    * @param type $tag
    * @param array $attributes
    * @param type $void
    * @return type
    */
   public function openTag($tag, array $attributes = null, $void = false) {
      return Tag::renderOpenTag($tag, $attributes, $void);
   }
   
   /**
    * 
    * @param type $tag
    * @return type
    */
   public function closeTag($tag, $void = false) {
      return Tag::renderCloseTag($tag, $void);
   }
   
	/**
	 * generate a html tag string based on given information
	 *
	 * $inner (inner html) can be an array, in which case, multiple of same tag
	 * will be created with same $attributes
	 * Example:
	 * <code>
	 * <?php
	 * $messages = array(
	 *		'This is message in first paragraph',
	 *		'This is second paragraph',
	 *		'This is third paragraph);
	 * ?>
	 *
	 *	<?php echo _Html::tag('p', $messages, array('style' => 'color:red')) ?>
	 * </code>
	 * @param string $tag
	 * @param string $inner
	 * @param array $attributes
	 */
	public function tag($tag, $inner = null, array $attributes = null, $void = null) {
		// create the attribute string from the $attributes
      if(is_array($tag)) {
         list($tag, $inner, $attributes) = $tag;
      }
      
      if($void === null) {
         if(isset(self::$voidTags[$tag])) {
            $void = true;
         } else {
            $void = false;
         }
      }
      
      return $this->openTag($tag, $attributes, $void) .
              $this->toString($inner) .
              $this->closeTag($tag, $void);
      
	}
   
   /**
    * Make any content into a string
    * 
    * @param mixed $content
    * @return string
    */
   public function toString($content) {
      if(is_scalar($content)) return (string) $content;
      else if($content instanceof \oxide\ui\Renderer) return $content->render();
      else if($content instanceof \oxide\util\Stringify) return (string)$content;
      else if($content instanceof \Closure) return $content();
      else if(is_array($content)) return implode (' ', $content);
      else if(is_object($content)) return implode (' ', (array) $content);
      else if(is_null($content)) return '';
      else null;
   }
   
   /**
    * this allows to render independed tags
    * 
    * for example:
    * <code>
    * _Html::tags(array(array('p', 'First paragraph', null), array('div', 'Div', null)));
    * </code>
    * @access public
    * @param type $tags 
    */
	public function tags(array $tags) {
      $buffer = '';
		foreach($tags as $tag) {
         if(count($tag) !== 3) throw new \Exception('Tag array is malformed');
			$buffer .= $this->tag($tag[0], $tag[1], $tag[2]);
		}
      return $buffer;
	}

   /**
    * Generates HTML tag attribute string from given array
    *
    * @param array $attributes
    * @return string
    */
   public function attributeString($attributes) {
  		return Tag::renderAttributeString($attributes);
   }
	
	/**
    * creates HTML A tag
    *
	 * @see tag()
    * @param string $link
    * @param string $text
    * @param array $attrib
    */
	public function a($link = null, $text = null, $attrib = null) {
		if($link) {
			if($attrib) {
				$attrib['href'] = $link;
			} else {
				$attrib = ['href' => $link];
			}
		}

		return $this->tag('a', ($text) ? $text: $link, $attrib);
	}
   
   /**
    * Renders a Label tag
    * 
    * @param string $text
    * @param string $for
    * @param array $attribs
    * @return string
    */
   public static function label($text, $for = null, array $attribs = null) {
      if(!$attribs) $attribs = [];
      if($for) $attribs['for'] = $for;
      return $this->tag('label', $text, $attribs);
   }
   
   /**
    * Renders a Input tag
    * 
    * @param type $type
    * @param type $name
    * @param type $value
    * @param type $attribs
    * @return type
    */
   public static function input($type, $name, $value = null, $attribs = null) {
      if(!$attribs) $attribs = [];
      $attribs['name'] = $name;
      $attribs['type'] = $type;
      if($value) $attribs['value'] = $value;
      return $this->tag('input', null,  $attribs);
   }
   
   /**
    * Renderds a Button tag
    * @param type $type
    * @param type $name
    * @param type $value
    * @param type $attribs
    * @return type
    */
   public static function button($type, $name, $value = null, $attribs = null) {
      if(!$attribs) $attribs = [];
      $attribs['name'] = $name;
      $attribs['type'] = $type;
      if($value) $attribs['value'] = $value;
      return $this->tag('button',  $attribs);
	}
   
   /**
    * Renders a textarea tag
    * 
    * @param type $name
    * @param type $value
    * @param array $attribs
    * @return type
    */
   public static function textarea($name, $value = null,  $attribs = null) {
      if(!$attribs) $attribs = [];
      $attrib['name'] = $name;
      return $this->tag('textarea', $value, $attrib);
   }
   
   /**
    * Render a html select element
    * 
    * @param string $name
    * @param string $value
    * @param array $options
    * @param string $label
    * @param array $attrib
    * @return string
    */
   public static function select($name, $value = null, $options = [], $attribs = null) {
      if(!$attribs) $attribs = [];
      $attribs['name'] = $name;
      
      $this->start('select', $attribs);
      foreach($options as $key => $val) {
         if(is_int($key)) {
            $text = $val;
         } else {
            $text = $key;
         }
         if($value == $val) $opt_attrib = ['selected' => 'selected'];
         else $opt_attrib = [];
         
         $opt_attrib['value'] = $val;
         echo $this->tag('option', $text, $opt_attrib);
      }
      return $this->end('select');
   }
   
   /**
    * builds and prints UL tag
    *
    * given $list must be an array or object that is iterable. 
    * $opt value determines how to handle associative array
	 * 
    * @param array $list
    * @param array $attrib
    * @param int $opt
    * @return string
    */
   public function ul($list, $attrib = null, $opt = self::LIST_SMART_LINK) {
      return $this->_list($list, 'ul', $attrib, $opt);
   }

   /**
    * builds and returns OL tag
    * @see ul()
    */
   public function ol($list, $attrib = null, $opt = self::LIST_SMART_LINK) {
      return $this->_list($list, 'ol', $attrib, $opt);
   }
   
   /**
    * build html list (UL/OL)
    * 
    * @param array $list
    * @param string $type must be 'ul' or 'ol'
    * @param array $attrib
    * @param int $opt
    * @return string
    */
   private function _list($list, $type = 'ul', $attrib = null, $opt = self::LIST_VALUE) {
      if(!$list) return;
      
      if(!is_array($list)) {
         // first we will check for some special object those are recognized by the oxide
         if($list instanceof oxide\base\Dictionary) {
            $list = $list->toArray();
         }
         
         else $list = (array) $list;
      }
      
      $this->start($type, $attrib);
      foreach($list as $name => $value) {
         if(is_int($name)) $name = "";

         echo $this->openTag('li');
         if(is_array($value)) {
				if($opt == self::LIST_VALUE_LINK)
					echo $this->a(null, $name);
				else echo $name;
            echo $this->_list($value, $type, null, $opt);
         } else {
            switch($opt) {
               case self::LIST_VALUE_CONCAT:
                  echo "{$name} {$value}";
                  break;

               case self::LIST_VALUE_LINK:
                  if($value)
                     echo $this->a($value, $name);
                  else echo $name;
                  break;

               case self::LIST_VALUE_IGNORE:
                  echo $name;
                  break;
               
               
               case self::LIST_SMART_LINK:
                  // only use link if both link and text available
                  if($name && $value) 
                     echo $this->a($value, $name);
                  // else simply return value
                  else echo "{$name}{$value}";
                  break;
                  

               case self::LIST_VALUE:
               default:
                  echo $value;
                  break;
            }
         }

         echo $this->closeTag('li');
      }
      return $this->end($type);
   }

	/**
	 * print HTML definition list using DL, DT, DD tags
	 *
	 * $list must be an associative array, where key of the array is the term (DT)
	 * and value of the array entry is definition (DD)
	 * @param array $list
	 */
   public function dl($list, $attrib = null) {
      if(!$list) {return;}
      if(!is_array($list) && !is_object($list)) {
         return $list;
      }
      
      $this->start('dl', $attrib);
      foreach($list as $key => $value) {
         if(is_numeric($key)) {
            $key = "";
         }
         echo $this->tag('dt', $key, ['title' => $key]);
         
         if(!is_array($value)) {
            $value = [$value];
         }
         
         foreach($value as $val) {
            echo $this->tag('dd', $val, ['title' => $key]);
         }
      }
      return $this->end('dl');
   }
 
   /**
    * this will output tag start HTML code and stack the tag
    * must call end() to output tag end HTML and balance
    * 
    * @param string $tag name to start
    * @param array $attrib attributes for the tag
    */
   public function start($tag = null, $attrib = null) {
      ob_start();
      if($tag)
         echo $this->openTag($tag, $attrib, false);

   }

	/**
	 * ends the last tag opened using start() method
	 * @see start()
	 */
   public function end($tag = null) {
      if($tag)
         echo $this->closeTag ($tag);
      
      return ob_get_clean();
   }

	/**
	 * escapes html entities
	 * 
	 * @param string $str
	 * @return string
	 */
	public function escape($str) {
      return htmlentities($str, ENT_QUOTES);
   }
   
   /**
    * 
    * @param type $str
    * @param type $encode
    * @return type
    */
   public function encode($str, $encode = 'utf-8') {
      return utf8_encode(htmlentities($str,ENT_QUOTES, $encode));
   }   
      
   /**
   * 
   * @param type $name
   * @param type $arguments
   * @return type
   */
   public function __call($name, $arguments) {
      if(!$arguments) {
         return $this->tag($name, '');
      } else {
         $inner = array_shift($arguments);
         $attributes = (!empty($arguments)) ? array_shift($arguments) : null;
         return $this->tag($name, $inner, $attributes);
      }
   }
}
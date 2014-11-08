<?php
namespace oxide\helper;
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
 * __html::title('Hello World Sample');
 * ?>
 * <pre>
 *	<html>
 *		<head>
 *			<!-- print the title tag -->
 *			<?php __html::title() ?>
 *
 *		</head>
 *
 *		<body>
 *			<?php __html::h1('Hello World') ?>
 *			<?php __html::p('Example of using _html helper', array('style' => 'color:blue') ?>
 *		</body>
 * </html>
 * </pre>
 * </code>
 */
abstract class _html
{
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
   
   protected static
      $renderers = [];
   
	private static
		$metas = array(),
		$scripts = array(),
		$links = array(),
		$title = null,
      $styles = array(),
      $_tagStack = array();
   
   const
      DEFAULT_RENDERER_KEY = '_',
      LIST_VALUE = 1,
      LIST_VALUE_LINK = 2,
      LIST_VALUE_CONCAT = 3,
      LIST_VALUE_IGNORE = 0,
      LIST_KEY = 0,
      LIST_SMART_LINK = 5;

   public static function renderer($tag, \Closure $renderer = null) {
      self::$renderer[$tag] = $renderer;
   }
   
   public static function render($tag, $inner = null, $attributes = null) {
      if(isset(self::$renderers[$tag])) {
         $renderer = self::$renderers[$tag];
      } else {
         $renderer = self::$renderers[self::DEFAULT_RENDERER_KEY];
      }
      
      return $renderer($tag, $inner, $attributes);
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
	 *	<?php echo __html::tag('p', $messages, array('style' => 'color:red')) ?>
	 * </code>
	 * @param string $tag
	 * @param string $inner
	 * @param array $attributes
	 */
	public static function tag($tag, $inner = null, $attributes = null)
	{
		// create the attribute string from the $attributes
      $attribString = self::attributeString($attributes);
      if(is_array($tag)) {
         list($tag, $inner, $attributes) = $tag;
      }
      
      if(isset(self::$voidTags[$tag]) && empty($inner)) {
         // will self close
         return "<{$tag}{$attribString} />" . self::toString($inner);
      } else {
         // full close
         return "<{$tag}{$attribString}>".
                 self::toString($inner).
                 "</{$tag}>";
      }
	}
   
   /**
    * 
    * @param type $content
    * @return string
    * @throws \Exception
    */
   public static function toString($content) {
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
    * __html::tags(array(array('p', 'First paragraph', null), array('div', 'Div', null)));
    * </code>
    * @access public
    * @param type $tags 
    */
	public static function tags($tags)
	{
      $buffer = '';
		foreach($tags as $tag) {
			$buffer .= self::tag($tag[0], $tag[1], $tag[2]);
		}
      return $buffer;
	}

   
   /** 
    *
    * @access public 
    */
	/**
	 * adds a meta name/content value pair
	 * 
	 * @staticvar array $metas
	 * @param string $name
	 * @param string $content
	 */
	public static function meta($name = null, $content = null, $name_key = 'name', $content_key = 'content')
	{
		if($name === null) {
			return self::tags(self::$metas);
		}

		self::$metas[] = ['meta', null, [$name_key => $name, $content_key => $content]];
	}

   
   /**
    *
    * @access public 
    */
	/**
	 * sets the HTTP-EQUIV in the meta tag
	 * 
	 * @staticvar array $https
	 * @param string $name
	 * @param string $content
	 * @return array
	 */
	public static function http($name = null, $content = null)
	{
		self::$metas[] = array('meta', null, array('http-equiv' => $name, 'content' => $content));
	}
   

	/**
	 * print/set current title for html title tag
	 *
	 * if $title is provided, then $title will be saved/stored
	 * if $title is null, then it will return the current title string
	 * @param string $str
	 * @return string
	 */
	public static function title($str = null)
	{
		if($str) {
         self::$title = $str;
		}

      return self::tag('title', self::$title);		
	}


   /**
    * add or print css links for html HEAD tag
    *
    * if $link is given, then it will add the link to the storage
    * if $link is empty, then it will print the links immidately
    * @staticvar array $files
    * @param string $link
    * @param string $media
    */
   public static function link($link = null, $rel = 'stylesheet', $attribs = null)
	{
		if($link === null) {
			return self::tags(self::$links);
		}
		
		if(!$attribs) $attribs = array();
		$attribs['href'] = $link;
		$attribs['rel'] = $rel;
		
		self::$links[] = array('link', null, $attribs);
	}

   

   /**
    * add or print script tags
    *
    * if $src is given, then it will store the javascript link
    * if $src is NOT given, then it will print all current javascript SCRIPT tags immediately
    * @staticvar array $files
    * @param string $src
    * @param string $lang
    */
	public static function script($src = null, $snippet = null, $attribs = null) {      
		if($src === null && $snippet == null) {
			return self::tags(self::$scripts);
		}

		if(!$attribs) $attribs = array();
      if($src) $attribs['src'] = $src;

		self::$scripts[] = array('script', $snippet, $attribs);
	}

   /**
    * Generates HTML tag attribute string from given array
    *
    * @param array $attributes
    * @return string
    */
   public static function attributeString($attributes) {
  		if(empty($attributes)) return '';
		
      $str = '';
      foreach ($attributes as $key => $value) {
         if(!empty($value) && !is_scalar($value)) {
            throw new \Exception('both value for attribute key {' . $key . '} must be scalar data type');
         }
         $value = self::escape($value);
         $str .= "{$key}=\"{$value}\" ";
      }
      
      return ' ' . trim($str);
   }
	

   
   
   
	/**
    * creates HTML A tag
    *
	 * @see tag()
    * @param string $link
    * @param string $text
    * @param array $attrib
    */
	public static function a($link = null, $text = null, $attrib = null) {
		if($link) {
			if($attrib) {
				$attrib['href'] = $link;
			} else {
				$attrib = array('href' => $link);
			}
		}

		return self::tag('a', ($text) ? $text: $link, $attrib);
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
   public static function ul($list, $attrib = null, $opt = self::LIST_SMART_LINK) {
      return self::_list($list, 'ul', $attrib, $opt);
   }

   /**
    * builds and returns OL tag
    * @see ul()
    */
   public static function ol($list, $attrib = null, $opt = self::LIST_SMART_LINK) {
      return self::_list($list, 'ol', $attrib, $opt);
   }
   
   public static function label($text, $for = null, $attribs = null) {
      if(!$attribs) $attribs = [];
      if($for) $attribs['for'] = $for;
      return self::tag('label', $text, $attribs);
   }
   
   public static function input($type, $name, $value = null, $label = null, $attribs = null) {
      if(!$attribs) $attribs = [];
      $attribs['name'] = $name;
      $attribs['type'] = $type;
      if($value) $attribs['value'] = $value;
      if($label) $label = self::label ($label, $name);
      return $label.self::tag('input', null,  $attribs);
   }
   
   public static function button($type, $name, $value = null, $label = null, $attribs = null) {
      if(!$attribs) $attribs = [];
      $attribs['name'] = $name;
      $attribs['type'] = $type;
      if($value) $attribs['value'] = $value;
      return self::tag('button', $label,  $attribs);
	}
   
   public static function textarea($name, $value = null,  $label = null, $attribs = null) {
      if(!$attribs) $attribs = [];
      $attrib['name'] = $name;
      if($label) $label = self::label ($label, $name);
      return $label . self::tag('textarea', $value, $attrib);
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
   public static function select($name, $value = null, $label = null, $options = [], $attribs = null) {
      if(!$attribs) $attribs = [];
      $attribs['name'] = $name;
      
      if($label) {
         $label = self::label($label, $name);
      }
      
      self::start('select', $attribs);
      foreach($options as $key => $val) {
         if(is_int($key)) {
            $text = $val;
         } else {
            $text = $key;
         }
         if($value == $val) $opt_attrib = ['selected' => 'selected'];
         else $opt_attrib = [];
         
         $opt_attrib['value'] = $val;
         echo self::tag('option', $text, $opt_attrib);
      }
      return $label . self::end();
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
   
   private static function _list($list, $type = 'ul', $attrib = null, $opt = self::LIST_VALUE)
   {
      if(!$list) {return;}
      
      if(!is_array($list)) {
         // first we will check for some special object those are recognized by the phpoxide
         if($list instanceof oxide\util\ArrayContainer) {
            $list = $list->toArray();
         }
         
         else $list = (array) $list;
      }
      
      self::start($type, $attrib);
      foreach($list as $name => $value) {
         if(is_int($name)) {
            // simple indexed array
            $name = "";
         }

         self::start('li');
         if(is_array($value)) {
				if($opt == self::LIST_VALUE_LINK)
					echo self::a(null, $name);
				else echo $name;
            echo self::_list($value, $type, null, $opt);
         } else {
            switch($opt) {
               case self::LIST_VALUE_CONCAT:
                  echo "{$name} {$value}";
                  break;

               case self::LIST_VALUE_LINK:
                  if($value)
                     echo self::a($value, $name);
                  else echo $name;
                  break;

               case self::LIST_VALUE_IGNORE:
                  echo $name;
                  break;
               
               
               case self::LIST_SMART_LINK:
                  // only use link if both link and text available
                  if($name && $value) 
                     echo self::a($value, $name);
                  // else simply return value
                  else echo "{$name}{$value}";
                  break;
                  

               case self::LIST_VALUE:
               default:
                  echo $value;
                  break;
            }
         }

         echo self::end();
      }
      return self::end();

   }

	/**
	 * print HTML definition list using DL, DT, DD tags
	 *
	 * $list must be an associative array, where key of the array is the term (DT)
	 * and value of the array entry is definition (DD)
	 * @param array $list
	 */
   public static function dl($list, $attrib = null) {
      if(!$list) {return;}
      if(!is_array($list) && !is_object($list)) {
         return $list;
      }
      
      self::start('dl', $attrib);
      foreach($list as $key => $value) {
         if(is_numeric($key)) {
            $key = "";
         }
         echo self::tag('dt', $key, array('title' => $key));
         
         if(!is_array($value)) {
            $value = [$value];
         }
         
         foreach($value as $val) {
            echo self::tag('dd', $val, array('title' => $key));
         }
      }
      return self::end();
   }
   
   /**
    * Renders and output the start tag
    * 
    * @param type $tag
    * @param type $attrib
    * @return type
    */
   public static function rstart($tag, $attrib = null) {
      // if tag is empty,
      // we will self close it.
      // this way it is HTML5 and XML valid at the same time
      $close_tag = '';
      if(isset(self::$voidTags[$tag]))  $close_tag = " /";

      // rendering the markup
      return sprintf('<%s%s%s>', 
         $tag, 
         self::attributeString($attrib),
         $close_tag);

   }
   
   public static function rend($tag) {
      if(isset(self::$voidTags[$tag])) return '';

      return "</{$tag}>";
   }

   /**
    * this will output tag start HTML code and stack the tag
    * must call end() to output tag end HTML and balance
    * 
    * @param string $tag name to start
    * @param array $attrib attributes for the tag
    */
   public static function start($tag = '', $attrib = null) {
      self::$_tagStack[] = $tag;
      ob_start();
      if($tag)
         printf("<%s%s>", $tag, self::attributeString($attrib));

   }

	/**
	 * ends the last tag opened using start() method
	 * @see start()
	 */
   public static function end() {
      $tag = array_pop(self::$_tagStack);
      if($tag)
         printf("</%s>", $tag);
      return ob_get_clean();
   }

	/**
	 * escapes html entities
	 * 
	 * @param string $str
	 * @return string
	 */
	public static function escape($str) {
      return htmlentities($str, ENT_QUOTES);
   }
   
   public static function encode($str, $encode = 'utf-8') {
      return utf8_encode(htmlentities($str,ENT_QUOTES, $encode));
   }   
      
   /**
   * 
   * @param type $name
   * @param type $arguments
   * @return type
   */
   public static function __callStatic($name, $arguments) {
      if(!$arguments) {
         return self::tag($name, '');
      } else {
         $inner = array_shift($arguments);
         $attributes = (!empty($arguments)) ? array_shift($arguments) : null;
         return self::tag($name, $inner, $attributes);
      }
   }
}
<?php
namespace oxide\app\helper;
use oxide\ui\html\Form;
use oxide\ui\html\Fieldset;
use oxide\ui\html\ButtonControl;
use oxide\util\ArrayString;
use oxide\ui\html\Control;


class Ui extends Html {
   
   protected
      $_head = null,
      $_attributes = [],
      $_wrappers = [],
      $_renderers = [];
   
   const
      STYLE_DEFAULT = 0,
      STYLE_PRIMARY = 1,
      STYLE_ALERT = 2,
      STYLE_SUCCESS = 3,
      STYLE_INFO = 4,
      STYLE_NONE = 5,
      STYLE_ERROR = 7,
      STYLE_WARNING = 8,
           
      FORM_STANDARD = 10,
      FORM_INLINE = 11,
           
      TABLE_STRIPED = 1,
      TABLE_HOVERED = 2,
      TABLE_BORDERED = 4,
      
      IMG_STANDARD = 1,
      IMG_ROUNDED = 2,
      IMG_THUMBNAIL = 4,
      IMG_RESPONSIVE = 8,
      IMG_MEDIA = 16,
           
      NAV_DEFAULT = 0,
      NAV_TABS = 1,
      NAV_PILLS = 2,
      NAV_NAVBAR = 3,
           
      LIST_NONE = 0,
      LIST_STANDARD = 1,
      LIST_INLINE = 2,
      LIST_LINK = 4,
      LIST_HORIZONTAL = 8,
      LIST_VERTICAL = 16,
      LIST_ORDERED = 32,
      LIST_UNORDERED = 64,
      LIST_LINK_KEY = 128,
      LIST_LINK_VALUE = 256,
      LIST_LINK_NONE = 512,

      SIZE_DEFAULT = 0,
      SIZE_EX_SMALL = 1,
      SIZE_SMALL = 2,
      SIZE_LARGE = 3;
   
   public function __construct(HelperContainer $c) {
      $this->_head = $c->get('head');
   }
   
   protected function _class_style($style, $prefix) {
      switch ($style) {
         case self::STYLE_PRIMARY:
            return "{$prefix}-primary";
         case self::STYLE_ERROR:
            return "{$prefix}-danger";
         case self::STYLE_SUCCESS:
            return "{$prefix}-success";
         case self::STYLE_INFO:
         case self::STYLE_ALERT:
            return "{$prefix}-info";
         case self::STYLE_DEFAULT:
         default:
            return "{$prefix}-default";
      }
   }
   
   protected function _class_size($size, $prefix) {
      if($size == self::SIZE_EX_SMALL) {
         return "{$prefix}-xs";
      } else if($size == self::SIZE_SMALL) {
         return "{$prefix}-sm";
      } else if($size == self::SIZE_LARGE) {
         return "{$prefix}-lg";
      } else {
         return '';
      }
   }
   
   /**
    * Text label
    * @param type $text
    * @param type $style
    * @return type
    */
   public function textLabel($text, $style = null, array $attrib = null) {
      $attrbs = [
          'class' => 'label ' . self::_class_style($style, 'label')
      ];
      return $this->tag('span', $text, $attrbs);
   }
   
   /**
    * Icon/glyphicon
    * 
    * @param string $name
    * @return string
    */
   public function icon($name) {
      return $this->tag('span', null, ['class' => 'glyphicon glyphicon-' . $name]);
   }

   /**
    * Creates a data table
    * 
    * @param array $data
    * @param array $cols
    * @param \Closure $thcallback
    * @param \Closure $tdcallback
    * @return type
    */
   public function table(array $data, array $cols = null, \Closure $thcallback = null, \Closure $tdcallback = null, $style = null) {
      $this->tableStart($style);
      if(!$thcallback) { // default header callback
         $thcallback = function($key) {
            return ucwords(str_replace(['-', '_'], ' ', $key));
         };
      }
      
      if(!$tdcallback) { // default row columns callback
         $tdcallback = function($key, $row) {
            return (isset($row[$key])) ? $row[$key] : '';
         };
      }
      echo '<thead>', '<tr>';
      foreach($cols as $key) {
         echo '<th>' . $thcallback($key) . '</th>';
      }
      echo '</tr>' , '</thead>', '<tbody>';
      foreach($data as $row) {
         echo '<tr>';
         foreach($cols as $key) {
            echo '<td>' , $tdcallback($key, $row) , '</td>';
         }
         echo '</tr>';
      }
      echo '</tbody>';
      
      return $this->tableEnd();
   }
   
   /**
    * Start creating table
    * @param int $style
    */
   public function tableStart($style = null) {
      $cls = ['table'];
      $cls[] = 'table-condensed';
      if($style & self::TABLE_HOVERED) $cls[] = 'table-hover';
      if($style & self::TABLE_STRIPED) $cls[] = 'table-striped';      
      if($style & self::TABLE_BORDERED) $cls[] = 'table-bordered';
      
      $this->start('table', ['class' => implode(' ', $cls)]);
   }
   
   /**
    * End table. Must be echoed.
    * 
    * @return string
    */
   public function tableEnd() {
      return $this->end('table');
   }
   
   
   /**
    * Prints heading
    * 
    * @param string $main
    * @param string $secondary
    * @return string
    */
   public function heading($main, $secondary = null, $outline_level = 1) {
      if($secondary) $h2 = '<br/>'.$this->_htmlHelper->tag('small', $secondary);
      else $h2 = null;
      
      return $this->tag("h{$outline_level}", $main . $h2);
   }
   
   /**
    * Renders a html link
    * 
    * @param type $link
    * @param type $text
    * @return type
    */
   public function link($link, $text = null) {
      return $this->tag('a', $text, ['href' => $link]);
   }
   
   /**
    * 
    * @param type $href
    * @param type $text
    * @param type $style
    * @param type $size
    * @param type $attribs
    * @return type
    */
   public function linkButton($href, $text, $style = null, $size = null) {
      $attribs = [];
      $cls_size = $this->_class_size($size, 'btn');
      $cls_style = $this->_class_style($style, 'btn');
      
      $attribs['class'] = "btn {$cls_size} {$cls_style}";
      $attribs['herf'] = $href;
      return $this->tag('a', $text, $attribs);
   }
   
   /**
    * Render a button
    * @param type $type
    * @param type $text
    * @param type $style
    * @param type $size
    * @return type
    */
   public function button($type, $name, $text = null, array $attribs = null, $style = null, $size = null) {
      $cls_size = $this->_class_size($size, 'btn');
      $cls_style = $this->_class_style($style, 'btn');
      if(!$attribs) $attribs = [];
      $attribs['name'] = $name;
      $attribs['type'] = $type;
      $attribs['class'] = "btn {$cls_size} {$cls_style}";
      return $this->tag('button', $text, $attribs);
   }
   
   /**
    * 
    * @param type $text
    * @param type $for
    */
   public function label($text, $for = null, array $attribs = null) {
      if(!$attribs) $attribs = [];
      $attribs['class'] = 'form-label';
      $attribs['for'] = $for;
      echo $this->tag('label', $text, $attribs);
   }
   
   /**
    * 
    * @param type $type
    * @param type $name
    * @param type $value
    * @param type $label
    * @return type
    */
   public function input($type, $name, $value = null, array $attribs = null) {
      if(!$attribs) $attribs = [];
      $attribs['type'] = $type;
      $attribs['name'] = $name; 
      $attribs['value'] = $this->encode($value);
      $attribs['class'] = 'form-control input-sm';
      return $this->tag('input', null, $attribs);
   }
   
   /**
    * 
    * @param type $name
    * @param type $value
    * @param type $label
    */
   public function textfield($name, $value = null, array $attribs = null) {
      if(!$attribs) $attribs = [];
      $attribs['class'] = 'form-control input-sm';
      $attribs['name'] = $name;
      $this->tag('textarea', $value, $attribs);
   }
   
   /**
    * 
    * @param type $name
    * @param type $value
    * @param type $label
    * @param array $items
    * @return type
    */
   public function select($name, $value = null, array $items = null, array $attribs = null) {
      if(!$attribs) $attribs = [];
      $attribs['class'] = 'form-control input-sm';
      $attribs['name'] = $name;
      
      $this->start('select', $attribs);
      foreach($items as $key => $val) {
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
    * Start a form element
    * 
    * @param type $style
    * @param type $method
    * @param type $action
    */
   public function formStart($style = null, $method = 'get', $action = null, array $attr = null) {
      if(!$attr) { $attr = []; }
      $attr['role'] = 'form';
      $attr['method'] = $method;
      $attr['action'] = $action;
   
      
      if($style == self::FORM_INLINE) {
         $attr['class'] = 'form-inline';
      } else if($style == self::FORM_STANDARD) {
         $attr['class'] = 'form-horizontal';
      }      
      
      $this->start('form', $attr);
   }
   
   /**
    * Starting a form row 
    * @return void Doesn't return anything.  Everything is buffered.
    */
   public function formRowStart() {
      $this->start('div', ['class' => 'form-group form-group-sm']);
   }
   
   /**
    * 
    * @return string returns the buffered 
    */
   public function formRowEnd() {
      return $this->end('div');
   }
  
   /**
    * 
    * @param type $type
    * @param type $name
    * @param type $value
    * @param array $options
    * @param array $attribs
    * @return string
    */
   public function control($type, $name, $value = null, $inner = null, array $options = null, array $attribs = null) {
      switch($type) {
         case 'input':
            $itype = ($attribs && isset($attribs['type'])) ? $attribs['type'] : 'text';
            return $this->input($itype, $name, $value, $attribs);
         case 'textfield':
            return $this->textarea($name, $value, $attribs);
         case 'select':
            return $this->select($name, $value, $options, $attribs);
         case 'button':
         case 'submit':
         case 'reset':
            return $this->button($type, $name, $value, $inner, $attribs);  
         case (isset(self::$inputTypes[$type])):
            return $this->input($type, $name, $value, $attribs);            
         default:
            return '';
      }
   }
   
   public function formControlElement(Control $control) {
      $tag = $control->getTag();
      $name = $control->getName();
      $value = $control->getValue();
      $attributes = $control->getAttributes();
      $inner = $control->getHtml();
      switch($tag) {
         case 'input':
            $type = (isset($control->type)) ? $control->type : 'text';
            return $this->input($type, $name, $value, $attributes);
         case 'button':
         case 'submit':
         case 'reset':
            $type = (isset($control->type)) ? $control->type : 'text';
            return $this->button($type, $name, $value, $inner, $attributes);
         case 'textfield':
            return $this->textfield($name, $value, $attributes);
      }
   }
   
   public function formEnd() {
      return $this->end('form');
   }
   
   public function formTab(Form $form) {
      $form->registerRenderCallback(function(Form $form, ArrayString $buffer) {
         $tabid = $form->id . '-tab';
         $ul = new Element('ul', null, ['id' => $tabid, 'role' => 'tablist', 'class' => 'nav nav-tabs']);
         $div = new Element('div', null, ['class' => 'tab-content']);
         $count = 0; 
         foreach($form->inner() as $inner) {
            if($inner instanceof Fieldset) {
               if($count === 0) {
                  $active = ' active';
               } else {
                  $active = '';
               }
               $count++;
               $tabname = $inner->getName() . '-tab';
               $a = $this->a("#".$tabname, $inner->getLabel(),['role' => 'tab', 'data-toggle' => 'tab']);
               $ul->inner($this->tag('li', $a, ['class' => $active]));
               $inner->wrapElement->tag('div');
               $inner->wrapElement->id = $tabname;
               $inner->wrapElement->class = 'tab-pane' . $active;
               $inner->setLabel(null);
               $form->moveControl($inner, $div);
            }
         }
         $form->prepend($div);
         $form->prepend($ul);
      });
   }
   
   /**
    * 
    * @param Form $form
    * @return Form
    */
   public function formElement(Form $form, $style = null, $size = null) {
      $form->getErrorTag()->class = 'alert alert-danger';
      $form->getSuccessTag()->class = 'alert alert-success';
      
      $callback = function(Control $control) {
         $this->formRowStart();
         echo $this->label($control->getLabel(), $control->getName());
         echo $this->formControlElement($control);
         
         
         return $this->formRowEnd();
      };
      
      foreach($form->getControls() as $control) {
         $control->rendererCallback($callback);
      }
      
      
//      $form->controlPreparedCallback(function(Control $control) {
//         $control->class = 'form-control col-sm-10';
//         $control->getLabelTag()->class = 'control-label col-sm-2';
//         $info = $control->getInfoTag();
//         $info->class = 'help-block';
//         $error = $control->getErrorTag();
//         $error->setTag('div');
//         $error->class = 'alert alert-danger';
//         if($control instanceof ButtonControl) {
//            $control->class = 'btn btn-primary';
//         }
//         
//         $control->getWrapperTag()->class = 'form-group form-group-sm';
//         if($control->getError()) {
//            $control->getWrapperTag()->class .= ' has-error';
//         }
//      });
               
      if($style == self::FORM_INLINE) {
         $form->class = 'form-inline';
      } else if($style == self::FORM_STANDARD) {
         $form->class = 'form-horizontal';
      }
      
      return $form->render();
   }
   
   /**
    * 
    * @param array $items
    * @param type $style
    * @return string
    */
   public function listing(array $items, $style = null) {
      if($style) {
         
      } else {
         $style = self::LIST_VERTICAL | self::LIST_UNORDERED;
      }
      
      $tag = 'ul';
      if($style & self::LIST_ORDERED) $tag = 'ol';
      else if($style & self::LIST_UNORDERED) $tag = 'ul';
      
      
      $this->start($tag);
      foreach($items as $key => $value) {
         echo "<li>";
         if($style & self::LIST_LINK_KEY) {
            echo $this->link($key, $value);
         } else if($style & self::LIST_LINK_VALUE) {
            echo $this->link($value, $key);
         } else {
            echo $value;
         }
         
         echo "</li>";
      }
      
      return $this->end($tag);
   }
   
   /**
    * 
    * @param type $items
    * @param type $style
    * @return type
    */
   public static function navigationList($items, $style = null, $activeUrl = null) {
      $this->start('div', ['class' => 'list-group']);
      $attrs = ['class' => 'list-group-item'];
      foreach($items as $key => $value) {
         if(is_array($value)) {
            unset($attrs['href']);
            echo $this->tag('span', $key,$attrs);
            echo $this->navList($value, $style, $activeUrl);
         } else {
            if($value) $attrs['href'] = $value;
            else unset($attrs['href']);

            if(stripos($value, $activeUrl)) {
               $attrs['class'] .= ' active';
            }

            echo $this->tag('a', $key, $attrs);
         }
      }
      
      return $this->end('div');
   }
   
   /**
    * Renders a navigation list
    * 
    * Navigation lists are used for page content navigation
    * @param type $items
    * @param type $active
    * @param type $style
    * @return type
    */
   public function navigationBar($items, $active = null, $style = null) {
      $cls = ['nav'];
      if($style & self::NAV_NAVBAR) $cls[] = 'navbar-nav';
      else if($style & self::NAV_PILLS) $cls[] = 'nav-pills';
      else if($style & self::NAV_TABS) $cls[] = 'nav-tabs';
      $this->start('ul', ['class' => implode(' ' , $cls)]);
      foreach( $items as $key => $link) {
         if($active && $key == $active) echo '<li class="active">';
         else echo '<li>';
         echo $this->link($link, $key);
         echo '</li>';
      }
      
      return $this->end('ul');
   }

   
      
   /**
    * Renders a definition list
    * 
    * @param array $list
    * @param int $style
    * @return string
    */
   public function dl($list, $style = null) {
      static $inline_enabled = false;
      
      if(!$list) {return;}
      if(!is_array($list) && !is_object($list)) {
         return $list;
      }
      
      $cls = null;
      if($style & self::LIST_STANDARD) $cls = 'dl-horizontal';
      else if($style & self::LIST_INLINE) { 
         $cls = 'dl-inline';
         if(!$inline_enabled) {
            Template::styles('.dl-inline dt, .dl-inline dd', [
                'display' => 'inline-block',
                'margin-right' => '4px'
            ]);
            $inline_enabled = true;
         }
      }
      
      $attrib = [
          'class' => $cls
      ];
      
      $this->start('dl', $attrib);
      foreach($list as $key => $value) {
         if(is_numeric($key)) {
            $key = "";
         }
         
         echo $this->tag('dt', $key, array('title' => $key));
         
         if(!is_array($value)) {
            $value = [$value];
         }
         
         foreach($value as $val) {
            echo $this->tag('dd', $val, array('title' => $key));
         }
      }
      return $this->end('dl');
   }
   
   
   /**
    * Renders a panel with given $body, $header and $footer
    * 
    * @param string $body
    * @param string $header
    * @param string $footer
    * @return string
    */
   public function panel($body, $header = null, $footer = null) {
      $this->panelStart();
      if($header)
         echo $this->panelHeader($header);
      echo $this->panelBody($body);
      if($footer)
         echo $this->panelFooter($footer);
      return $this->panelEnd();
   }
   
   /**
    * Starts a panel
    */
   public function panelStart() {
      $this->start('div', ['class' => 'panel panel-default']);
   }
   
   /**
    * Renders panel header
    * 
    * @param string $title
    * @return string
    */
   public function panelHeader($title) {
      return $this->tag('div', $title, ['class' => 'panel-heading']);
   }
   
   /**
    * Renders panel body
    * 
    * @param string $body
    * @return string
    */
   public function panelBody($body) {
      return $this->tag('div', $body, ['class' => 'panel-body']);
   }
   
   /**
    * Starts panel body
    */
   public function panelBodyStart() {
      $this->start('div',  ['class' => 'panel-body']);
   }
   
   /**
    * ends panel body
    * 
    * @return string
    */
   public function panelBodyEnd() {
      return $this->end('div');
   }
   
   /**
    * Renders panel footer
    * 
    * @param string $html
    * @return string
    */
   public function panelFooter($html) {
      return $this->tag('div', $html, ['class' => 'panel-footer']);
   }
   
   /**
    * End the panel
    */
   public function panelEnd() {
      return $this->end('div');
   }
   
   /**
    * Given $items will used to create the breadcrumb. Order of items will be used.
    * 
    * @param array $items
    * @return string
    */
   public function breadcrumb($items) {
      $this->start('ol', ['class' => 'breadcrumb']);
      $count = count($items);
      for($i = 0; $i < $count; $i++) {
         list($key, $link) = each($items); 
         if($i == $count - 1 || empty($link)) echo $this->tag('li', $key, ['class' => 'active']);
         else echo $this->tag('li', $this->tag('a', $key, ['href' => $link]));
      }
      return $this->end('ol');
   }
   
   /**
    * Prints pagination
    * 
    * @param int $pagecount
    * @param string $querykey
    * @param int $printcount
    * @return type
    */
   public function pagination($pagecount, $urlprefix, $querykey = 'p', $printcount = 5) {
      if(!$querykey) $querykey = 'p';
      $currentpage = Url::query($querykey, 1);
      if($currentpage < 1) $currentpage = 1;
      $start = -1;
      $end = -1;
		$mean = floor($printcount/2);
      
      $linkmake = function($text, $link_page = null, $class = null) use ($urlprefix) {
         if($class) echo '<li class="'.$class.'">';
         else echo '<li>';
         if($link_page) {
            $href = "{$urlprefix}p={$link_page}";
            echo $this->link($href, $text);
         } else {
            echo '<span>'.$text.'</span>';
         }
         echo '</li>';
      };
		
		if($currentpage <= $printcount) {
			$start = 1;
			if($pagecount < $printcount)  $end = $pagecount;
			else $end = $printcount;
		} elseif($currentpage > $printcount) {
			$start = $currentpage - $mean;
			if($pagecount < ($currentpage + $printcount))  $end = $pagecount;
			else $end = $currentpage + $mean;
		}	
		
      $this->start('ul', ['class' => 'pagination']);
		// previous link.
		if($currentpage > 1) $linkmake('&laquo;', $currentpage - 1);
				
		// page number links
		for($i = $start; $i <= $end; $i++) {
         if($i == $currentpage) $linkmake($i, null, 'active');
         else $linkmake($i, $i);
		}
      
      // last link
		if($end < $pagecount) {
         $linkmake('&hellip;', null, 'disabled');
         $linkmake($pagecount,$pagecount);
		}
      
		// next link
		if($currentpage < $pagecount) {
         $linkmake('&raquo;', $currentpage + 1);
		}
      return $this->end('ul');
   }
   
   /**
    * Image
    * 
    * @param string $src
    * @param string $title
    * @param int $size
    * @param int $style
    * @return type
    */
   public function img($src, $title = null, $size = null, $style = null) {
      $attr = [
          'src' => $src,
          'title' => $title
      ];
      
      $cls = [];
      if($style & self::IMG_ROUNDED) $cls[] = 'img-rounded';
      if($style & self::IMG_THUMBNAIL) $cls[] = 'img-thumbnail';
      if($style & self::IMG_RESPONSIVE) $cls[] = 'img-responsive';
      if($style & self::IMG_MEDIA) $cls[] = 'media-object';
      $attr['class'] = implode(' ', $cls);
      if($size) {
         if(is_array($size)) {
            $attr['width'] = $size[0];
            $attr['height'] = $size[1];
         } else {
            $attr['width'] = $size;
         }
      }
      return $this->tag('img', null, $attr);
   }
   
   /**
    * Starts a responsive grid system
    */
   public function gridRowOpen() {
      echo $this->openTag('div', ['class' => 'row']);
   }
   
   /**
    * Starts a responsive grid
    * 
    * @param int $lg_cols number of cols the large screen should take
    * @param int $md_cols number of cols medium screeen should take
    * @param int $sm_cols number of cols small screen should take
    */
   public function gridColumnOpen($lg_cols, $md_cols, $sm_cols = 1) {
      $this->start('div', ['class' => "col col-lg-{$lg_cols} col-md-{$md_cols} col-sm-{$sm_cols}"]);
   }
   
   /**
    * Ends the responsive grid
    */
   public function gridColumnClose() {
      echo $this->end('div');
   }
   
   /**
    * Ends the responseive grid system
    */
   public function gridRowClose() {
      echo $this->closeTag('div');
   }
   
   /**
    * Prints a a media component box
    * 
    * @param type $src
    * @param type $title
    * @param type $description
    * @return type
    */
   public function media($src, $title, $description = null) {
      $this->start('div', ['class' => 'media']);
      echo $this->tag('a', $this->img($src, $title, null, self::IMG_MEDIA), ['class' => 'pull-left']);
      echo $this->tag('div', 
         $this->tag('h4', $title, ['class' => 'media-heading']) .  
         $description
         , ['class' => 'media-body']);
      return $this->end('div');
   }
   
   /**
    * Prints an alert message box with given $message
    * 
    * @param string $message
    * @param int $style
    * @param boolean $allowdismiss
    * @return string
    */
   public function message($message, $style = self::STYLE_ALERT, $allowdismiss = false) {
      $cls = 'alert';
      $cls .= ' ' . $this->_class_style($style, 'alert');
      return $this->tag('div', $message, ['class' => "{$cls}", 'role' => 'alert']);
   }
}
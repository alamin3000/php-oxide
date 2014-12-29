<?php
namespace oxide\helper;
use oxide\helper\_html;
use oxide\ui\html\Form;
use oxide\ui\html\Fieldset;
use oxide\ui\html\ButtonControl;
use oxide\util\ArrayString;

class Ui {
   use \oxide\base\pattern\SingletonTrait;
   
   protected
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
   
   
   protected function _class_style($style, $prefix) {
      switch ($style) {
         case self::STYLE_PRIMARY:
            return "{$prefix}-primary";
         case self::STYLE_ERROR:
            return "{$prefix}-danger";
         case self::STYLE_SUCCESS:
            return "{$prefix}-success";
         case self::STYLE_INFO:
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
   public function text_label($text, $style = null) {
      $attrbs = [
          'class' => 'label ' . self::_class_style($style, 'label')
      ];
      return Html::tag('span', $text, $attrbs);
   }
   
   /**
    * Icon/glyphicon
    * 
    * @param string $name
    * @return string
    */
   public function icon($name) {
      return Html::tag('span', null, ['class' => 'glyphicon glyphicon-' . $name]);
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
   public static function table(array $data, array $cols = null, \Closure $thcallback = null, \Closure $tdcallback = null) {
      Html::start('table', ['class' => 'table']);
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
      
      return Html::end();
   }
   
   /**
    * Start creating table
    * @param int $style
    */
   public static function table_start($style = null) {
      $cls = ['table'];
      $cls[] = 'table-condensed';
      if($style & self::TABLE_HOVERED) $cls[] = 'table-hover';
      if($style & self::TABLE_STRIPED) $cls[] = 'table-striped';      
      if($style & self::TABLE_BORDERED) $cls[] = 'table-bordered';
      
      Html::start('table', ['class' => implode(' ', $cls)]);
   }
   
   /**
    * End table. Must be echoed.
    * @return string
    */
   public static function table_end() {
      return Html::end();
   }
   
   /**
    * 
    * @param Form $controls
    * @param array $attrs
    * @return type
    */
   public static function form($controls, array $attrs = null) {
      if($controls instanceof Form) return self::form_element($controls);
      
      Html::start('form', $attrs);
      foreach($controls as $name => $control) {
         
      }
      
      return Html::end();
   }
   
   
   /**
    * Prints heading
    * @param string $main
    * @param string $secondary
    * @return string
    */
   public static function heading($main, $secondary = null, $outline_level = 1) {
      if($secondary) $h2 = '<br/>'.Html::tag('small', $secondary);
      else $h2 = null;
      
      return Html::tag("h{$outline_level}", $main . $h2);
   }
   
   
   public static function heading_content($main, $sub = null) {
      
   }
   
   /**
    * 
    * @param type $link
    * @param type $text
    * @return type
    */
   public static function link($link, $text = null) {
      return Html::a($link, $text);
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
   public static function link_button($href, $text, $style = null, $size = null) {
      $attribs = [];
      
      $cls_size = self::_class_size($size, 'btn');
      $cls_style = self::_class_style($style, 'btn');
      
      $attribs['class'] = "btn {$cls_size} {$cls_style}";
      return Html::a($href, $text, $attribs);
   }
   
   /**
    * 
    * @param type $type
    * @param type $text
    * @param type $style
    * @param type $size
    * @return type
    */
   public static function button($type, $text, $style = null, $size = null) {
      $cls_size = self::_class_size($size, 'btn');
      $cls_style = self::_class_style($style, 'btn');

      $attr = [
          'type' => $type,
          'class' => "btn {$cls_size} {$cls_style}"
      ];
          
      return Html::tag('button', $text,$attr);
   }
   
   /**
    * 
    * @param type $text
    * @param type $for
    */
   public static function label($text, $for = null) {
      echo Html::tag('label', $text, ['class' => 'form-label', 'for' => $for]);
   }
   
   /**
    * 
    * @param type $type
    * @param type $name
    * @param type $value
    * @param type $label
    * @return type
    */
   public static function input($type, $name, $value = null, $label = null) {
      $attribs = ['type' => $type, 
                  'name' => $name, 
                  'value' => Html::encode($value),
                  'class' => 'form-control input-sm'];
      self::form_row_start();
      if($label) {
         echo self::label($label, $name);
      }
      echo Html::tag('input', null, $attribs);
      return self::form_row_end();
   }
   
   /**
    * 
    * @param type $name
    * @param type $value
    * @param type $label
    */
   public static function textfield($name, $value = null, $label = null) {
      $attribs = [
          'class' => 'form-control input-sm',
          'name' => $name];
      self::form_row_start();
      if($label) {
         echo self::label($label, $name);
      }
      echo Html::tag('textarea', $value, $attribs);
      return self::form_row_end();
   }
   
   /**
    * 
    * @param type $name
    * @param type $value
    * @param type $label
    * @param array $items
    * @return type
    */
   public static function select($name, $value = null, $label = null, array $items = null) {
      $attribs = [
          'class' => 'form-control input-sm',
          'name' => $name
      ];
      
      self::form_row_start();
      if($label) {
         echo self::label($label, $name);
      }
      Html::start('select', $attribs);
      foreach($items as $key => $val) {
         if(is_int($key)) {
            $text = $val;
         } else {
            $text = $key;
         }
         if($value == $val) $opt_attrib = ['selected' => 'selected'];
         else $opt_attrib = [];
         
         $opt_attrib['value'] = $val;
         echo Html::tag('option', $text, $opt_attrib);
      }
      echo Html::end();
      return self::form_row_end();
   }

   /**
    * 
    * @param type $style
    * @param type $method
    * @param type $action
    */
   public static function form_start($style = null, $method = 'get', $action = null) {
      $attr = [
          'role' => 'form',
          'method' => $method,
          'action' => $action
      ];
      if($style == self::FORM_INLINE) {
         $attr['class'] = 'form-inline';
      } else if($style == self::FORM_STANDARD) {
         $attr['class'] = 'form-horizontal';
      }      
      
      Html::start('form', $attr);
   }
   
   public static function form_row_start() {
      Html::start('div', ['class' => 'form-group form-group-sm']);
   }
   
   public static function form_row_end() {
      return Html::end();
   }
   
   public static function form_control($type, $name, $value = null, $label = null, $items = null) {
      $attrs = [
          'class' => 'form-control'
      ];
      
      $rendered = null;
      switch ($type) {
         case (isset(Html::$inputTypes[$type])) :
            $rendered = Html::input($type, $name, $value, null, $attrs);
            break;
         
         case 'textfield':
            $rendered = Html::textarea($name, $value, null, $attrs);
            break;
         
         case 'select':
            $rendered = Html::select($name, $value, null, $items, $attrs);
            break;
         
         case 'button':
         case 'submit':
         case 'reset':
            $rendered = Html::button($type, $name, $value, null, $attrs);
            break;
         
         default:
      }
      
      if($label) {
         $rendered = Html::label($label, $name, ['class' => 'control-label']) . $rendered;
      }
      
      return $rendered;
   }
   
   public static function form_end() {
      return Html::end();
   }
   
   /**
    * 
    * @param Form $form
    * @return Form
    */
   public static function form_element(Form $form, $style = null, $size = null) {
//      return $form;
//      $form->registerRenderCallback(function(Form $form, ArrayString $buffer) {
//         
//         $tabid = $form->id . '-tab';
//         $ul = new Element('ul', null, ['id' => $tabid, 'role' => 'tablist', 'class' => 'nav nav-tabs']);
//         $div = new Element('div', null, ['class' => 'tab-content']);
//         $count = 0; 
//         foreach($form->inner() as $inner) {
//            if($inner instanceof Fieldset) {
//               if($count === 0) {
//                  $active = ' active';
//               } else {
//                  $active = '';
//               }
//               $count++;
//               $tabname = $inner->getName() . '-tab';
//               $a = Html::a("#".$tabname, $inner->getLabel(),['role' => 'tab', 'data-toggle' => 'tab']);
//               $ul->inner(Html::tag('li', $a, ['class' => $active]));
//               $inner->wrapElement->tag('div');
//               $inner->wrapElement->id = $tabname;
//               $inner->wrapElement->class = 'tab-pane' . $active;
//               $inner->setLabel(null);
//               $form->moveControl($inner, $div);
//            }
//         }
//         $form->prepend($div);
//         $form->prepend($ul);
//      });
      
    
      $form->registerRenderCallbacks(function(Form $form, ArrayString $buffer) {
         foreach($form->getControls() as $control) {
            if(!$control instanceof Fieldset) {
               $control->class = 'form-control';
               $control->getLabelTag()->class = 'control-label';
            }

            if($control instanceof ButtonControl) {
               $control->class = 'btn btn-primary';
            }
         }
         
      });
               
      if($style == self::FORM_INLINE) {
         $form->class = 'form-inline';
      } else if($style == self::FORM_STANDARD) {
         $form->class = 'form-horizontal';
      }
      
      $form->controlWrapperTag->setTag('div');
      $form->controlWrapperTag->class = 'form-group form-group-sm';
      return $form;
   }
   
   public static function listing(array $items, $style = null) {
      if($style) {
         
      } else {
         $style = self::LIST_VERTICAL | self::LIST_UNORDERED;
      }
      
      $tag = 'ul';
      if($style & self::LIST_ORDERED) $tag = 'ol';
      else if($style & self::LIST_UNORDERED) $tag = 'ul';
      
      
      Html::start($tag);
      foreach($items as $key => $value) {
         echo "<li>";
         if($style & self::LIST_LINK_KEY) {
            echo self::link($key, $value);
         } else if($style & self::LIST_LINK_VALUE) {
            echo self::link($value, $key);
         } else {
            echo $value;
         }
         
         echo "</li>";
      }
      
      return Html::end();
   }
   
   /**
    * 
    * @param type $items
    * @param type $style
    * @return type
    */
   public static function list_group($items, $style = null) {
      Html::start('div', ['class' => 'list-group']);
      $cpath = Url::path();
      $attrs = ['class' => 'list-group-item'];
      foreach($items as $key => $value) {
         if(is_array($value)) {
            unset($attrs['href']);
            echo Html::tag('span', $key,$attrs);
            echo self::nav_list($value, $style);
         } else {
            if($value) $attrs['href'] = $value;
            else unset($attrs['href']);

            if(stripos($value, $cpath)) {
               $attrs['class'] .= ' active';
            }

            echo Html::tag('a', $key, $attrs);
         }
      }
      
      return Html::end();
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
   public function nav_list($items, $active = null, $style = null) {
      $cls = ['nav'];
      if($style & self::NAV_NAVBAR) $cls[] = 'navbar-nav';
      else if($style & self::NAV_PILLS) $cls[] = 'nav-pills';
      else if($style & self::NAV_TABS) $cls[] = 'nav-tabs';
      Html::start('ul', ['class' => implode(' ' , $cls)]);
      foreach( $items as $key => $link) {
         if($active && $key == $active) echo '<li class="active">';
         else echo '<li>';
         echo self::link($link, $key);
         echo '</li>';
      }
      
      return Html::end();
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
      
      Html::start('dl', $attrib);
      foreach($list as $key => $value) {
         if(is_numeric($key)) {
            $key = "";
         }
         
         echo Html::tag('dt', $key, array('title' => $key));
         
         if(!is_array($value)) {
            $value = [$value];
         }
         
         foreach($value as $val) {
            echo Html::tag('dd', $val, array('title' => $key));
         }
      }
      return Html::end();
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
      self::panel_start();
      if($header)
         echo self::panel_header($header);
      echo self::panel_body($body);
      if($footer)
         echo self::panel_footer($footer);
      return self::panel_end();
   }
   
   /**
    * Starts a panel
    */
   public function panel_start() {
      Html::start('div', ['class' => 'panel panel-default']);
   }
   
   /**
    * Renders panel header
    * 
    * @param string $title
    * @return string
    */
   public function panel_header($title) {
      return Html::tag('div', $title, ['class' => 'panel-heading']);
   }
   
   /**
    * Renders panel body
    * 
    * @param string $body
    * @return string
    */
   public function panel_body($body) {
      return Html::tag('div', $body, ['class' => 'panel-body']);
   }
   
   /**
    * Starts panel body
    */
   public function panel_body_start() {
      Html::start('div',  ['class' => 'panel-body']);
   }
   
   /**
    * ends panel body
    * 
    * @return string
    */
   public function panel_body_end() {
      return Html::end();
   }
   
   /**
    * Renders panel footer
    * 
    * @param string $html
    * @return string
    */
   public function panel_footer($html) {
      return Html::tag('div', $html, ['class' => 'panel-footer']);
   }
   
   /**
    * End the panel
    */
   public function panel_end() {
      return Html::end();
   }
   
   /**
    * Given $items will used to create the breadcrumb. Order of items will be used.
    * 
    * @param array $items
    * @return string
    */
   public function breadcrumb($items) {
      Html::start('ol', ['class' => 'breadcrumb']);
      $count = count($items);
      for($i = 0; $i < $count; $i++) {
         list($key, $link) = each($items); 
         if($i == $count - 1 || empty($link)) echo Html::tag('li', $key, ['class' => 'active']);
         else echo Html::tag('li', Html::tag('a', $key, ['href' => $link]));
      }
      return Html::end();
   }
   
   /**
    * Prints pagination
    * 
    * @param int $pagecount
    * @param string $querykey
    * @param int $printcount
    * @return type
    */
   public function pagination($pagecount, $querykey = 'p', $printcount = 5) {
      if(!$querykey) $querykey = 'p';
      $currentpage = Url::query($querykey, 1);
      if($currentpage < 1) $currentpage = 1;
      $start = -1;
      $end = -1;
		$mean = floor($printcount/2);
      $path = Url::path();
      $qparams = Url::query();
      
      $linkmake = function($text, $link_page = null, $class = null) use ($path, $qparams, $querykey) {
         if($class) echo '<li class="'.$class.'">';
         else echo '<li>';
         if($link_page) {
            $qparams[$querykey] = $link_page;
            $query = http_build_query($qparams);
            echo self::link("{$path}?{$query}", $text);
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
		
      Html::start('ul', ['class' => 'pagination']);
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
      return Html::end();
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
      return Html::tag('img', null, $attr);
   }
   
   /**
    * Starts a responsive grid system
    */
   public function grid_start() {
      Html::start('div', ['class' => 'row']);
   }
   
   /**
    * Starts a responsive grid
    * 
    * @param int $lg_cols number of cols the large screen should take
    * @param int $md_cols number of cols medium screeen should take
    * @param int $sm_cols number of cols small screen should take
    */
   public function grid_item_start($lg_cols, $md_cols, $sm_cols = 1) {
      Html::start('div', ['class' => "col col-lg-{$lg_cols} col-md-{$md_cols} col-sm-{$sm_cols}"]);
   }
   
   /**
    * Ends the responsive grid
    */
   public function grid_item_end() {
      echo Html::end();
   }
   
   /**
    * Ends the responseive grid system
    */
   public function grid_end() {
      echo Html::end();
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
      Html::start('div', ['class' => 'media']);
      echo Html::tag('a', self::img($src, $title, null, Ui::IMG_MEDIA), ['class' => 'pull-left']);
      echo Html::tag('div', 
         Html::tag('h4', $title, ['class' => 'media-heading']) .  
         $description
         , ['class' => 'media-body']);
      return Html::end();
   }
   
   /**
    * Prints an alert message box with given $message
    * 
    * @param string $message
    * @param int $style
    * @param boolean $allowdismiss
    * @return string
    */
   public function alert($message, $style = self::STYLE_ALERT, $allowdismiss = false) {
      $cls = 'alert';
      $cls .= ' ' . self::_class_style($style, 'alert');

      return Html::tag('div', $message, ['class' => "alert {$cls}", 'role' => 'alert']);
   }
}
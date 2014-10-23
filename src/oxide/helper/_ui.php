<?php
namespace oxide\helper;
use oxide\helper\Html;
use oxide\ui\html\Form;
use oxide\ui\html\Fieldset;
use oxide\ui\html\ButtonControl;
use oxide\util\ArrayString;

abstract class _ui {
   static protected
      $_attributes = [],
      $_wrappers = [],
      $_renderers = [];
   
   const
      STYLE_DEFAULT = 0,
      STYLE_PRIMARY = 1,
      STYLE_ALERT = 2,
      STYLE_SUCCESS = 3,
      STYLE_INFO = 4,
      FORM_STANDARD = 10,
      FORM_INLINE = 11,
           
      TABLE_STRIPED = 1,
      TABLE_HOVERED = 2,
      TABLE_BORDERED = 4,
      
      IMG_STANDARD = 1,
      IMG_ROUNDED = 2,
      IMG_THUMBNAIL = 4,
           
      LIST_STANDARD = 1,
      LIST_INLINE = 2,
      LIST_LINK = 4,

      SIZE_DEFAULT = 0,
      SIZE_SMALL = 1,
      SIZE_LARGE = 2;
   
   
   public static function initialize() {
      self::$_renderers['thead'] = function($tag, $inner, $attrib) {
         if(!is_array($inner)) {
            $inner = [$inner];
         }
         
         Html::start($tag, $attrib);
         Html::start('tr');
         foreach($inner as $title) {
            echo Html::tag('th', $title);
         }
         echo Html::end();
         return Html::end();
      };
      
      self::$_renderers['tbody'] = function($tag, $inner, $attrib) {
         if(!is_array($inner)) {
            $inner = [$inner];
         }
      };
   }
   
   protected static function _class_style($style, $prefix) {
      switch ($style) {
         case self::STYLE_PRIMARY:
            return "{$prefix}-primary";
         case self::STYLE_ALERT:
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
   
   protected static function _class_size($size, $prefix) {
      if($size == self::SIZE_SMALL) {
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
   public static function text_label($text, $style = null) {
      $attrbs = [
          'class' => 'label ' . self::_class_style($style, 'label')
      ];
      return Html::tag('span', $text, $attrbs);
   }
   
   /**
    * Icon/glyphicon
    * @param type $name
    * @return type
    */
   public static function icon($name) {
      return Html::tag('span', null, ['class' => 'glyphicon glyphicon-' . $name]);
   }

   /**
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
    * @param type $style
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
      if($controls instanceof Form) return self::form_element ($controls);
      
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
   
   /**
    * 
    */
   public static function nav_start() {
      Html::start('nav');
   }
   
   
   public static function nav_list($items, $style = null) {
      Html::start('div', ['class' => 'list-group']);
      $cpath = _url::path();
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
   
   public static function nav_end() {
      return Html::end();
   }
   
   public static function list_group($items, $style = null) {
      Html::start('ul');
      
      $keyvaluelist = function($key, $value) {
         echo '<span>'.$key.'</span>';
         echo $value;
      };
      
      $simplelist = function($key, $value) {
         echo $value;
      };
      
      foreach($items as $key => $value) {
         echo '<li>';
         if(is_array($value)) {
            echo $key;
            echo self::list_group($items);
         } else {
            
         }  
         
         echo '</li>';
      }
      
      return Html::end();
   }
   
   /**
    * 
    * @param type $list
    * @param type $style
    * @return type
    */
   public static function dl($list, $style = null) {
      if(!$list) {return;}
      if(!is_array($list) && !is_object($list)) {
         return $list;
      }
      
      $cls = null;
      if($style & self::LIST_STANDARD) $cls = 'dl-horizontal';
      else if($style & self::LIST_INLINE) { 
         $cls = 'dl-inline';
         _template::styles('.dl-inline dt, .dl-inline dl', [
             'display' => 'inline-block'
         ]);
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
    * 
    * @param string $body
    * @param string $header
    * @param string $footer
    * @return string
    */
   public static function panel($body, $header = null, $footer = null) {
      self::panel_start();
      if($header)
         echo self::panel_header($header);
      echo self::panel_body($body);
      if($footer)
         echo self::panel_footer($footer);
      return self::panel_end();
   }
   
   /**
    * 
    */
   public static function panel_start() {
      Html::start('div', ['class' => 'panel panel-default']);
   }
   
   /**
    * 
    * @param type $title
    * @return type
    */
   public static function panel_header($title) {
      return Html::tag('div', $title, ['class' => 'panel-heading']);
   }
   
   /**
    * 
    * @param type $body
    * @return type
    */
   public static function panel_body($body) {
      return Html::tag('div', $body, ['class' => 'panel-body']);
   }
   
   /**
    * 
    */
   public static function panel_body_start() {
      Html::start('div',  ['class' => 'panel-body']);
   }
   
   /**
    * 
    * @return string
    */
   public static function panel_body_end() {
      return Html::end();
   }
   
   /**
    * 
    * @param type $html
    * @return type
    */
   public static function panel_footer($html) {
      return Html::tag('div', $html, ['class' => 'panel-footer']);
   }
   
   /**
    * 
    */
   public static function panel_end() {
      return Html::end();
   }
   
   /**
    * 
    * @param type $items
    * @return type
    */
   public static function breadcrumb($items) {
      Html::start('ol', ['class' => 'breadcrumb']);
      foreach($items as $key => $link) {
         echo Html::tag('li', Html::tag('a', $key, ['href' => $link]));
      }
      return Html::end();
   }
   
   /**
    * 
    * @param type $pagecount
    * @param type $querykey
    * @param type $printcount
    * @return type
    */
   public static function pagination($pagecount, $querykey = 'p', $printcount = 5) {
      if(!$querykey) $querykey = 'p';
      $currentpage = _url::query($querykey, 1);
      if($currentpage < 1) $currentpage = 1;
      $start = -1;
      $end = -1;
		$mean = floor($printcount/2);
      $path = _url::path();
      $qparams = _url::query();
      
      $linkmake = function($text, $link_page = null, $class = null) use ($path, $qparams, $querykey) {
         if($class) echo '<li class="'.$class.'">';
         else echo '<li>';
         if($link_page) {
            $qparams[$querykey] = $link_page;
            $query = implode('&', $qparams);
            echo self::link("/{$path}?{$query}", $text);
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
    * 
    * @param type $src
    * @param type $title
    * @param type $size
    * @param type $style
    * @return type
    */
   public static function img($src, $title = null, $size = null, $style = null) {
      $attr = [
          'src' => $src,
          'title' => $title
      ];
      
      $cls = ['img-responsive'];
      if($style & self::IMG_ROUNDED) $cls[] = 'img-rounded';
      if($style & self::IMG_THUMBNAIL) $cls[] = 'img-thumbnail';
      $attr['class'] = implode(' ', $cls);
      
      return Html::tag('img', null, $attr);
   }
   
   
   public static function grid_start() {
      Html::start('div', ['class' => 'row']);
   }
   
   public static function grid_item_start($lg_cols, $md_cols, $sm_cols = 1) {
      Html::start('div', ['class' => "col col-lg-{$lg_cols} col-md-{$md_cols} col-sm-{$sm_cols}"]);
   }
   
   public static function grid_item_end() {
      echo Html::end();
   }
   
   public static function grid_end() {
      echo Html::end();
   }
}
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
      FORM_STANDARD = 10,
      FORM_INLINE = 11,
    

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

   public static function input($type, $name, $value = null, $label = null, $attribs = []) {
      $class = (isset($attribs['class'])) ? $attribs['class'] : null;
      $attribs['class'] = 'form-control' . ' ' . $class;
      return Html::tag('div', 
              Html::input($type, $name, $value, $label, $attribs), 
               ['class' => 'form-group']);
   }
   
   public static function renderer($tag, \Closure $renderer) {
      self::$_renderers[$tag] = $renderer;
   }

   public static function render($name, $inner = null, $attr = null) {
      if(isset(self::$_renderers[$name])) {
         $renderer = self::$_renderers[$name];
         return $renderer($name, $inner, $attr);
      } else {
         return Html::tag($name, $inner, $attr);
      }
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
   
   public static function control($tag, $name, $value = null, $label = null, $type = null, $inner = null, array $attribs = null) {
      Html::start('div', ['class' => 'form-group']);
      if($label) {
         echo self::tag('label', $label, ['for' => $name]);
      }
      
      if($attribs) $attribs = [];
      $attribs['name'] = $name;
      $attribs['type'] = $type;
      
      
      
      
      return end();
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
   
   public static function a($href, $text) {
      $attribs['class'] = "btn";
      return Html::a($href, $text, $attribs);
   }
   
   /**
    * Prints heading
    * @param string $main
    * @param string $secondary
    * @return string
    */
   public static function heading($main, $secondary = null) {
      if($secondary) $h2 = Html::tag('small', $secondary);
      else $h2 = null;
      
      return Html::tag('h1', $main . $h2);
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
      if($size == self::SIZE_SMALL) {
         $cls_size = 'btn-sm';
      } else if($size == self::SIZE_LARGE) {
         $cls_size = 'btn-lg';
      } else {
         $cls_size = '';
      }
      if($style == self::STYLE_PRIMARY) {
         $cls_style = 'btn-primary';
      } else if($style == self::STYLE_ALERT) {
         $cls_style = 'btn-danger';
      } else {
         $cls_style = 'btn-default';
      }
      
      $attribs['class'] = "btn {$cls_size} {$cls_style}";
      return Html::a($href, $text, $attribs);
   }
   
   public static function css_class_from_style($style) {
      
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
               
//      if($style == self::FORM_INLINE) {
//         $form->class = 'form-inline';
//      } else {
//         $form->class = 'form-horizontal';
//      }
      
      $form->controlWrapperTag->setTag('div');
      $form->controlWrapperTag->class = 'form-group';
      return $form;
   }
   
   /**
    * 
    */
   public static function nav_start() {
      Html::start('nav');
   }
   
   public static function nav_list($items, $active_index = -1, $style = null) {
      Html::start('ul', ['class' => 'list-group']);
      foreach($items as $key => $value) {
         if(!$value) {
            $text = Html::tag('a', $key, ['href' => $value]);
         } else {
            $text = $key;
         }
         echo Html::tag('li', $text, ['class' => 'list-group-item']);
      }
      
      return Html::end();
   }
   
   public static function nav_end() {
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
      echo self::panel_header($header);
      echo self::panel_body($body);
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
    * @param type $html
    * @return type
    */
   public static function panel_footer($html) {
      return Html::tag('div', $html, ['panel-footer']);
   }
   
   /**
    * 
    */
   public static function panel_end() {
      Html::end();
   }
}
<?php
namespace oxide\app\helper;
use oxide\ui\Renderer;
use oxide\ui\html\Form;
use oxide\ui\html\Fieldset;
use oxide\util\ArrayString;
use oxide\ui\html\Control;
use oxide\ui\html\Tag;
use oxide\ui\html\ControlFactory;

class Ui extends Html {
   
   
   protected           
      $_openedTags = [],
           
      /**
       * @var \oxide\app\helper\Url 
       */
      $_url = null;


   protected
      $_attributes = [],
      $_wrappers = [],
      $_openStack = [],
      $_renderers = [];
   
   const
      STYLE_NONE 			= 1,
      STYLE_SMALL 		= 2,
      STYLE_LARGE 		= 4,
      STYLE_STANDARD 	= 8,
      STYLE_INLINE 		= 16,
      STYLE_HORIZONTAL 	= 32,
      STYLE_PRIMARY 		= 64,
      STYLE_ALERT 		= 128,
      STYLE_SUCCESS 		= 256,
      STYLE_INFO 			= 512,
      STYLE_DANGER 		= 1024,
      STYLE_WARNING 		= 2048,
      STYLE_STRIPED 		= 4096,
      STYLE_HOVERED 		= 8192,
      STYLE_BORDERED 	= 16384,
      STYLE_ROUNDED 		= 32768,
      STYLE_THUMBNAIL 	= 65536,
      STYLE_RESPONSIVE 	= 131072,
      STYLE_TAB 			= 262144,
      STYLE_PILL 			= 524288,
      STYLE_BAR 			= 1048576,
      STYLE_LEFT 			= 2097152,
      STYLE_RIGHT 		= 4194304,
      STYLE_CENTER 		= 8388608,
      STYLE_INSIDE 		= 16777216,
      STYLE_VERTICAL 	= 33554432,
      STYLE_ORDERED 		= 67108864,
      STYLE_UNORDERED 	= 134217728,
      STYLE_LINK_VALUE 	= 268435456;
      
   
   public function __construct(HelperContainer $c) {
      $this->_url = $c->get('url');
   }
   
   protected function _merge_attributes(array &$arr1 = null, array $arr2 = null) {
      if(!$arr2) return;
      if($arr1 === null) {
         $arr1 = [];
      }
      
      foreach($arr2 as $key => $val) {
         if($key == 'class' && isset($arr1[$key])) {
            $arr1[$key] = $arr1[$key] . ' ' . $val;
         } else {
            $arr1[$key] = $val;
         }
      }
   }
   
   protected function _class_style($style, $prefix) {
     
      
      switch ($style) {
         case ($style & self::STYLE_PRIMARY):
            return "{$prefix}-primary";
         case ($style & self::STYLE_DANGER):
            return "{$prefix}-danger";
         case ($style & self::STYLE_SUCCESS):
            return "{$prefix}-success";
         case ($style & self::STYLE_INFO):
         case ($style & self::STYLE_ALERT):
            return "{$prefix}-info";
         case ($style & self::STYLE_NONE):
         default:
            return "{$prefix}-default";
      }
   }
   
   protected function _class_size($style, $prefix) {
      if($style & self::STYLE_SMALL) {
         return "{$prefix}-xs";
      } else if($style & self::STYLE_LARGE) {
         return "{$prefix}-lg";
      } else {
         return '';
      }
   }
   
   /**
    * Text label
    * 
    * @param type $text
    * @param type $style
    * @return type
    */
   public function textLabel($text, $style = self::STYLE_NONE, array $attrib = null) {
      $this->_merge_attributes($attrib, [
         'class' => 'label ' . self::_class_style($style, 'label')
      ]);
   
      return $this->tag('span', $text, $attrib);
   }
   
   /**
    * Icon/glyphicon
    * @styles STYLE_LEFT, STYLE_RIGHT
    * @param string $name
    * @param string $text
    * @param int $style
    * @return string
    */
   public function icon($name, $text = null, $style = self::STYLE_NONE) {
      return $this->tag('span', null, ['class' => 'glyphicon glyphicon-' . $name]).$text;
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
      $this->start();
      echo $this->tableOpen($style);
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
      
      echo $this->tableClose();
      return $this->end();
   }
   
   /**
    * Start creating table
    * @param int $style
    */
   public function tableOpen($style = self::STYLE_NONE) {
      $cls = ['table'];
      $cls[] = 'table-condensed';
      if($style & self::STYLE_HOVERED) $cls[] = 'table-hover';
      if($style & self::STYLE_STRIPED) $cls[] = 'table-striped';      
      if($style & self::STYLE_BORDERED) $cls[] = 'table-bordered';
      
      $this->_openedTags['table'] = $style;
      return $this->openTag('table', ['class' => implode(' ', $cls)]);
   }
   
   /**
    * End table. Must be echoed.
    * 
    * @return string
    */
   public function tableClose() {
      unset($this->_openedTags['table']);
      return $this->closeTag('table');
   }
   
   
   /**
    * Prints heading
    * 
    * @param string $main
    * @param string $secondary
    * @return string
    */
   public function heading($main, $secondary = null, $outline_level = 1) {
      if($secondary) $h2 = '<br/>'.$this->tag('small', $secondary);
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
   public function link($link, $text = null, $target = '_self') {
      if($text === null) $text = $link;
      return $this->tag('a', $text, ['href' => $link, 'target' => $target]);
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
   public function linkButton($href, $text, $style = self::STYLE_NONE) {
      $attribs = [];
      $cls_size = $this->_class_size($style, 'btn');
      $cls_style = $this->_class_style($style, 'btn');
      
      $attribs['class'] = "btn {$cls_size} {$cls_style}";
      $attribs['href'] = $href;
      $attribs['role'] = 'button';
      return $this->tag('a', $text, $attribs);
   }

   /**
    * Start a form element
    * 
    * @param type $style
    * @param type $method
    * @param type $action
    */
   public function formOpen($name, $method = 'get', $action = null, $style = self::STYLE_NONE, array $attr = null) {
      if($style & self::STYLE_INLINE) $cls = 'form-inline';
      else if($style & self::STYLE_HORIZONTAL) $cls = 'form-horizontal';
      else $cls = null;
      
      $this->_merge_attributes($attr, [
         'name' => $name,
         'role' => 'form',
         'method' => $method,
         'action' => $action,
         'class' => $cls
      ]);
      
      
      $this->_openedTags['form'] = $style;
      return $this->openTag('form', $attr);
   }
   
   /**
    * Starting a form row 
    * @return void Doesn't return anything.  Everything is buffered.
    */
   public function formRowOpen(array $attributes = null) {
      $this->_merge_attributes($attributes, ['class' => 'form-group']);
      return $this->openTag('div', $attributes);
   }
   
   /**
    * 
    * @return string returns the buffered 
    */
   public function formRowClose() {
      return $this->closeTag('div');
   }
   
   public function control($type, $name, $value = null, $label = null, $data = null, $style = self::STYLE_NONE, array $attribs = null) {
	   $control = null;
	   $control = ControlFactory::create($type, $name, $value, $label, $data, $attribs);
	   return $this->renderControl($control, $style);
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
   public function controls($type, $name, $value = null, $label = null, $data = null, $style = self::STYLE_NONE, array $attribs = null) {
      $tag = '';
      $inner = '';
      $buffer = '';
      $classes = [];
      $void = false;
      $wrap = null;
      
      if($style & self::STYLE_SMALL) $classes[] = 'form-group-sm';
      else if($style & self::STYLE_LARGE) $classes[] = 'form-group-lg';
      $this->_merge_attributes($attribs, ['name' => $name]);
      
      $ctl = '';
      switch ($type) {
         case 'text':
         case 'email':
         case 'password':
         case 'hidden':
            $classes[] = 'form-control';
            $attribs['value'] = $value;
            $attribs['type'] = $type;
            $tag = 'input';
            $void = true;
            break;
         
         case 'submit':
            $classes[] = 'btn btn-primary';
            $attribs['value'] = $value;
            $attribs['type'] = $type;
            $tag = 'input';
            $void = true;
            break;
         
         case 'reset':
            $classes[] = 'btn btn-danger';
            $attribs['value'] = $value;
            $attribs['type'] = $type;
            $tag = 'input';
            $void = true;
				break;
				
         case 'button':
            $classes[] = 'btn';
            $attribs['value'] = $value;
            $attribs['type'] = $type;
            $tag = 'button';
            $inner = $value;
            $void = false;
            break;
         
         case 'textarea':
            $classes[] = 'form-control';
            $tag = 'textarea';
            $void = false;
            $inner = $value;
            break;
         
         case 'select':
            $classes[] = 'form-control';
            $tag = 'select';
            $void = false;
            $inner = '';
            if($data) {
               if(!is_array($data)) throw new \Exception('Data for select must be an associative array.');
               $optattr = [];
               foreach($data as $lbl => $val) {
                  if($val == $value) $optattr['selected'] = 'selected';
                  else if(isset($optattr['selected'])) unset($optattr['selected']);
                  $optattr['value'] = $val;
                  $inner .= $this->tag('option', $lbl, $optattr);
               }
            }
            break;
            
         case 'checkbox':
            $attribs['type'] = $type;
            $tag = 'input';
            if(!is_array($data)) {
               if($data == $value) {
                  $attribs['checked'] = 'checked';
               }
               $attribs['value'] = $data;
               $inner = $data;
               $wrap = ['label', [
	               'class' => 'checkbox-inline'
               ]];
               $ctl = $this->tag('label', $this->tag('input', null, $attribs) . $data,
                       ['class' => 'checkbox-inline']);
            } else {
               $_chkcount = count($data);
               if($_chkcount > 1) {
                  $attribs['name'] = $name . '[]';
               }
               
               $bff = '';
               foreach($data as $chklabel => $chkvalue) {
                  $attribs['value'] = $chkvalue;
                  if($chkvalue == $value) {
                     $attribs['checked'] = 'checked';
                  }
                  
                  $_ctlchkbx = $this->tag('input', null, $attribs, true);
                  $bff .= $this->tag('label', $_ctlchkbx . $chklabel, [
                     'class' => 'checkbox-inline'
                  ]);
               }
               $ctl = $bff;
            }
            break;
            
         case 'radio':
            $attribs['type'] = $type;
            if(!is_array($data)) {
               $attribs['value'] = $value;
               $ctl = $this->tag('label', $this->tag('input', null, $attribs) . $data,
                       ['class' => 'radio-inline']);
            } else {
               $attribs['name'] = $name . '[]';
               $bff = '';
               foreach($data as $label => $val) {
                  $attribs['value'] = $val;
                  $bff .= $this->tag('label', $this->tag('input', null, $attribs) . $label,
                          ['class' => 'radio-inline']);
               }
               $ctl = $bff;
            }
            break;
      }
      
      $ctl = $this->tag('input', null, $attribs, true);
      
      if($label) {
         $lblattr = ['for' => $name];
         if($style & self::STYLE_HORIZONTAL) {
            $lblattr['class'] = 'col-sm-3 control-label';
         } else {
            $lblattr['class'] = 'control-label';
         }
         $buffer .= $this->tag('label', $label, $lblattr);
      }
      
      
      if($style === null) {
         if(isset($this->_openedTags['form'])) {
            $style = $this->_openedTags['form'];
         }
      }
      
      if($style & self::STYLE_HORIZONTAL) {
         $buffer .= $this->tag('div', $ctl, ['class' => 'col-sm-9']);
      } else {
         $buffer .= $ctl;
      }
      
      return $buffer;
   }
  
   /**
    * Render form end tag
    * 
    * @return string
    */
   public function formClose() {
      unset($this->_openedTags['form']);
      return $this->closeTag('form');
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
    * @param array $items
    * @param type $style
    * @return string
    */
   public function bullets(array $items, $style = self::STYLE_NONE) {
      if($style) {
         
      } else {
         $style = self::STYLE_VERTICAL | self::STYLE_UNORDERED;
      }
      
      $tag = 'ul';
      if($style & self::STYLE_ORDERED) $tag = 'ol';
      else if($style & self::STYLE_UNORDERED) $tag = 'ul';
      
      
      $this->start($tag);
      foreach($items as $key => $value) {
         echo "<li>";
         if($style & self::STYLE_LINK_VALUE) {
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
   public function navigationList($items, $style = self::STYLE_NONE, $activeUrl = null) {
      $this->start('div', ['class' => 'list-group']);
      $attrs = ['class' => 'list-group-item'];
      foreach($items as $key => $value) {
         if(is_array($value)) {
            unset($attrs['href']);
            echo $this->tag('span', $key,$attrs);
            echo $this->navigationList($value, $style, $activeUrl);
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
   public function navigationBar($items, $active = null, $style = self::STYLE_NONE) {
      $cls = ['nav'];
      if($style & self::STYLE_BAR) $cls[] = 'navbar-nav';
      else if($style & self::STYLE_PILL) $cls[] = 'nav-pills';
      else if($style & self::STYLE_TAB) $cls[] = 'nav-tabs';
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
   public function dl($list, $style = self::STYLE_NONE) {
      static $inline_enabled = false;
      
      if(!$list) {return;}
      if(!is_array($list) && !is_object($list)) {
         return $list;
      }
      
      $cls = null;
      if($style & self::STYLE_STANDARD) $cls = 'dl-horizontal';
      else if($style & self::STYLE_INLINE) { 
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
    * Renders a panel with given $body, $header and $footer
    * 
    * @param string $body
    * @param string $header
    * @param string $footer
    * @return string
    */
   public function panel($body, $header = null, $footer = null) {
      $buffer = '';
      $buffer .= $this->panelOpen();
      if($header)
         $buffer .= $this->panelHeader($header);
      $buffer .= $this->panelBody($body);
      if($footer)
         $buffer .= $this->panelFooter($footer);
      $buffer .= $this->panelClose();
      
      return $buffer;
   }
   
   /**
    * Starts a panel
    */
   public function panelOpen() {
      return $this->openTag('div', ['class' => 'panel panel-default']);
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
   public function panelBodyOpen() {
      return $this->openTag('div',  ['class' => 'panel-body']);
   }
   
   /**
    * ends panel body
    * 
    * @return string
    */
   public function panelBodyClose() {
      return $this->closeTag('div');
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
   public function panelClose() {
      return $this->closeTag('div');
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
   public function pagination($pagecount, $querykey = 'p', $printcount = 5) {
      $urlHelper = $this->_url;
      if(!$querykey) $querykey = 'p';
      
      $currentpage = $urlHelper->query($querykey, 1);
      if($currentpage < 1) $currentpage = 1;
      $start = -1;
      $end = -1;
		$mean = floor($printcount/2);
      
      $urlprefix = $urlHelper->url(true, null, $querykey);
      if(stristr($urlprefix, '?') === TRUE) {
         $urlprefix .= '&';
      } else {
         $urlprefix .= '?';
      }
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
   public function img($src, $title = null, $size = null, $style = self::STYLE_NONE) {
      $attr = [
          'src' => $src,
          'title' => $title
      ];
      
      $cls = [];
      if($style & self::STYLE_ROUNDED) $cls[] = 'img-rounded';
      if($style & self::STYLE_THUMBNAIL) $cls[] = 'img-thumbnail';
      if($style & self::STYLE_RESPONSIVE) $cls[] = 'img-responsive';
//      if($style & self::IMG_MEDIA) $cls[] = 'media-object';
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
      return $this->openTag('div', ['class' => 'row']);
   }
   
   /**
    * Starts a responsive grid
    * 
    * @param int $lg_cols number of cols the large screen should take
    * @param int $md_cols number of cols medium screeen should take
    * @param int $sm_cols number of cols small screen should take
    */
   public function gridColumnOpen($xs_cols = 12, $sm_cols = null, $md_cols = null, $lg_cols = null) {
      $class = 'col';
      if($xs_cols) $class .= ' col-xs-' . $xs_cols;
      if($sm_cols) $class .= ' col-sm-' . $sm_cols;
      if($md_cols) $class .= ' col-md-' . $md_cols;
      if($lg_cols) $class .= ' col-lg-' . $lg_cols;
      return $this->openTag('div', ['class' => $class]);
   }
   
   /**
    * Ends the responsive grid
    */
   public function gridColumnClose() {
      return $this->closeTag('div');
   }
   
   /**
    * Ends the responseive grid system
    */
   public function gridRowClose() {
      return $this->closeTag('div');
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
   public function alert($message, $style = self::STYLE_ALERT, $allowdismiss = false) {
      $cls = 'alert';
      $cls .= ' ' . $this->_class_style($style, 'alert');
      return $this->tag('div', $message, ['class' => "{$cls}", 'role' => 'alert']);
   }
   
   /**
    * Render a Form element
    * 
    * @param Form $form
    * @param type $style
    * @return type
    */
   public function renderForm(Form $form, $style = self::STYLE_NONE) {      
      $form->errorTag   = new Tag('div', ['class' => 'alert alert-danger']);
      $form->successTag = new Tag('div', ['class' => 'alert alert-success']);
      
      if($style & self::STYLE_INLINE) $cls = 'form-inline';
      else if($style & self::STYLE_HORIZONTAL) $cls = 'form-horizontal';
      else $cls = null;
		$form->setAttribute('class', $cls, ' ');

      foreach($form->getControls() as $control) {
         $control->setRendererCallback(function($ctl) use ($style) {
            return $this->renderControl($ctl, $style);
         });
      }
     
      
      return $form->render();
   }
   
   /**
    * Render a Control element
    * 
    * @param Control $ctl
    * @return type
    */
   public function renderControl(Control $ctl, $style = self::STYLE_NONE) {
      // setting up the controls
      if($ctl instanceof \oxide\ui\html\SubmitControl) {
         $ctl->setAttribute('class', 'btn btn-primary');
      } else if($ctl instanceof \oxide\ui\html\CheckboxGroupControl) {
         $grptag = $ctl->getTemplateCheckboxTag();
         $grptag->getLabelTag()->setAttribute('class', 'checkbox-inline', ' ');
         $grptag->labelWrapsControl = true;
         $grptag->labelPosition = Control::RIGHT;
      } else if($ctl instanceof \oxide\ui\html\RadioGroupControl) {
         $grptag = $ctl->getTemplateRadioTag();
         $grptag->getLabelTag()->setAttribute('class', 'radio-inline', ' ');
         $grptag->labelWrapsControl = true;
         $grptag->labelPosition = Control::RIGHT;
      } else if($ctl instanceof \oxide\ui\html\FileControl) {
         // don't do anything for the file control
      } else {
	      // for every other controls
         $ctl->setAttribute('class', 'form-control');
      }
      
      $buffer = '';
      
      // setting up form group
      $cls = ['form-group'];
      $error = $ctl->getError();
      if($error) $cls[] = 'has-error';
      if($style & self::STYLE_SMALL) $cls[] = 'form-group-sm';
      else if($style & self::STYLE_LARGE) $cls[] = 'form-group-lg';
      
      // control group
      if(count($ctl->wrappers)) {
	      $wrapper = array_values($ctl->wrappers)[0];
	      $wrapper->setAttribute('class', implode(' ', $cls), ' ');
      } else {
	      $ctl->wrappers[] = new Tag('div', ['class' => implode(' ', $cls)]);
      }
            
		if($style & self::STYLE_NONE) {
			$usegrid = false;
		} else {
			$usegrid = true;
		}
		      
      // label
      if($ctl->getLabel()) {
         $lblTag = $ctl->getLabelTag();
         $lblTag->setAttribute('class', 'control-label col col-sm-3', ' ');
         $lblTag->setAttribute('for', $ctl->getName());
         $buffer .= $lblTag->renderContent($ctl->getLabel());
      }
      
          
      
      // info
      if($ctl->getInfo()) {
         $infoTag = $ctl->getInfoTag();
         $infoTag->setAttribute('class', 'help-block', ' ');
         $buffer .= $infoTag->renderContent($ctl->getInfo());
      }
      
      // error
      if($error) {
         $errorTag = $ctl->getErrorTag();
         $errorTag->setAttribute('class', 'help-block', ' ');
         $buffer .= $errorTag->renderContent($error);
      }
      
      // control
      $ctl->before[] = $this->gridColumnOpen(12, 9);  
      $ctl->after[] = $this->gridColumnClose();
      return $buffer;
   }
   
   /**
    * Render
    * 
    * @param Renderer $renderer
    * @return string
    */
   public function render($renderer, $style = self::STYLE_NONE) {
      if($renderer instanceof Form) {
         return $this->renderForm($renderer, $style);
      } else if($renderer instanceof Control) {
         return $this->renderControl($renderer, $style);
      } else if($renderer instanceof Renderer) {
         return $renderer->render();
      } else {
         return (string)$renderer;
      }
   }
}
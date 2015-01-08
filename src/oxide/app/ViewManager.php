<?php

namespace oxide\app;
use oxide\http\Route;
use oxide\ui\Page;

/**
 * View Controller
 *
 * Controls View object.
 * renders both action view and layout view
 * determine which view script to render based on priority and hierarchy
 * @package oxide
 * @subpackage application
 * @todo throw some events in global scope.
 */
class ViewManager {
   use \oxide\base\pattern\SharedInstanceTrait;
   
   public
      $disableLayout = false;
   
   protected
      /**
       * @var Page Holds the layout page
       */
      $_layoutPage = null,
      $_viewDirName = 'view',
      $_viewScriptExt = 'phtml',
      $_templateDir = null,
      $_route = null,
      $_scriptExt = 'phtml',
      $_scriptname = 'layout.phtml',
      $_contentView = null,
      $_defaultTemplateKey = 0,
      $_view = null,
      $_layoutView = null;

   /**
    * Construction
    * 
    * @access public
    * @param string $template
    * @param Route $route
    */
   public function __construct($template, Route $route) {
      $this->setTemplateDir($template);
      $this->_route = $route;
   }
   
   /**
    * Set the default script extension
    * 
    * Default value is set to 'phtml'
    * @param string $ext
    */
   public function setViewScriptExt($ext) {
      $this->_viewScriptExt = $ext;
   }
   
   /**
    * Get the default view script extension
    * @return string
    */
   public function getViewScriptExt() {
      return $this->_viewScriptExt;
   }
   
   /**
    * Set the view directory name
    * 
    * Default value is set to 'view'
    * @param string $dirname
    */
   public function setViewDirName($dirname) {
      $this->_viewDirName = $dirname;
   }
   
   /**
    * Get the view directory name
    * @return string
    */
   public function getViewDirName() {
      return $this->_viewDirName;
   }
   
   /**
    * Create a page object
    * @param string $script
    * @param Dictionary $data
    * @return Page
    */
   protected function createPage($script, ViewData $data) {
      $page = new Page($script);
      $page->setData($data);
      
      return $page;
   }
   
   /**
    * Create a new view using given $script
    * 
    * If $script is not provided, it will create the view based on route action
    * @param string|null $script
    * @return Page
    */
   public function createView(ViewData $data, $script = null) {
      if($script === null) {
         $script = $this->_route->action;
      }
      $templateScript = $this->getTemplateScript($script); // get templatized script
      $page = $this->createPage($templateScript, $data);
      $view = new View($page);
      
      return $view;
   }
   
   /**
    * returns the current active template directory
    * if template found for the module, it will return that
    * else it will return the main/generic tmeplate
    * @return string 
    */
   public function getTemplateDir() {
      return $this->_templateDir;
   }
   
   /**
    * 
    * @param string $dir
    */
   public function setTemplateDir($dir) {
      if(is_array($dir)) $dir = current($dir);
      $this->_templateDir = $dir;
   }
   
   
   /**
    * Get the template script for the given $script
    * 
    * If no template script found, then the provided script will be returned
    * @param string $script
    * @return string
    */
   public function getTemplateScript($script) {
      $route = $this->_route;
      
      $module = $route->module;
      $controller = $route->controller;
      $dirs = [
          $this->getTemplateDir() . "/{$module}/{$controller}",
          $route->dir  . '/' . $this->_viewDirName . "/{$controller}"
      ];
      
      $fileinfo = pathinfo($script);
      if(!isset($fileinfo['extension'])) {
         $scriptfile = $fileinfo['filename'] . "." . $this->_scriptExt;
      } else {
         $scriptfile = $fileinfo['basename'];
      }
      foreach($dirs as $dir) {
         $file = "{$dir}/{$scriptfile}";
         if (file_exists($file)) {
            return $file;
         }
      }
      
      return $script;
   }

   /**
    * get the layout script file
    *
    * attempts to get the layout script for the current module
    * @access public
    * @return string
    * @throws \Exception 
    */
   public function getLayoutScript() {
      $dirs = [];
      $route = $this->_route;
      $tdir = $this->getTemplateDir();
      $dirs[] = "{$tdir}/{$route->module}";
      $dirs[] = "{$route->dir}/{$this->_viewDirName}";
      $dirs[] = $tdir;
      
      $scriptname = $this->_scriptname;
      foreach($dirs as $dir) {
         $path = "{$dir}/{$scriptname}";
         if (file_exists($path)) {
            return $path;
         }
      }

      return null;
   }
   
   public function getLayoutPage() {
      if($this->_layoutPage == null) {
         $script = $this->getLayoutScript();
         $page = new Page($script);
         $page->setCodeScript($this->getCodeScriptForScript($script));
         
         $this->_layoutPage = $page;
      }
      
      return $this->_layoutPage;
   }
   
   /**
    * 
    * @param type $script
    * @return string
    */
   public function getCodeScriptForScript($script) {
      		$base = basename($script, '.' . $this->_viewScriptExt);
		$dir = dirname($script);
		$codefile = $dir . '/' . $base . '.php';
      
      if(($script != $codefile) && file_exists($codefile)) {
         return $codefile;
      } else return null;
   }
   
   /**
    * 
    * @param \oxide\app\View $view
    * @param \oxide\app\ViewData $data
    * @return View
    */
   public function prepareViewWithData(View $view = null, ViewData $data = null) {
      if(!$view) {
         $view = $this->createView($data);
      }
      
      if(!$this->disableLayout) {
         $layoutPage = $this->getLayoutPage();
         $layoutPage->setData($data);
         $layoutPage->addPartial($view, 'content');
         $layoutView = new View($layoutPage);
         return $layoutView;
      } else {
         return $view;
      }
   }
}
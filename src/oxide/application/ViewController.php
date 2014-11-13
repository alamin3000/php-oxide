<?php

namespace oxide\application;

use oxide\application\View;

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
class ViewController {
   use \oxide\util\pattern\DefaultInstanceTrait;
   protected
           $Templates = null,
           $_module = null,
           $_scriptname = 'layout.phtml',
           $_view = null;

   /**
    *
    * @access public
    * @param array $templates
    * @param string $module name of the current module
    */
   public function __construct($templates, $module = null) {
      if (!is_array($templates))
         $templates = (array) $templates;
      $this->Templates = $templates;
      $this->_module = $module;
   }

   /**
    * returns the current active template directory
    * @access public
    * @return string 
    */
   public function getTemplateDir() {
      return (isset($this->Templates[$this->_module])) ? $this->Templates[$this->_module] : current($this->Templates);
   }

   /**
    * sets the template directory to different location
    * @access public
    * @param mixed $templates 
    */
   public function setTemplates($templates) {
      $this->Templates = $templates;
   }

   /**
    * get the layout script file
    *
    * attempts to get the layout script for the current module
    * following are the order of preference:
    * 	{template_folder}/{module_name}/layout.phtml
    * 	{template_folder}/layout.phtml
    * 	{module_folder}/{view}/layout.phtml
    * @access public
    * @return string
    * @throws \Exception 
    */
   public function getLayoutScript() {
      $tdir = $this->getTemplateDir();
      $scriptname = $this->_scriptname;
      $path = "{$tdir}/{$scriptname}";
      if (!file_exists($path)) {
         throw new \Exception('Layout Script was not found in any location');
      }

      return $path;
   }

   /**
    * get the content view script from template folder, if found
    * {template_dir}/{module_name}/{{controller}/{action}.phtml}
    * @access public
    * @param string $script
    * @return string 
    */
   public function getTemplateScript($script) {
      $dir = $this->getTemplateDir();
      $scriptname = basename($script);
      $dirname = dirname($script);
      $paths = explode(DIRECTORY_SEPARATOR, $dirname);
      $scriptdir = end($paths);
      $file = "{$dir}/{$scriptdir}/{$scriptname}";
      if (file_exists($file)) {
         return $file;
      } else {
         return $script;
      }
   }

   /**
    * returns the main layout view
    * 
    * @access public
    * @return View 
    */
   public function getView() {
      if ($this->_view == null) {
         $layoutView = new View($this->getLayoutScript());
         $this->_view = $layoutView;
      }

      return $this->_view;
   }

   /**
    * renders the view
    *
    * renders both layout view and the action/content view.
    * 
    * first renders the action/content view.
    * then uses the same view object to render the layout script
    * both layout and action/content script is determined based on Script Preference Rules
    * 
    * adds all contents to the response object and prints the response
    * @access public
    * @param View $view
    * @return type 
    */
   public function render(View $view = null) {
      // add the view to the layout
      $layoutView = $this->getView();

      if ($view) {
         // use template script if available for the action/content view
         $templateScript = $this->getTemplateScript($view->getScript());
         $view->setScript($templateScript);
         $layoutView->addView($view);
      }

      return $layoutView->render();
   }
}

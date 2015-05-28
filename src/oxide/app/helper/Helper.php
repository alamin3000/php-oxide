<?php
namespace oxide\app\helper;
use oxide\http\Context;

class Helper {
	use 
		\oxide\base\pattern\SharedInstanceTrait,
		\oxide\base\pattern\ExtendableTrait,
		\oxide\base\pattern\ResolverTrait;
	
	protected
		$_context = null,
		$_helpers = [
			'html' 		=> 'oxide\app\helper\Html',
			'flash' 		=> 'oxide\app\helper\Flash', 
			'ui' 			=> 'oxide\app\helper\Ui',
			'master' 	=> 'oxide\app\helper\Master',
			'url' 		=> 'oxide\app\helper\Url',
			'locale' 	=> 'oxide\app\helper\Locale',
			'formatter' => 'oxide\app\helper\Formatter'
		];
		
	public function __construct(Context $context) {
		$this->_context = $context;
		foreach($this->_helpers as $helper) {
		}
	}
	

	
	/**
	 * load function.
	 * 
	 * @access public
	 * @return void
	 */
	public function load($helper1) {
		$helpers = func_get_args();
		if(empty($helpers)) {
			throw new \Exception("Must specify helpers to load.");
		}
		
		foreach($helpers as $helper) {
			if(!is_string($helper)) {
				throw new \Exception("Helper name must be a string.");
			}
			
			$instance = $this->resolve($helper);
			if($instance === null) {
				throw new \Exception("Unable to load helper: $helper");
			}
			
			$this->extendObject($instance);
		}
	}
	
	public function __call($name, $args) {
		return $this->invokeExtended($name, $args);
	}
}
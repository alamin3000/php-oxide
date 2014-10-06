<?php
namespace oxide\validation\misc;

/**
 * Static Value Fiterer
 *
 * This filterer simply passes back the value given during constraction
 * This is useful to pass static value to
 *
 * @package oxide
 * @subpackage validation
 */
class StaticValueFilterer implements \oxide\validation\Filterer {
	protected
		$_value = null;

	public function  __construct($static_value) {
		$this->_value = $static_value;
	}

	public function filter($value) {
		return $this->_value;
	}
}
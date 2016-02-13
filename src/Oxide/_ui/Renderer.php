<?php
namespace oxide\ui;

/**
 * Renderer interface
 *
 * Any object implementing this interface can be represented as string
 * @package oxide
 * @subpackage ui
 * @abstract
 */
interface Renderer {
	/**
	 * abstract render function
	 *
	 * $arg could be anything that renderer can use in order to render
	 * @return string
	 */
	public function render();
}
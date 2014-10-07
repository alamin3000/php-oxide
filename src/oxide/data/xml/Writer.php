<?php
namespace oxide\data\xml;

class Writer extends \XMLWriter implements \oxide\ui\Renderer
{

	public function render($sender = null)
	{
		return $this->flush();
	}

	public function __toString()
	{
		return $this->render($this);
	}
}
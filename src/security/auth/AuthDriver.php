<?php
/**
 * interface defined for Authentication adapter
 * 
 * In order to use Oxide_Auth to authenticate, an adapter must implement this
 * @package Oxide
 * @subpackage Auth
 * @author Alamin Ahmed <aahmed753@gmail.com>
 */
interface AuthDriver {
	public function getIdentity();
   public function authenticate();
}
?>
<?php
namespace oxide\helper;
use oxide\http\Route;
use oxide\application\auth\Authentication;
use oxide\application\auth\AccessValidator;
use oxide\validation\ValidationResult;
use oxide\application\exception\AuthAccessException;
use oxide\util\EventNotifier;

abstract class Auth {
   const
      EVENT_ACCESS_DENIED = 'AuthAccessDenied',
      EVENT_ACCESS_GRANTED = 'AuthAccessGranted';

   public static function identity() {
		return self::instance()->getIdentity();
	}
	
	public static function instance() {
		return Authentication::defaultInstance();	
	}
	
	public static function rules() {
		$config = App::config();
		return $config->get('rule', null);
	}
	
	public static function roles() {
		$config = App::config();
		return $config->get('role', null);
	}
	
	public static function access(Route $route, $throwException = true) {
		$roles = self::roles();
		$rules = self::rules();
		$notifier = EventNotifier::defaultInstance();

      $identity = self::identity();
      $validator = new AccessValidator($route, $roles, $rules);
      $result = new ValidationResult();
      $validator->validate($identity, $result);
      if(!$result->isValid()) {
         $notifier->notify(self::EVENT_ACCESS_DENIED, self::instance(), ['route' => $route, 'identity' => $identity, 'result' => $result]);
      	if($throwException) {
	      	$error_string = implode('. ', $result->getErrors());
	      	throw new AuthAccessException($error_string);
      	}
      } else {
         $notifier->notify(self::EVENT_ACCESS_GRANTED, self::instance(), ['route' => $route, 'identity' => $identity, 'result' => $result]);
      }
      
      return $result;
	}
}
<?php
namespace home;
use oxide\std\AbstractClass;
use oxide\http\FrontController;

class Home extends AbstractClass {
   public static function initialize(FrontController $app = null) {
      $router = $app->getRouter();
      $router->register('home', self::classDir());
   }
}
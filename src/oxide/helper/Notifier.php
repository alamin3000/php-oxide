<?php
namespace oxide\helper;
use oxide\util\EventNotifier;

class Notifier {
   
   /**
    * 
    * @return EventNotifier
    */
   public static function instance() {
      return EventNotifier::defaultInstance();
   }
   
   /**
    *
    * @access public
    * @param type $event
    * @param type $args 
    */
   public static function notify($event, $sender, $args = null) {
      $notifier = EventNotifier::defaultInstance();
      $notifier->notify($event, $sender, $args);
   }
   
   /**
    * 
    * @param type $event
    * @param \oxide\helper\callable $callback
    * @param type $scope
    */
   public static function register($event, callable $callback, $scope = null) {
      $notifier = EventNotifier::defaultInstance();
      $notifier->register($event, $callback, $scope);
   }
}
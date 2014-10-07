<?php
namespace oxide\plugin;
use oxide\http\Context;
use oxide\util\Notifier;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

interface Pluggable {
   public function plug(Notifier $notifier, Context $context);
}
<?php
namespace oxide\plugin;
use oxide\http\Context;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

interface Pluggable {
   public function plug(Context $context);
}
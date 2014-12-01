<?php
namespace oxide\plugin;
use oxide\http\Context;

interface Pluggable {
   public function plug(Context $context);
}
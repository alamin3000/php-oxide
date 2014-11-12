<?php
namespace oxide\application;
use oxide\http\Context;

interface Pluggable {
   public function plug(Context $context);
}
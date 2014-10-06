<?php
namespace oxide\validation\file;
use oxide\validation\Filterer;


class FilenameFilterer implements Filterer
{
   public function filter($value) {
      // filter the name first
      return preg_replace("/[^A-Z0-9._-]/i", "_", $value);
   }
}
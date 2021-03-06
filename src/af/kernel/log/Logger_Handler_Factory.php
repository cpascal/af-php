<?php
namespace af\kernel\log;

use af\kernel\log\handlers\File_Handler;
use af\kernel\log\handlers\Console_Handler;
use af\kernel\log\handlers\Null_Logger_Handler;
use af\plugins\config\Config;

class Logger_Handler_Factory {
  public function create($handler) {
    if ($handler->type == 'file') {
      return new File_Handler($this->get_path($handler->path));
    }
    if ($handler->type == 'console') {
      return new Console_Handler();
    }
    return new Null_Logger_Handler();
  }

  private function get_path($path) {
    if ($this->is_full_path($path))
      return $path;
    else
      return Config::get_instance()->get_application_path() . '/db/' . $path;
  }

  private function is_full_path($path) {
    return $path[0] == '/' ? true : false;
  }
}

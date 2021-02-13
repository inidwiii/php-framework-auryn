<?php

namespace Auryn\Core;

class Application
{
  public function __construct()
  {
    $this->preloadConstants();
  }

  private function preloadConstants()
  {
    defined('DS') or define('DS', DIRECTORY_SEPARATOR);
    defined('ROOT') or define('ROOT', realpath(getcwd()) . DS);

    defined('PATH_TO_LIB') or define('PATH_TO_LIB', realpath(ROOT . 'src/Auryn') . DS);
    defined('PATH_TO_CONFIG') or define('PATH_TO_CONFIG', realpath(ROOT . 'config') . DS);
    defined('PATH_TO_CORE') or define('PATH_TO_CORE', realpath(PATH_TO_LIB . 'core') . DS);
  }
}
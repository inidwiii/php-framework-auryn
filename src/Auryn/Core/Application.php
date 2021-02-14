<?php

namespace Auryn\Core;

class Application extends Container
{
  public function __construct()
  {
    $this->preloadConstants();
    $this->preloadDependencies();
  }

  private function preloadDependencies()
  {
    foreach (require realpath(PATH_APP . 'bootstrap.php') as $key => $value) {
      if ($key === 'singletons') foreach ($value as $abstract => $concrete) parent::singleton($abstract, $concrete);
      else parent::bind($value, $value);
    }
  }

  /**
   * Pre-defined constant
   * 
   * @return void
   */
  private function preloadConstants()
  {
    defined('DS') or define('DS', DIRECTORY_SEPARATOR);
    defined('ROOT') or define('ROOT', realpath(__DIR__ . '/../../..') . DS);
    defined('PATH_APP') or define('PATH_APP', realpath(ROOT . 'app') . DS);
    defined('PATH_LIB') or define('PATH_LIB', realpath(ROOT . 'src/Auryn') . DS);
    defined('PATH_CORE') or define('PATH_CORE', realpath(PATH_LIB . 'Core') . DS);
  }
}
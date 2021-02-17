<?php

namespace Auryn\Core;

class Application extends Container
{
  public function __construct()
  {
    $this->preloadConstants();
    $this->preloadDependencies();

    $this->call('env', 'init');
  }
  
  /**
   * Preloading registered dependency instances
   * 
   * @return void
   */
  private function preloadDependencies()
  {
    $bootstrap = require realpath(PATH_APP . 'bootstrap.php');

    foreach ($bootstrap['singletons'] as $abstract => $concrete) $this->singleton($abstract, $concrete);
    foreach ($bootstrap['providers'] as $provider) $this->call($provider, 'register');
  }

  /**
   * Pre-define constant
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
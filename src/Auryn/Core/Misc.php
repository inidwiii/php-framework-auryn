<?php

use Auryn\Core\Application;

if (!function_exists('app')) {
  /**
   * Get the app instance or get stored class on the container
   * 
   * @param string|null $abstract
   * @return Application|object
   */
  function app(string $abstract = null): object
  {
    $app = new Application();
    if (is_null($abstract)) return $app;
    return $app->use($abstract);
  }
}
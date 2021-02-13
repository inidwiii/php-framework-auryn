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

if (!function_exists('array_forget')) {
  /**
   * Deleting value from an array with dot notation
   * 
   * @param array $array
   * @param array|string $keys
   */
  function array_forget(&$array, $keys)
  {
    $original = &$array;
    $keys = (array) $keys;

    if (count($keys) === 0) return;

    foreach ($keys as $key) {
      if (array_key_exists($key, $array)) {
        unset($array[$key]);
        continue;
      }

      $segments = explode('.', $key);
      $array = &$original;

      while (count($segments) > 1) {
        $segment = array_shift($segments);

        if (isset($array[$segment]) && is_array($array[$segment])) $array = &$array[$segment];
        else continue 2;
      }

      unset($array[array_shift($segments)]);
    }
  }
}

if (!function_exists('array_get')) {
  /**
   * Get value from a array with dot notation
   * 
   * @param array $array
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  function array_get($array, $key, $default = null)
  {
    if (!is_array($array)) return value($default);
    if (is_null($key)) return $array;
    if (array_key_exists($key, $array)) return $array[$key];
    if (strpos($key, '.') === false) return $array[$key] ?? value($default);

    foreach (explode('.', $key) as $segment) {
      if (is_array($array) && array_key_exists($segment, $array)) $array = $array[$segment];
      else return value($default);
    }

    return $array;
  }
}

if (!function_exists('array_set')) {
  /**
   * Set value to an array with dot notation
   * 
   * @param array $array
   * @param string $key
   * @param mixed $value
   * @return array $array
   */
  function array_set(&$array, $key, $value)
  {
    if (is_null($key)) return $array = $value;

    $keys = explode('.', $key);

    foreach ($keys as $idx => $key) {
      if (count($keys) === 1) break;
      unset($keys[$idx]);
    
      if (!isset($array[$key]) || !is_array($array[$key])) $array[$key] = [];
      $array = &$array[$key];
    }

    $array[array_shift($keys)] = $value;
    return $array;
  }
}

if (!function_exists('value')) {
  /**
   * Provide the initial result from the target
   * 
   * @param mixed $target
   * @return mixed
   */
  function value($target)
  {
    if (is_callable($target)) return $target();
    return $target;
  }
}
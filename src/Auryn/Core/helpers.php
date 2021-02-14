<?php

use Auryn\Core\Application;

if (!function_exists('app')) {
  /**
   * Get app instance or registered instances in container
   * 
   * @param string $abstract
   * @return \Auryn\Core\Application|object
   */
  function app($abstract = null)
  {
    $app = new Application(); 
    if (is_null($abstract)) return $app;
    return $app;
  }
}

if (!function_exists('arrayGet')) {
  /**
   * Get the value inside the array with dot notation
   * 
   * @param array $array
   * @param string|int $key
   * @param mixed $default;
   * @return mixed
   */
  function arrayGet($array, $key, $default = null)
  {
    if (!is_array($array)) return $default;
    if (is_null($key)) return $default;
    if (array_key_exists($key, $array)) return $array[$key];
    if (strpos($key, '.') === false) return $array[$key] ?? $default;

    foreach (explode('.', $key) as $segment) {
      if (isset($array[$segment]) || array_key_exists($segment, $array)) $array = $array[$segment];
      else return $default;
    }

    return $array;
  }
}

if (!function_exists('arraySet')) {
  /**
   * Insert value into array with dot notation
   * 
   * @param array $array
   * @param string|int $key
   * @param mixed $value
   * @return array
   */
  function arraySet(&$array, $key, $value)
  {
    if (is_null($key)) $array = $value;
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
   * Get the initial value of the target
   * 
   * @param mixed $target
   * @return mixed
   */
  function value($target) 
  {
    return is_callable($target) ? call_user_func($target) : $target;
  }
}
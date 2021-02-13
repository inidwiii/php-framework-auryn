<?php

namespace Auryn\Core;

abstract class Container 
{
  /**
   * Storing registered instances
   * @var array
   */
  protected static $_instances = [];

  /**
   * Storing resolved singleton instances
   * @var array
   */
  protected static $_resolved = [];

  /**
   * Registering instances
   * 
   * @param string $abstract
   * @param string|callable|object $concrete
   */
  public function bind(string $abstract, $concrete)
  {
    if ($this->isBinded($abstract)) return;
    $this->pushToInstances($abstract, $concrete);
  }

  /**
   * Calling method from spesific object or class
   * 
   * @param string|callable|object $abstract
   * @param string $method
   * @return mixed
   */
  public function call($abstract, string $method)
  {
    $abstract = is_callable($abstract) && !is_object($abstract) ? call_user_func($abstract, $this) : $abstract;
    $concrete = $this->isBinded($abstract) || array_key_exists($abstract, self::$_resolved) 
      ? $this->use($abstract) : $this->resolveConcrete($abstract);

    try {
      $method = new \ReflectionMethod($concrete::class, $method);
      $dependencies = $this->resolveDependencies($method->getParameters());
      return $method->invokeArgs($concrete, $dependencies);
    } catch (\ReflectionException $err) { die($err->getMessage()); } 
  }

  /**
   * Registering instances as a singleton class
   * 
   * @param string $abstract
   * @param string|callable|object $concrete
   */
  public function singleton(string $abstract, $concrete)
  {
    if ($this->isBinded($abstract)) return;
    $this->pushToInstances($abstract, $concrete, true);
  }

  /**
   * Calling the registered instance
   * 
   * @param string $abstract
   */
  public function use(string $abstract)
  {
    if (!$this->isBinded($abstract)) throw new \InvalidArgumentException("Abstract '{$abstract}' is not registered or binded.");
    if (self::$_instances[$abstract]['singleton']) return $this->resolveSingleton($abstract);
    else return $this->resolveConcrete(self::$_instances[$abstract]['concrete']);
  }

  /**
   * Check if the abstract is registered or not
   * 
   * @param string $abstract
   * @return bool
   */
  private function isBinded(string $abstract): bool
  {
    return (bool) array_key_exists($abstract, self::$_instances);
  }

  /**
   * Check if the abstract is resolved or not
   * 
   * @param string $abstract
   * @return bool
   */
  private function isResolved(string $abstract): bool
  {
    return (bool) array_key_exists($abstract, self::$_resolved);
  }

  /**
   * Registering new data to instances variable
   * 
   * @param string $abstract
   * @param string|callable|object $concrete
   * @param bool $singleton
   */
  private function pushToInstances(string $abstract, $concrete, $singleton = false)
  {
    self::$_instances[$abstract] = compact('concrete', 'singleton');
  }

  /**
   * Resolving concrete as a new instance every time called
   * 
   * @param string|object $concrete
   * @return object
   */
  private function resolveConcrete($concrete): object
  {
    if (is_callable($concrete) && !is_object($concrete)) $concrete = call_user_func($concrete, $this);

    try {
      $class = new \ReflectionClass($concrete);
      $constructor = $class->getConstructor();

      if ($constructor instanceof \ReflectionMethod) $dependencies = $this->resolveDependencies($constructor->getParameters());
      else $dependencies = [];

      return $class->newInstanceArgs($dependencies);
    } catch (\ReflectionException $err) { die($err->getMessage()); }
  }

  /**
   * Resolving the registered dependencies on the instance constructor
   * 
   * @param array $dependencies
   * @return array
   */
  private function resolveDependencies(array $dependencies = []): array
  {
    $resolvedDependencies = [];

    foreach ($dependencies as $dependency) {
      if ($dependency->getType() instanceof \ReflectionNamedType)
        $resolvedDependencies[] = $this->resolveConcrete($dependency->getType()->getName());
      else $resolvedDependencies[] = $dependency;
    }

    return $resolvedDependencies;
  }

  /**
   * Resolving the registered instance but not create new instance every time called
   * 
   * @param string $abstract
   * @return object
   */
  private function resolveSingleton($abstract): object
  {
    if ($this->isResolved($abstract)) return self::$_resolved[$abstract];
    self::$_resolved[$abstract] = $this->resolveConcrete(self::$_instances[$abstract]['concrete']);
    return self::$_resolved[$abstract];
  }
}
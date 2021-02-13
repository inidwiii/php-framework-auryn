<?php

namespace Auryn\Core;

abstract class Container 
{
  protected static $_instances = [];

  protected static $_resolved = [];

  public function bind($abstract, $concrete)
  {
    if ((bool) array_key_exists($abstract, self::$_instances)) return;
    self::$_instances[$abstract] = ['concrete' => $concrete, 'singleton' => false];
  }

  public function singleton($abstract, $concrete)
  {
    if ((bool) array_key_exists($abstract, self::$_instances)) return;
    self::$_instances[$abstract] = ['concrete' => $concrete, 'singleton' => true];
  }

  public function use($abstract)
  {
    if (!(bool) array_key_exists($abstract, self::$_instances)) return;
    if (self::$_instances[$abstract]['singleton']) return $this->resolveSingleton($abstract);
    else return $this->resolveConcrete(self::$_instances[$abstract]['concrete']);
  }

  private function resolveConcrete($concrete)
  {
    if (is_callable($concrete) && !is_object($concrete)) $concrete = call_user_func($concrete, $this);

    $class = new \ReflectionClass($concrete);
    $constructor = $class->getConstructor();

    if ($constructor instanceof \ReflectionMethod) 
      $dependencies = $this->resolveDependencies($constructor->getParameters());
    else $dependencies = [];

    return $class->newInstanceArgs($dependencies);
  }

  private function resolveDependencies(array $dependencies = []) 
  {
    $resolvedDependencies = [];

    foreach ($dependencies as $dependency) {
      if ($dependency->getType() instanceof \ReflectionNamedType)
        $resolvedDependencies[] = $this->resolveConcrete($dependency->getType()->getName());
      else $resolvedDependencies[] = $dependency;
    }

    return $resolvedDependencies;
  }

  private function resolveSingleton($abstract)
  {
    if ((bool) array_key_exists($abstract, self::$_resolved)) return self::$_resolved[$abstract];
    self::$_resolved[$abstract] = $this->resolveConcrete(self::$_instances[$abstract]['concrete']);
    return self::$_resolved[$abstract];
  }
}
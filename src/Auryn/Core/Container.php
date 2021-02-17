<?php

namespace Auryn\Core;

class Container 
{
  /**
   * Store the current active pre-registered abstract 
   * @var string
   */
  private $current;

  /**
   * Store the instance of the class object
   * @var object
   */
  protected static $_instance;

  /**
   * Hold the registered instances
   * @var array
   */
  protected static $_instances = [];

  /**
   * Hold the registered singleton instances
   * @var array
   */
  protected static $_resolved = [];

  /**
   * Register a new instance 
   * 
   * @param string|int $abstract
   * @param callable|object|string $concrete
   * @return self
   */
  public function bind($abstract, $concrete = null)
  {
    if ($this->isRegistered($abstract)) throw new \InvalidArgumentException("'{$abstract}' is already registered.");
    $this->registerInstances($abstract, $concrete);
    return $this;
  }

  /**
   * Call a method from a object or registered instance
   * 
   * @param callable|object|string $concrete
   * @param string $method
   * @param array ...$dependencies
   * @return mixed
   * @throws \InvalidArgumentException
   */
  public function call($concrete, $method, ...$dependencies)
  {
    try {
      if (is_callable($concrete) && !is_object($concrete) && !is_string($concrete)) $concrete = call_user_func($concrete, $this);
      if (is_string($concrete) && $this->isRegistered($concrete)) $concrete = $this->use($concrete);
      if (is_string($concrete)) $concrete = $this->resolveConcrete(compact('concrete'));
      if (is_object($concrete)) $concrete = $concrete;
      else throw new \InvalidArgumentException(
        'Invalid typeof $concrete. Expected to be string, object or function, given ' . gettype($concrete)
      );

      $method = new \ReflectionMethod($concrete, $method);
      $dependencies = $this->resolveDependencies($method->getParameters(), $dependencies);

      return $method->invokeArgs($concrete, $dependencies);
    } catch (\ReflectionException $ex) { die($ex->getMessage()); } 
      catch (\Exception $ex) { die($ex->getMessage()); }
  }

  /**
   * Register a new instance and save it on first call 
   * 
   * @param string|int $abstract
   * @param callable|object|string $concrete
   * @return self
   * @throws \InvalidArgumentException
   */
  public function singleton($abstract, $concrete = null)
  {
    if ($this->isRegistered($abstract)) throw new \InvalidArgumentException("'{$abstract}' is already registered.");
    $this->registerInstances($abstract, $concrete, true);
    return $this;
  }

  /**
   * Get the registered instance in the container
   * 
   * @param string $abstract
   * @return object
   */
  public function use($abstract)
  {
    $this->current = null;
    if (!$this->isRegistered($abstract)) throw new \InvalidArgumentException("'{$abstract}' is not registered.");
    if (self::$_instances[$abstract]['singleton']) return $this->resolveSingleton($abstract);
    else return $this->resolveConcrete(self::$_instances[$abstract]);
  }

  /**
   * Inject parameters into the object instance constructor
   * 
   * @param array ...$dependencies
   * @return void
   */
  public function with(...$dependencies)
  {
    $this->registerInjectedDependencies($dependencies);
  }

  /**
   * Get the instance of application class through the Container
   * 
   * @param mixed ...$args
   * @return \Auryn\Core\Application
   */
  public static function instance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new static(...func_get_args());
    }

    return self::$_instance;
  }

  /**
   * Check if an instance is already registered or not
   * 
   * @param string $abstract
   * @return bool
   */
  private function isRegistered($abstract): bool
  {
    return (bool) array_key_exists($abstract, self::$_instances);
  }

  /**
   * Check if an instance is already resolved or not
   * 
   * @param string $abstract
   * @return bool
   */
  private function isResolved($abstract): bool
  {
    return (bool) array_key_exists($abstract, self::$_resolved);
  }

  /**
   * Registering dependencies to currenct pre-registered instance
   * 
   * @param array $dependencies
   * @return void
   */
  private function registerInjectedDependencies(array $dependencies)
  {
    self::$_instances[$this->current]['dependencies'] = $dependencies;
  }

  /**
   * Register a new instances to the container
   * 
   * @param string $abstract
   * @param callable|object|string
   * @return void
   */
  private function registerInstances($abstract, $concrete, $singleton = false)
  {
    $concrete = is_null($concrete) ? $abstract : $concrete;
    self::$_instances[$abstract] = compact('concrete', 'singleton');
    $this->current = $abstract;
  }

  /**
   * Resolving concrete instance as an object 
   * 
   * @param array $data
   * @return object
   * @throws \ReflectionException
   */
  private function resolveConcrete($data)
  {
    extract($data);

    try {
      if (is_callable($concrete) && !is_object($concrete)) $concrete = call_user_func($concrete, $this);

      $class = new \ReflectionClass($concrete);
      $constructor = $class->getConstructor();
      $dependencies = $constructor instanceof \ReflectionMethod 
        ? $this->resolveDependencies($constructor->getParameters(), $dependencies ?? []) 
        : $dependencies ?? [];

      return $class->newInstanceArgs($dependencies);
    } catch (\ReflectionException $ex) { die($ex->getMessage()); }
  }

  /**
   * Resolving the dependencies that are required by the concrete
   * 
   * @param array $dependencies
   * @return array
   */
  private function resolveDependencies(array $dependencies, array $injectedDependencies = [])
  {
    $resolvedDependencies = [];

    foreach ((array) $dependencies as $dependency) {
      if ($dependency->getType() instanceof \ReflectionNamedType) 
        $resolvedDependencies[] = $this->resolveConcrete(['concrete' => $dependency->getType()->getName()]);
      else if (!empty($injectedDependencies)) $resolvedDependencies[] = array_shift($injectedDependencies);
      else $resolvedDependencies[] = $dependency->getDefaultValue();
    }

    return $resolvedDependencies;
  }

  /**
   * Resolving singleton registered concrete instance as an object 
   * 
   * @param string $abstract
   * @return object
   */
  private function resolveSingleton($abstract)
  {
    if ($this->isResolved($abstract)) return self::$_resolved[$abstract];
    self::$_resolved[$abstract] = $this->resolveConcrete(self::$_instances[$abstract]);
    return self::$_resolved[$abstract];
  }
}
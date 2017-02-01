<?php

namespace Slinky\Container;

use Closure;
use ReflectionClass;
use ReflectionMethod;

use Slinky\Exception\Core\ClassNotFoundException;
use Slinky\Exception\Core\MethodNotFoundException;

class Container
{
	/**
     * An array of the types that have been resolved
     *
     * @var array
     */
	private $resolved = [];
	
	/**
     * An array of shared instances
     *
     * @var array
     */
	private $shared = [];
	
	/**
     * The registered type aliases
     *
     * @var array
     */
    private $aliases = [];
	
	/**
	 * Register new class with closure
	 * 
	 * @param string $name
	 * @param \Closure $resolve
	 * @return void
	 */
	public function set($name, Closure $resolve)
	{
		$this->resolved[$name] = $resolve;
	}
	
	/**
     * Alias a type to a different name
     *
     * @param string $alias
     * @param string $abstract
     * @return void
     */
	public function alias($alias, $abstract)
	{
		$this->aliases[$alias] = $abstract;
	}
	
	/**
	 * Returns the instance of $name class if exist, if not creates it first
	 * 
	 * @param mixed $name
	 * @param array $arguments
	 * @return object
	 */
	public function get($name, array $arguments = [])
	{
		if (is_object($name)) {
			$shortName = $name->getShortName();
			
			if ($this->exists($shortName) || $this->exists($shortName, 'shared') || $this->exists($shortName, 'aliases')) {
				$name = $shortName;
			} else {
				$name = $name->getName();
			}
		}
		
		if ($this->exists($name, 'aliases')) {
			$abstract = $this->getAlias($name);
		} else {
			$abstract = $name;
		}
		
		if (! $this->exists($name, 'shared')) {
			if ($this->exists($name)) {
				$this->shared[$name] = $this->getNew($name, $arguments);
			} else {
				$this->shared[$name] = $this->getNew($abstract, $arguments);
			}
		}
		
		return $this->shared[$name];
	}
	
	/**
	 * Create and returns new instance of $name class
	 * 
	 * @param string $name
	 * @param array $arguments
	 * @return object
	 */
	public function getNew($name, array $arguments = [])
	{
		if ($this->exists($name)) {
			return $this->createFromResolved($name, $arguments);
		} else {
			return $this->createFromReflection($name);
		}
		
		throw new ClassNotFoundException('Class with name "' . $name . '" does not exist.');
	}
	
	/**
	 * Create and returns new instance of $name class from resolved
	 * 
	 * @param string $name
	 * @param array $arguments
	 * @return object
	 */
	private function createFromResolved($name, $arguments = [])
	{
		$class = $this->resolved[$name];
		
		$object = $class();

		return $object;
	}
	
	/**
	 * Create and returns new instance of $name class with ReflectionClass
	 * 
	 * @param string $class
	 * @return object
	 */
	private function createFromReflection($class)
	{
		if (!class_exists($class)) {
			throw new ClassNotFoundException('Class with name "' . $class . '" does not exist.');
		}
		
		$reflection = new ReflectionClass($class);
		
		if (!$reflection->isInstantiable()) {
			throw new ClassNotFoundException('Class with name "' . $class . '" is not instantiable.');
		}
		
		$constructor = $reflection->getConstructor();
		
		if (!$constructor) {
			return new $class();
		}
		
		if (!$constructor->getNumberOfParameters()) {
			return new $class();
		}
		
		$dependencies = $constructor->getParameters();
		
		$instances = $this->getDependencies($dependencies);
		
		return $reflection->newInstanceArgs($instances);
	}
	
	/**
	 * Returns method from already instanced object
	 * 
	 * @param string $class
	 * @param string $method
	 * @param array $arguments
	 */
	public function getMethod($class, $method, $arguments = [])
	{
		if (!method_exists($class, $method)) {
			throw new MethodNotFoundException('Method with name "' . $method . '" does not exist in class "' . get_class($class) . '".');
		}
		
		$reflection = new ReflectionMethod($class, $method);
		
		if (!$reflection->getNumberOfParameters()) {
			return $class->$method();
		}
		
		$dependencies = $reflection->getParameters();
		
		$instances = $this->getDependencies($dependencies, $arguments);
		
		return $reflection->invokeArgs($class, $instances);
	}
	
	/**
     * Resolve all of the dependencies from the ReflectionParameters.
     *
     * @param array $parameters
     * @param array $arguments
     * @return array
     */
	private function getDependencies(array $parameters, array $arguments = [])
	{
		$dependencies = [];
		
		foreach ($parameters as $parameter) {
			$dependency = $parameter->getClass();
			
            if (array_key_exists($parameter->name, $arguments)) {
                $dependencies[] = $arguments[$parameter->name];
            } elseif (is_null($dependency)) {
                $dependencies[] = null;
            } else {
                $dependencies[] = $this->get($dependency);
            }
        }
        return $dependencies;
	}
	
	/**
	 * Get alias via abstract
	 * 
	 * @param string $alias
	 * @return string
	 */
	private function getAlias($alias)
	{
		return $this->aliases[$alias];
	}
	
	/**
	 * Check if class name is already registered
	 * 
	 * @param string $name
	 * @param string $type
	 * @return bool
	 */
	private function exists($name, $type = 'resolved')
	{
		return array_key_exists($name, $this->$type);
	}
}

<?php

namespace Slinky\Slinky;

use Slinky\Container\Container;
use Slinky\Config\Config;
use Dotenv\Dotenv;

class Application
{
	/**
     * The Slinky framework version
     *
     * @const string
     */
    const VERSION = '0.1';

    /**
     * The base path for the Slinky installation
     *
     * @var string
     */
    private $basePath;

	/**
	 * DI Container class instance
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * Config class instance
	 *
	 * @var Config
	 */
	private $config;

	/**
	 * Create new application instance
	 *
	 * @return void
	 */
	public function __construct($basePath)
	{
		$this->setBasePath($basePath);

		$this->createContainer();

		$this->init();
	}

	/**
	 * Set application base path
	 *
	 * @param string $basePath
	 */
	private function setBasePath($basePath)
	{
		$this->basePath = rtrim($basePath, '\/') . DIRECTORY_SEPARATOR;
	}

	/**
	 * Create DI Container
	 *
	 * @return void
	 */
	private function createContainer()
	{
		$this->container = $container = new Container();

		$this->container->set('Container', function() use ($container) {
			return $container;
		});
	}

	/**
	 * Initialize application
	 *
	 * @return void
	 */
	private function init()
	{
		session_save_path($this->sessionPath());
		session_start();

		$this->loadEnv($this->basePath());
		$this->loadConfig($this->configPath());

		date_default_timezone_set($this->config->get('app.timezone'));
		mb_internal_encoding($this->config->get('app.encoding'));

		$this->registerContainerAliases();
	}

	/**
	 * Load enviroment file
	 *
	 * @param string $path
	 * @return void
	 */
	private function loadEnv($path)
	{
		$dotenv = new Dotenv($path);
		$dotenv->load();
	}

	/**
	 * Load config file
	 *
	 * @param string $path Path to config directory
	 * @return void
	 */
	private function loadConfig($path)
	{
		$this->config = $config = new Config(include $path . 'config.php');

		$this->container->set('Config', function() use ($config) {
			return $config;
		});
	}

	/**
	 * Enable access to the DI container
	 *
	 * @return Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
     * Get the base path of the Slinky installation
     *
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

	/**
     * Get the path to the application root
     *
     * @return string
     */
    public function appPath()
    {
        return $this->basePath() . 'app' . DIRECTORY_SEPARATOR;
    }

	/**
     * Get the path to the language directory
     *
     * @return string
     */
    public function languagePath()
    {
        return $this->resourcePath() . 'lang' . DIRECTORY_SEPARATOR;
    }

	/**
     * Get the path to the application configuration
     *
     * @return string
     */
    public function configPath()
    {
        return $this->basePath() . 'config' . DIRECTORY_SEPARATOR;
    }

	/**
     * Get the path to the storage directory
     *
     * @return string
     */
    public function storagePath()
    {
        return $this->basePath() . 'storage' . DIRECTORY_SEPARATOR;
    }

	/**
     * Get the path to the resources directory
     *
     * @return string
     */
    public function resourcePath()
    {
        return $this->basePath() . 'resources' . DIRECTORY_SEPARATOR;
    }

	/**
     * Get the path to the cache directory
     *
     * @return string
     */
    public function cachePath()
    {
        return $this->storagePath() . 'cache' . DIRECTORY_SEPARATOR;
    }

	/**
     * Get the path to the log directory
     *
     * @return string
     */
    public function logPath()
    {
        return $this->storagePath() . 'log' . DIRECTORY_SEPARATOR;
    }

	/**
     * Get the path to the session directory
     *
     * @return string
     */
    public function sessionPath()
    {
        return $this->storagePath() . 'session' . DIRECTORY_SEPARATOR;
    }

	/**
     * Get the path to the public web directory
     *
     * @return string
     */
    public function publicPath()
    {
        return $this->basePath() . 'public_html' . DIRECTORY_SEPARATOR;
    }

	/**
     * Get the path to the storage directory
     *
	 * @param string $file
     * @return string
     */
    public function elixir($file)
    {
        $path = $this->publicPath().'rev-manifest.json';

		if (file_exists($path)) {
            $manifest = json_decode(file_get_contents($path), true);
        }

		if (isset($manifest[$file])) {
            return $manifest[$file];
        }

		$unversioned = $this->publicPath().$file;

        if (file_exists($unversioned)) {
            return $file;
        }
    }

	/**
	 * Register the core class aliases in the container
	 *
	 * @return void
	 */
	private function registerContainerAliases()
	{
		$aliases = [
			'Auth' => 'Slinky\Auth\Auth',
			'Cache' => 'Slinky\Cache\Cache',
			'Config' => 'Slinky\Config\Config',
			'Container' => 'Slinky\Container\Container',
			'Cookie' => 'Slinky\Cookie\Cookie',
			'Crypt' => 'Slinky\Encryption\Crypt',
			'Database' => 'Slinky\Database\Database',
			'QueryBuilder' => 'Slinky\Database\QueryBuilder',
			'QueryLog' => 'Slinky\Database\QueryLog',
			'Dispatcher' => 'Slinky\Routing\Dispatcher',
			'ExceptionHandler' => 'Slinky\Exception\ExceptionHandler',
			'ExceptionLog' => 'Slinky\Exception\ExceptionLog',
			'File' => 'Slinky\File\File',
			'HandleExceptions' => 'Slinky\Slinky\HandleExceptions',
			'Hash' => 'Slinky\Hash\Hash',
			'Header' => 'Slinky\Http\Header',
			'Language' => 'Slinky\Translation\Language',
			'Log' => 'Slinky\Log\Log',
			'Pagination' => 'Slinky\Pagination\Pagination',
			'Redirect' => 'Slinky\Routing\Redirect',
			'Response' => 'Slinky\Http\Response',
			'Request' => 'Slinky\Http\Request',
			'Route' => 'Slinky\Routing\Route',
			'RouteGroup' => 'Slinky\Routing\RouteGroup',
			'Router' => 'Slinky\Routing\Router',
			'Session' => 'Slinky\Session\Session',
			'Status' => 'Slinky\Http\Status',
			'Translation' => 'Slinky\Translation\Translation',
			'Validate' => 'Slinky\Validation\Validate',
			'ValidateFactory' => 'Slinky\Validation\ValidateFactory',
			'Validator' => 'Slinky\Validation\Validator',
		];

		foreach ($aliases as $key => $alias) {
            $this->container->alias($key, $alias);
        }
	}

	/**
	 * Register the core class dependencies based on config in the container
	 *
	 * @return void
	 */
	private function registerContainerDependencies()
	{
		$container = $this->container;
		$config = $this->config;

		$languagePath = $this->languagePath();
		$responsePath = $this->appPath() . 'View' . DIRECTORY_SEPARATOR . 'Web' . DIRECTORY_SEPARATOR;
		$logPath = $this->logPath();
		$cachePath = $this->cachePath();

		$container->set('Translation', function() use ($container, $languagePath) {
			return new \Slinky\Translation\Translation($container->get('File'), $languagePath);
		});

		$container->set('Language', function() use ($container, $config) {
			return new \Slinky\Translation\Language($container->get('Translation'), $config->get('language.default'), $config->get('language.allowed'));
		});

		$container->set('Response', function() use ($container, $responsePath) {
			return new \Slinky\Http\Response($container->get('File'), $container->get('Redirect'), $container->get('Session'), $container->get('Header'), $container->get('Status'), $responsePath);
		});

		$container->set('Log', function() use ($container, $logPath) {
			return new \Slinky\Log\Log($container->get('File'), $logPath);
		});

		$container->set('Cache', function() use ($container, $cachePath, $config) {
			return new \Slinky\Cache\Cache($container->get('File'), $cachePath, $config->get('cache.type'));
		});

		$container->set('ImageFactory', function() use ($config) {
			return new \Slinky\Image\ImageFactory($config->get('image.driver'));
		});

		$container->set('Crypt', function() use ($config) {
			if ($config->get('app.cipher')) {
				return new \Slinky\Encryption\Crypt($config->get('app.key'), $config->get('app.cipher'));
			}

			return new \Slinky\Encryption\Crypt($config->get('app.key'));
		});

		$container->set('Hybridauth', function() use ($config) {
			return new \Hybrid_Auth($config->get('social'));
		});
	}

	/**
	 * Register error, exception and shutdown handlers
	 *
	 * @return void
	 */
	private function registerErrorHandlers()
	{
		$this->container->get('HandleExceptions');
	}

	/**
	 * Load all application routes from routes.php
	 *
	 * @return void
	 */
	private function loadRoutes()
	{
		$routes_file = $this->appPath() . 'Controller' . DIRECTORY_SEPARATOR . 'routes.php';

		$this->container->get('File')->load($routes_file, ['app' => $this]);
	}

	/**
     * Get or check the current application environment
     *
     * @param mixed
     * @return bool|string
     */
	public function environment()
	{
		if (func_num_args() > 0) {
            $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

            foreach ($patterns as $pattern) {
                if ($pattern == $this->config->get('app.env')) {
                    return true;
                }
            }

            return false;
        }

		return $this->config->get('app.env');
	}

	/**
	 * Register error handlers, load routes and run the application
	 *
	 * @return void
	 */
	public function run()
	{
		$this->registerContainerDependencies();
		$this->registerErrorHandlers();

		$this->loadRoutes();

		$this->container->get('Router')->route();
		$this->container->get('Dispatcher')->dispatch();
	}
}
